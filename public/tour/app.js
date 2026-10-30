import * as THREE from "three";
import { OrbitControls } from "three/addons/controls/OrbitControls.js";
import { CSS2DRenderer, CSS2DObject } from "three/addons/renderers/CSS2DRenderer.js";

const D = await (await fetch("data/rooms.json")).json();
const ROOMS = Object.fromEntries(D.rooms.map((r) => [r.id, r]));
const TIERS = Object.fromEntries(D.tiers.map((t) => [t.id, t]));
const money = (n, c = 0) => "$" + Number(n).toLocaleString("en-US", { minimumFractionDigits: c, maximumFractionDigits: c });
const tierAmt = (t) => money(t.amount) + (t.plus ? "+" : "");
const el = (tag, attrs = {}, ...kids) => {
  const e = document.createElement(tag);
  for (const [k, v] of Object.entries(attrs)) {
    if (k === "class") e.className = v; else if (k === "text") e.textContent = v;
    else if (k.startsWith("on")) e.addEventListener(k.slice(2), v); else if (v != null && v !== false) e.setAttribute(k, v);
  }
  for (const k of kids.flat()) if (k != null) e.append(k.nodeType ? k : document.createTextNode(String(k)));
  return e;
};

const REDUCE = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const params = new URLSearchParams(location.search);
function webglOk() {
  try {
    const canvas = document.createElement("canvas");
    return !!(window.WebGLRenderingContext && (canvas.getContext("webgl2") || canvas.getContext("webgl")));
  } catch (e) {
    return false;
  }
}
const want3d = params.get("view") === "3d" || (params.get("view") !== "list" && !REDUCE);
const USE_3D = want3d && webglOk();

const panel = document.getElementById("panel");
let selected = null;
let camera, controls, renderer, labelR, scene, model;
const floors = {};
const labels = {};
const pickables = [];
let highlight = () => {};
let fly = () => {};
let overview = () => {};
let topView = () => {};

function reserveInterest(slot) {
  const dlg = document.getElementById("reserve");
  const sel = dlg?.querySelector("select[name=interest]");
  const known = sel && [...sel.options].some((o) => o.value === slot.id);
  if (dlg && sel && known && typeof dlg.showModal === "function") {
    sel.value = slot.id;
    const label = dlg.querySelector("[data-reserve-label]");
    const meta = dlg.querySelector("[data-reserve-meta]");
    const tier = TIERS[slot.tier];
    if (label) label.textContent = slot.label;
    if (meta && tier) {
      const proposed = slot.status === "proposed" ? " Proposed use: the campaign plan ties this tier to studios and labs, so using it here needs board approval." : "";
      meta.textContent = tier.name + " tier, " + tierAmt(tier) + ". " + tier.funds + "." + proposed;
    }
    dlg.showModal();
    dlg.querySelector("input[name=name]")?.focus();
    return;
  }
  location.href = "/join/?slot=" + encodeURIComponent(slot.id);
}

function slotsFor(pred) { return D.slots.filter(pred); }
function oppRow(title, desc, slots, tier, proposed) {
  const slot = slots[0];
  return el("div", { class: "opp" },
    el("div", {},
      el("div", { class: "t", text: title }),
      el("div", { class: "d" }, `${tier.name} tier · ${tierAmt(tier)}`, proposed ? el("span", { class: "pill prop", text: "proposed use" }) : el("span", { class: "pill", text: "plan tier" })),
      el("div", { class: "d" }, desc),
      el("div", { class: "s", text: slots.length === 1 ? "Available" : `${slots.length} available` })),
    el("button", { type: "button", class: "primary", onclick: () => reserveInterest(slot) }, "Reserve interest"));
}
function renderRoom(r) {
  const w = D.wings[r.wing];
  panel.replaceChildren();
  panel.append(el("div", { class: "kicker", style: `color:${w.color}` }, r.inBuilding ? (r.wing === "shared" ? "Shared space" : `${w.name} wing`) : "Site"));
  panel.append(el("h2", { text: r.name }));
  panel.append(el("p", { text: r.purpose }));
  const stat = el("div", { class: "stat" });
  stat.append(el("div", {}, el("b", { text: r.sf.toLocaleString() + " sf" }), el("span", { text: r.inBuilding ? "concept allocation" : "site element, outside the 12,000 sf" })));
  stat.append(el("div", {}, el("b", { text: r.fundTotal != null ? money(r.fundTotal) : "Not priced" }), el("span", { text: r.fundTotal != null ? "to build out and equip (budget.json)" : "no line in budget.json" })));
  panel.append(stat);
  panel.append(el("h3", { text: "What it costs to fund" }));
  if (r.notPriced) panel.append(el("p", { class: "note", text: r.notPriced }));
  const t = el("table", { class: "lines" });
  if (r.buildout) t.append(el("tr", {}, el("td", {}, "Build-out share", el("div", { class: "note" }, r.buildout.basis, " · ", el("a", { href: r.buildout.source, text: "source" }))), el("td", { class: "n", text: money(r.buildout.amount) })));
  for (const e of r.equipment) t.append(el("tr", {}, el("td", {}, e.item, el("div", { class: "note" }, `${e.qty} × ${money(e.unit, 2)} · `, el("a", { href: e.source, text: "source" }))), el("td", { class: "n", text: money(e.total, 2) })));
  if (t.children.length) panel.append(t);
  if (r.staff.length) {
    panel.append(el("h3", { text: "People who would run it (annual)" }));
    const st = el("table", { class: "lines" });
    for (const s of r.staff) st.append(el("tr", {}, el("td", {}, s.role, el("div", { class: "note" }, `${s.fte} FTE · ${s.basis} · `, el("a", { href: s.source, text: "source" }))), el("td", { class: "n", text: money(s.total) + "/yr" })));
    panel.append(st);
  }
  panel.append(el("h3", { text: "Naming and interest" }));
  const roomSlot = D.slots.find((s) => s.id === "room:" + r.id);
  const any = [];
  if (roomSlot) any.push(oppRow(r.name, roomSlot.status === "plan" ? "Name this studio or lab." : "Name this space. The plan ties Founding Partner naming to studios and labs; using it here needs board approval.", [roomSlot], TIERS.founding, roomSlot.status !== "plan"));
  const byTier = {};
  for (const s of slotsFor((s) => s.room === r.id && s.kind !== "room")) (byTier[s.tier] ||= []).push(s);
  for (const [tid, ss] of Object.entries(byTier)) any.push(oppRow(ss[0].label.replace(/ #\d+$/, "").replace(/ \(position.*$/, ""), TIERS[tid].funds, ss, TIERS[tid], false));
  if (["create", "tech", "build", "well", "navigate"].includes(r.wing)) {
    const ws = D.slots.find((s) => s.id === "wing:" + r.wing);
    if (ws) any.push(oppRow(ws.label, "Name the whole wing. No wing amount is set in the campaign plan; Founding Partner is the only naming tier.", [ws], TIERS.founding, true));
  }
  if (any.length) panel.append(...any); else panel.append(el("p", { class: "note", text: "No naming option for this space." }));
  panel.append(el("p", { class: "note", text: `Tiers: ${D.meta.tierSource}. ${D.meta.costNote}` }));
}
function renderWelcome() {
  panel.replaceChildren(
    el("div", { class: "kicker", style: "color:#8e294f", text: "Explore the plan" }),
    el("h2", { text: "Azalea Yard" }),
    el("p", { text: "A year-round place for young adults ages 16 to 24 to make art, learn job-ready skills from digital media and coding to the skilled trades, take care of their well-being, and connect with local resources." }),
    el("div", { class: "stat" },
      el("div", {}, el("b", { text: D.meta.totalSf.toLocaleString() + " sf" }), el("span", { text: "one story, " + D.meta.footprint.split(",")[0] })),
      el("div", {}, el("b", { text: money(D.meta.budgetTotals.startup) }), el("span", { text: "startup (lease + renovate plan)" })),
      el("div", {}, el("b", { text: money(D.meta.budgetTotals.equipment, 2) }), el("span", { text: "itemized equipment" })),
      el("div", {}, el("b", { text: money(D.meta.budgetTotals.ops) }), el("span", { text: "year-1 operating" }))),
    el("h3", { text: "Five wings" }),
    el("ul", {},
      el("li", {}, el("b", { text: "Create: " }), "art studio, ceramics, music and recording booths, film and media studio, screening room"),
      el("li", {}, el("b", { text: "Tech: " }), "computer, AI and coding lab; 3D printing lab"),
      el("li", {}, el("b", { text: "Build: " }), "wood and metal shop"),
      el("li", {}, el("b", { text: "Well: " }), "counseling rooms, youth lounge, fitness and rec"),
      el("li", {}, el("b", { text: "Navigate: " }), "career and resource center")),
    el("p", { class: "note", text: "Choose a room to see what it is for, what the plan says it would cost, and how to reserve interest. " + D.meta.sfNote }),
    el("h3", { text: "Giving tiers" }),
    ...D.tiers.map((t) => el("div", { class: "opp" }, el("div", {}, el("div", { class: "t", text: `${t.name} · ${tierAmt(t)}` }), el("div", { class: "d", text: t.funds })), el("a", { href: "/join/?interest=" + encodeURIComponent("tier:" + t.id) }, "Reserve interest"))),
    el("p", { class: "note", text: "Tiers from the campaign plan. The Founding Partner amount is a proposal. Reserving interest does not take a donation." }));
}
function select(id, flyTo) {
  if (!ROOMS[id]) return;
  selected = id;
  highlight(id);
  const r = ROOMS[id];
  const [x0, y0, x1, y1] = r.rect;
  const cx = (x0 + x1) / 2, cy = (y0 + y1) / 2;
  const span = Math.max(x1 - x0, y1 - y0);
  if (flyTo && USE_3D) fly(new THREE.Vector3(cx + span + 40, span * 1.2 + 60, -cy + span * 1.1 + 55), new THREE.Vector3(cx, 2, -cy));
  renderRoom(r);
  document.querySelectorAll("#floorplan a[data-room]").forEach((a) => a.classList.toggle("is-sel", a.dataset.room === id));
  const jump = document.getElementById("room-jump");
  if (jump) jump.value = id;
  const url = new URL(location.href);
  url.searchParams.set("room", id);
  history.replaceState(null, "", url.pathname + url.search);
}
function showWing(wid) {
  const ids = D.rooms.filter((r) => r.wing === wid).map((r) => r.id);
  document.querySelectorAll("#floorplan a[data-room]").forEach((a) => a.classList.toggle("is-dim", !ids.includes(a.dataset.room)));
  if (ids.length === 1) return select(ids[0], true);
  for (const [rid, f] of Object.entries(floors)) {
    f.material.emissive.set(ids.includes(rid) ? "#ffd27a" : "#000");
    f.material.emissiveIntensity = ids.includes(rid) ? 0.5 : 0;
  }
  const w = D.wings[wid];
  panel.replaceChildren(
    el("div", { class: "kicker", style: `color:${w.color}`, text: wid === "shared" ? "Shared spaces" : wid === "site" ? "Court and parking" : `${w.name} wing` }),
    el("h2", { text: wid === "shared" ? "Shared spaces" : wid === "site" ? "Court and parking" : `${w.name} wing` }));
  const ws = D.slots.find((s) => s.id === "wing:" + wid);
  if (ws) panel.append(oppRow(ws.label, "Name the whole wing. No wing amount is set in the campaign plan; Founding Partner is the only naming tier.", [ws], TIERS.founding, true));
  for (const id of ids) {
    const r = ROOMS[id];
    panel.append(el("div", { class: "opp" },
      el("div", {}, el("div", { class: "t", text: r.name }), el("div", { class: "d", text: `${r.sf.toLocaleString()} sf · ${r.fundTotal != null ? money(r.fundTotal) + " to build out and equip" : "not priced"}` })),
      el("button", { type: "button", onclick: () => select(id, true) }, "Open")));
  }
}
function openWall() {
  const wallDlg = document.getElementById("wall");
  if (!wallDlg || typeof wallDlg.showModal !== "function") { location.href = "/donors/"; return; }
  const body = el("div", { class: "dlg" },
    el("h2", { id: "wall-title", text: "Founding Donors" }),
    el("p", { class: "note", text: `${D.slots.length} naming opportunities from the plan. Every slot is Available. No names are shown.` }));
  for (const t of [...D.tiers].reverse()) {
    const ss = D.slots.filter((s) => s.tier === t.id);
    if (!ss.length) continue;
    body.append(el("h3", { class: "tierhead" }, `${t.name} · ${tierAmt(t)} `, el("span", { text: `${ss.length} available` })));
    const grid = el("div", { class: "wallgrid" });
    for (const s of ss) {
      grid.append(el("div", { class: "slot" },
        el("div", { class: "who", text: "Available" }),
        el("div", { class: "what" }, s.label, s.status === "proposed" ? " (proposed)" : ""),
        el("button", { type: "button", onclick: () => reserveInterest(s) }, "Reserve interest")));
    }
    body.append(grid);
  }
  body.append(el("div", { class: "row" },
    el("a", { href: "/donors/" }, "Open the full wall"),
    el("button", { type: "button", class: "primary", onclick: () => wallDlg.close() }, "Close")));
  wallDlg.replaceChildren(body);
  if (!wallDlg.open) wallDlg.showModal();
}

const legend = document.getElementById("legend");
if (legend) {
  for (const [wid, w] of Object.entries(D.wings)) {
    const name = wid === "site" ? "Court and parking" : wid === "shared" ? "Shared spaces" : `${w.name} wing`;
    legend.append(el("button", { type: "button", onclick: () => showWing(wid) }, el("i", { style: `background:${w.color}` }), name));
  }
}

function start3d() {

  const vp = document.getElementById("viewport");
  renderer = new THREE.WebGLRenderer({ antialias: true, preserveDrawingBuffer: false });
  renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFShadowMap;
  vp.append(renderer.domElement);
  labelR = new CSS2DRenderer();
  labelR.domElement.style.position = "absolute";
  labelR.domElement.style.top = "0";
  labelR.domElement.style.pointerEvents = "none";
  vp.append(labelR.domElement);

  scene = new THREE.Scene();
  scene.background = new THREE.Color("#eef0ea");
  scene.fog = new THREE.Fog("#eef0ea", 420, 900);
  camera = new THREE.PerspectiveCamera(40, 1, 1, 3000);
  controls = new OrbitControls(camera, renderer.domElement);
  controls.enableDamping = !REDUCE;
  controls.maxPolarAngle = Math.PI * 0.47;
  controls.minDistance = 40; controls.maxDistance = 700;

  scene.add(new THREE.HemisphereLight("#ffffff", "#b9b4a6", 1.6));
  const sun = new THREE.DirectionalLight("#fff4e2", 2.2);
  sun.position.set(-160, 260, 180); sun.castShadow = true;
  sun.shadow.mapSize.set(2048, 2048);
  Object.assign(sun.shadow.camera, { left: -260, right: 260, top: 260, bottom: -260, near: 10, far: 900 });
  sun.target.position.set(80, 0, -20);
  scene.add(sun, sun.target);

  model = new THREE.Group(); model.name = "AzaleaYard_Concept"; scene.add(model);
  const mats = {};
  const mat = (hex, o = {}) => (mats[hex + JSON.stringify(o)] ||= new THREE.MeshStandardMaterial({ color: hex, flatShading: true, roughness: 0.85, metalness: 0.0, ...o }));
  const W = (x, y) => new THREE.Vector3(x, 0, -y); // plan (x,y ft) -> world
  function box(group, w, h, d, x, y, z, color, roomId, o = {}) {
    const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat(color, o));
    m.position.set(x, z + h / 2, -y); m.castShadow = true; m.receiveShadow = true;
    if (roomId) { m.userData.roomId = roomId; pickables.push(m); }
    group.add(m); return m;
  }
  function cyl(group, r, h, x, y, z, color, roomId, seg = 8) {
    const m = new THREE.Mesh(new THREE.CylinderGeometry(r, r, h, seg), mat(color));
    m.position.set(x, z + h / 2, -y); m.castShadow = true;
    if (roomId) { m.userData.roomId = roomId; pickables.push(m); }
    group.add(m); return m;
  }

  // ground
  const ground = new THREE.Mesh(new THREE.PlaneGeometry(1400, 1400), mat("#cfd8c4"));
  ground.rotation.x = -Math.PI / 2; ground.position.set(100, -0.05, 0); ground.receiveShadow = true; scene.add(ground);
  // walkway from parking to entry
  box(model, 14, 0.2, 14, 60, -7, 0, "#e4ddd0");
  box(model, 240, 0.2, 6, 120, -3 + 0, 0, "#e4ddd0").position.z = 3; // front sidewalk along building

  const WALL_H = 9, WALL_T = 0.6;
  const wallKeys = new Set();
  const roomGroups = {};

  function wallSeg(g, x0, y0, x1, y1, roomId, gaps = []) {
    const key = [x0, y0, x1, y1].join(",");
    if (wallKeys.has(key)) return; wallKeys.add(key);
    const horiz = y0 === y1;
    const a = horiz ? x0 : y0, b = horiz ? x1 : y1;
    const pieces = []; let cur = a;
    for (const [g0, g1] of gaps.sort((p, q) => p[0] - q[0])) { if (g0 > cur) pieces.push([cur, g0]); cur = g1; }
    if (cur < b) pieces.push([cur, b]);
    for (const [p0, p1] of pieces) {
      const len = p1 - p0, mid = (p0 + p1) / 2;
      if (horiz) box(g, len, WALL_H, WALL_T, mid, y0, 0, "#f4f1ec", roomId);
      else box(g, WALL_T, WALL_H, len, x0, mid, 0, "#f4f1ec", roomId);
    }
  }
  // door gaps (concept): every room opens to the corridor
  const DOORS = {
    // edge key -> gaps
    "40,0,80,0": [[54, 66]], // main entry
    "90,100,120,100": [[100, 112]], // shop roll-up door
  };
  function edgeGaps(x0, y0, x1, y1) {
    const k = [x0, y0, x1, y1].join(",");
    if (DOORS[k]) return DOORS[k];
    const gaps = [];
    if (y0 === y1 && (y0 === 30 || y0 === 38) && x1 - x0 > 8) { const m = (x0 + x1) / 2; gaps.push([m - 2.5, m + 2.5]); }
    return gaps;
  }

  for (const r of D.rooms) {
    const [x0, y0, x1, y1] = r.rect; const w = x1 - x0, d = y1 - y0; const cx = (x0 + x1) / 2, cy = (y0 + y1) / 2;
    const g = new THREE.Group(); g.name = r.id; g.userData = { name: r.name, wing: r.wing, sf: r.sf }; model.add(g); roomGroups[r.id] = g;
    const color = D.wings[r.wing].color;
    let floorColor = r.inBuilding ? new THREE.Color(color).lerp(new THREE.Color("#ffffff"), 0.55).getStyle() : color;
    if (r.id === "corridor") floorColor = "#ece7de";
    if (r.id === "court") floorColor = "#c8754b";
    if (r.id === "parking") floorColor = "#7d8589";
    const f = box(g, w - 0.2, 0.5, d - 0.2, cx, cy, 0, floorColor, r.id, { roughness: 0.95 });
    f.material = f.material.clone(); f.name = r.id + "_floor"; floors[r.id] = f;
    if (r.inBuilding && r.id !== "corridor") {
      wallSeg(g, x0, y0, x1, y0, r.id, edgeGaps(x0, y0, x1, y0));
      wallSeg(g, x0, y1, x1, y1, r.id, edgeGaps(x0, y1, x1, y1));
      wallSeg(g, x0, y0, x0, y1, r.id); wallSeg(g, x1, y0, x1, y1, r.id);
    }
    furnish(g, r);
    const lab = el("div", { class: "label" + (r.inBuilding ? "" : " site") }, r.name, el("small"));
    lab.addEventListener("click", (e) => { e.stopPropagation(); select(r.id, true); });
    lab.tabIndex = 0; lab.setAttribute("role", "button");
    lab.addEventListener("keydown", (e) => { if (e.key === "Enter" || e.key === " ") { e.preventDefault(); select(r.id, true); } });
    const o = new CSS2DObject(lab); o.position.set(cx, r.inBuilding ? WALL_H + 3 : 3, -cy); g.add(o); labels[r.id] = lab;
    if (r.id === "corridor") o.visible = false;
  }
  // exterior wall accent band (low-poly parapet hint on the outer shell)
  for (const [x0, y0, x1, y1] of [[0, 0, 120, 0], [0, 100, 120, 100], [0, 0, 0, 100], [120, 0, 120, 100]]) {
    const horiz = y0 === y1; const len = horiz ? x1 - x0 : y1 - y0;
    const b = horiz ? box(model, len + 1.2, 1.2, 1.4, (x0 + x1) / 2, y0, WALL_H, "#3d4a4f") : box(model, 1.4, 1.2, len + 1.2, x0, (y0 + y1) / 2, WALL_H, "#3d4a4f");
    b.castShadow = false;
  }
  // entry canopy + sign
  box(model, 26, 0.8, 10, 60, -4, 11, "#3d4a4f");
  const sign = box(model, 22, 3.2, 0.6, 60, -0.8, 12, "#b5446e");

  // ---------- furniture (low-poly props) ----------
  function rows(g, r, nx, ny, fn) {
    const [x0, y0, x1, y1] = r.rect; const mx = 3, my = 3;
    for (let i = 0; i < nx; i++) for (let j = 0; j < ny; j++) {
      const x = x0 + mx + (i + 0.5) * (x1 - x0 - 2 * mx) / nx, y = y0 + my + (j + 0.5) * (y1 - y0 - 2 * my) / ny;
      fn(x, y, i, j);
    }
  }
  function furnish(g, r) {
    const id = r.id, [x0, y0, x1, y1] = r.rect;
    const wood = "#c9a77c", dark = "#3a3f44", white = "#fafafa";
    switch (r.furniture) {
      case "art": rows(g, r, 2, 4, (x, y) => { box(g, 6, 0.3, 2.6, x, y, 2.6, white, id); box(g, 5.6, 2.6, 0.3, x, y, 0, "#9aa0a6", id); }); box(g, 0.4, 5, 12, x0 + 0.8, (y0 + y1) / 2, 2, "#f2d4c9", id); break;
      case "ceramics": rows(g, r, 4, 2, (x, y) => { cyl(g, 1.1, 2, x, y - 4, 0, "#8a8f94", id); cyl(g, 0.9, 0.2, x, y - 4, 2, "#b8a48d", id); });
        cyl(g, 1.7, 3, x0 + 5, y1 - 4, 0, "#6c7277", id, 8); cyl(g, 1.7, 3, x0 + 10, y1 - 4, 0, "#6c7277", id, 8); box(g, 16, 0.3, 2.5, (x0 + x1) / 2, y0 + 2, 3, wood, id); break;
      case "music": for (let k = 0; k < 3; k++) { const by = y0 + 5 + k * 9; box(g, 7, 7.5, 7, x1 - 4.2, by, 0, "#2d3436", id); box(g, 0.2, 4, 4.5, x1 - 7.8, by, 2.5, "#9fd3e6", id, { transparent: true, opacity: 0.6 }); }
        box(g, 6, 3, 2.2, x0 + 4, y0 + 8, 0, dark, id); box(g, 5, 0.6, 2, x0 + 4, y0 + 16, 2.2, "#111", id); box(g, 5, 0.6, 2, x0 + 4, y0 + 22, 2.2, "#111", id); break;
      case "screening": { const sx = (x0 + x1) / 2; box(g, 13, 6, 0.3, sx, y1 - 1, 2, white, id);
        for (let k = 0; k < 4; k++) box(g, 12, 1 + k * 0.9, 3.2, sx, y0 + 4 + k * 4, 0, "#5b3f52", id); } break;
      case "coding": rows(g, r, 4, 5, (x, y) => { box(g, 4, 0.25, 2.2, x, y, 2.5, white, id); box(g, 1.8, 1.3, 0.15, x, y + 0.5, 2.75, "#cfd6dd", id); }); break;
      case "printing": box(g, 12, 3, 2.5, (x0 + x1) / 2, y1 - 3, 0, wood, id); for (let k = 0; k < 4; k++) box(g, 1.6, 1.8, 1.6, x0 + 3 + k * 3.2, y1 - 3, 3, "#2f3640", id); box(g, 8, 0.3, 4, (x0 + x1) / 2, y0 + 12, 2.6, white, id); break;
      case "film": box(g, 18, 9, 0.6, (x0 + x1) / 2, y1 - 1.5, 0, "#d9d9d9", id); box(g, 18, 0.3, 8, (x0 + x1) / 2, y1 - 6, 0, "#e9e9e9", id);
        for (let k = 0; k < 3; k++) { const m = new THREE.Mesh(new THREE.ConeGeometry(1, 5, 3), mat(dark)); m.position.set(x0 + 6 + k * 6, 2.5, -(y0 + 12)); m.userData.roomId = id; pickables.push(m); g.add(m); } break;
      case "shop": rows(g, r, 3, 2, (x, y) => { box(g, 7, 3, 3.2, x, y, 0, wood, id); }); box(g, 4, 3, 3, x0 + 5, y0 + 4, 0, "#c0392b", id); for (let k = 0; k < 2; k++) box(g, 5, 7, 0.3, x1 - 3 - k * 6, y0 + 3, 0, "#3b6e8f", id); break;
      case "fitness": box(g, 9, 2.5, 5, x0 + 8, y0 + 14, 0, "#1f5fa8", id); box(g, 9, 2.5, 5, x0 + 22, y0 + 14, 0, "#1f5fa8", id);
        for (let k = 0; k < 4; k++) box(g, 3, 0.25, 6, x0 + 5 + k * 6.5, y0 + 40, 0.5, "#e07a5f", id); box(g, 20, 0.2, 10, x0 + 15, y0 + 52, 0.5, "#9dc3a8", id); break;
      case "lounge": box(g, 10, 2.4, 3, x0 + 8, y0 + 6, 0, "#7a8f6a", id); box(g, 3, 2.4, 9, x0 + 3, y0 + 13, 0, "#7a8f6a", id); box(g, 5, 1.2, 3, x0 + 9, y0 + 13, 0, wood, id); box(g, 8, 3, 2.4, x0 + 16, y0 + 24, 0, "#7a8f6a", id); break;
      case "counsel": for (let k = 1; k < 3; k++) box(g, 16, WALL_H, 0.5, (x0 + x1) / 2, y0 + k * 10, 0, "#f4f1ec", id);
        for (let k = 0; k < 3; k++) { box(g, 2.2, 2.4, 2.2, x0 + 5, y0 + 5 + k * 10, 0, "#81b29a", id); box(g, 2.2, 2.4, 2.2, x0 + 10, y0 + 5 + k * 10, 0, "#81b29a", id); } break;
      case "lobby": box(g, 12, 3.5, 3, x0 + 10, y0 + 18, 0, wood, id);
        for (let k = 0; k < 4; k++) box(g, 0.4, 6, 6, x0 + 22 + k * 4.5, y0 + 15, 1, white, id);
        { const dw = box(g, 0.6, 7, 22, x1 - 1.2, y0 + 15, 1, "#5a463c", id); dw.name = "donor_wall";
          const dl = el("div", { class: "label" }, "Founding Donors wall", el("small", { text: "click to view" }));
          dl.addEventListener("click", (e) => { e.stopPropagation(); openWall(); });
          dl.tabIndex = 0; dl.setAttribute("role", "button");
          dl.addEventListener("keydown", (e) => { if (e.key === "Enter" || e.key === " ") { e.preventDefault(); openWall(); } });
          const o = new CSS2DObject(dl); o.position.set(x1 - 2, 6, -(y0 + 15)); g.add(o); } break;
      case "navigate": box(g, 10, 3.5, 3, x0 + 7, y0 + 6, 0, "#4a7bc8", id); rows(g, { rect: [x0, y0 + 10, x1, y1] }, 2, 2, (x, y) => box(g, 6, 2.6, 3, x, y, 0, white, id)); break;
      case "offices": for (let k = 1; k < 3; k++) box(g, 16, WALL_H, 0.5, (x0 + x1) / 2, y0 + k * 10, 0, "#f4f1ec", id); for (let k = 0; k < 3; k++) box(g, 5, 2.6, 2.4, x0 + 7, y0 + 5 + k * 10, 0, wood, id); break;
      case "restrooms": box(g, 16, WALL_H, 0.5, (x0 + x1) / 2, (y0 + y1) / 2, 0, "#f4f1ec", id); break;
      case "court": { const cx = (x0 + x1) / 2, cy = (y0 + y1) / 2; box(g, 0.4, 0.06, y1 - y0 - 2, cx, cy, 0.5, white, id);
        const ring = new THREE.Mesh(new THREE.TorusGeometry(6, 0.2, 4, 24), mat(white)); ring.rotation.x = Math.PI / 2; ring.position.set(cx, 0.6, -cy); g.add(ring);
        for (const hx of [x0 + 2, x1 - 2]) { cyl(g, 0.3, 10, hx, cy, 0, dark, id, 6); box(g, 0.3, 3.5, 6, hx + (hx < cx ? 0.6 : -0.6), cy, 9, white, id); } } break;
      case "parking": for (const ry of [y0 + 2, y1 - 20, y0 + 28, y0 + 46]) for (let k = 0; k <= 13; k++) box(g, 0.35, 0.06, 18, x0 + 2 + k * 9, ry + 9, 0.5, white, id); break;
    }
  }

  const CENTER = new THREE.Vector3(100, 0, -20);
  fly = function (pos, target, ms = 900) {
    if (REDUCE) {
      camera.position.copy(pos);
      controls.target.copy(target);
      controls.update();
      return;
    }
    const p0 = camera.position.clone(), t0 = controls.target.clone(), st = performance.now();
    const step = (now) => {
      const k = Math.min(1, (now - st) / ms);
      const e = k < 0.5 ? 2 * k * k : 1 - ((-2 * k + 2) ** 2) / 2;
      camera.position.lerpVectors(p0, pos, e);
      controls.target.lerpVectors(t0, target, e);
      if (k < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };
  overview = (ms) => fly(new THREE.Vector3(205, 165, 120), new THREE.Vector3(85, 0, -35), ms);
  topView = () => fly(new THREE.Vector3(100.01, 420, -19.9), CENTER);
  camera.position.set(205, 165, 120);
  controls.target.set(85, 0, -35);
  highlight = function (id) {
    for (const [rid, f] of Object.entries(floors)) {
      f.material.emissive.set(rid === id ? "#ffd27a" : "#000000");
      f.material.emissiveIntensity = rid === id ? 0.55 : 0;
    }
    for (const [rid, l] of Object.entries(labels)) l.classList.toggle("sel", rid === id);
  };

  const ray = new THREE.Raycaster(), ptr = new THREE.Vector2();
  let down = null;
  renderer.domElement.addEventListener("pointerdown", (e) => { down = [e.clientX, e.clientY]; });
  renderer.domElement.addEventListener("pointerup", (e) => {
    if (!down || Math.hypot(e.clientX - down[0], e.clientY - down[1]) > 5) return;
    const b = renderer.domElement.getBoundingClientRect();
    ptr.set(((e.clientX - b.left) / b.width) * 2 - 1, -((e.clientY - b.top) / b.height) * 2 + 1);
    ray.setFromCamera(ptr, camera);
    const hit = ray.intersectObjects(pickables, false).find((h) => h.object.userData.roomId && h.object.userData.roomId !== "corridor");
    if (hit) select(hit.object.userData.roomId, true);
  });
  renderer.domElement.addEventListener("pointermove", (e) => {
    const b = renderer.domElement.getBoundingClientRect();
    ptr.set(((e.clientX - b.left) / b.width) * 2 - 1, -((e.clientY - b.top) / b.height) * 2 + 1);
    ray.setFromCamera(ptr, camera);
    const h = ray.intersectObjects(pickables, false)[0];
    renderer.domElement.style.cursor = h && h.object.userData.roomId !== "corridor" ? "pointer" : "grab";
  });
  function resize() {
    const w = vp.clientWidth, h = vp.clientHeight;
    if (!w || !h) return;
    renderer.setSize(w, h);
    labelR.setSize(w, h);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  }
  addEventListener("resize", resize);
  resize();
  const draw = () => { renderer.render(scene, camera); labelR.render(scene, camera); };
  if (REDUCE) {
    controls.addEventListener("change", draw);
    draw();
  } else {
    renderer.setAnimationLoop(() => { controls.update(); renderer.render(scene, camera); labelR.render(scene, camera); });
  }
}

if (!webglOk()) document.querySelectorAll(".js-load-3d").forEach((a) => a.remove());
if (!USE_3D) document.getElementById("btn-top")?.setAttribute("hidden", "");
if (USE_3D) {
  document.documentElement.classList.add("is-3d");
  const viewport = document.getElementById("viewport");
  viewport.hidden = false;
  start3d();
  const hint = document.getElementById("hint");
  if (hint) hint.textContent = REDUCE
    ? "Click a room or use Jump to a room. Camera moves are immediate."
    : "Drag to orbit, scroll to zoom, right-drag to pan, click a room. Or use Jump to a room.";
}
document.getElementById("btn-overview")?.addEventListener("click", () => {
  selected = null;
  highlight(null);
  document.querySelectorAll("#floorplan a").forEach((a) => a.classList.remove("is-dim", "is-sel"));
  renderWelcome();
  overview();
  const url = new URL(location.href);
  url.searchParams.delete("room");
  history.replaceState(null, "", url.pathname + url.search);
  const jump = document.getElementById("room-jump");
  if (jump) jump.value = "";
});
document.getElementById("btn-top")?.addEventListener("click", () => { if (USE_3D) topView(); });
document.getElementById("btn-wall")?.addEventListener("click", (e) => { e.preventDefault(); openWall(); });
document.getElementById("room-jump")?.addEventListener("change", (e) => { if (e.target.value) select(e.target.value, true); });
document.getElementById("reserve-cancel")?.addEventListener("click", () => document.getElementById("reserve")?.close());
document.querySelectorAll("#floorplan a[data-room]").forEach((a) => {
  a.addEventListener("click", (e) => {
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
    e.preventDefault();
    select(a.dataset.room, true);
  });
});
const room = params.get("room");
if (room && ROOMS[room]) select(room, true); else renderWelcome();
const preset = params.get("slot");
if (preset) {
  const slot = D.slots.find((s) => s.id === preset);
  if (slot) reserveInterest(slot);
}
window.__ready = true;
