<?php
declare(strict_types=1);

function azalea_hex_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function azalea_mix(string $hex, string $with, float $amount): string
{
    [$r, $g, $b] = azalea_hex_rgb($hex);
    [$r2, $g2, $b2] = azalea_hex_rgb($with);
    $mix = static fn (int $a, int $b): int => (int) round($a + ($b - $a) * $amount);
    return sprintf('#%02x%02x%02x', $mix($r, $r2), $mix($g, $g2), $mix($b, $b2));
}

function azalea_wrap_label(string $text, int $maxChars): array
{
    $words = preg_split('/\s+/', trim($text)) ?: [];
    $lines = [];
    $current = '';
    foreach ($words as $word) {
        $try = $current === '' ? $word : $current . ' ' . $word;
        if (mb_strlen($try) <= $maxChars) {
            $current = $try;
            continue;
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        $current = $word;
    }
    if ($current !== '') {
        $lines[] = $current;
    }
    if (count($lines) > 3) {
        $lines = [ $lines[0], $lines[1], implode(' ', array_slice($lines, 2)) ];
    }
    return $lines;
}

/**
 * Concept plan. Plan y grows toward the back of the building; the drawing
 * flips it so parking (negative plan y) sits at the bottom of the figure.
 */
function azalea_floorplan_svg(string $context): string
{
    $plan = azalea_plan();
    $wings = $plan['wings'];
    $svg = [];
    $svg[] = '<svg class="floorplan" viewBox="-10 -114 248 224" aria-labelledby="floorplan-title floorplan-desc">';
    $svg[] = '<title id="floorplan-title">Azalea Yard concept floor plan</title>';
    $svg[] = '<desc id="floorplan-desc">Not to scale. A one-story 120 by 100 foot building colored by program track, parking to the south, and an outdoor court to the east. Room sizes are a concept allocation of 12,000 square feet.</desc>';
    $svg[] = '<rect x="-8" y="-110" width="242" height="216" rx="4" fill="#e5f0e6"/>';

    foreach ($plan['rooms'] as $room) {
        [$x0, $y0, $x1, $y1] = $room['rect'];
        $width = $x1 - $x0;
        $height = $y1 - $y0;
        $x = $x0 + 0.35;
        $y = -$y1 + 0.35;
        $w = max(0.5, $width - 0.7);
        $h = max(0.5, $height - 0.7);
        $wing = $wings[$room['wing']]['color'] ?? '#cccccc';
        if ($room['id'] === 'parking') {
            $fill = '#7d8589';
            $ink = '#f4efe6';
        } elseif ($room['id'] === 'court') {
            $fill = '#d08a62';
            $ink = '#1a2420';
        } elseif ($room['id'] === 'corridor') {
            $fill = '#efeae2';
            $ink = '#1a2420';
        } else {
            $fill = azalea_mix($wing, '#ffffff', 0.42);
            $ink = '#1a2420';
        }
        $href = $context === 'tour'
            ? '?room=' . rawurlencode($room['id']) . '&view=list'
            : '/tour/?room=' . rawurlencode($room['id']);
        $font = min($w, $h) < 16 ? 2.45 : 3.15;
        $maxChars = max(6, (int) floor($w / ($font * 0.55)));
        $lines = $room['id'] === 'corridor'
            ? ['Main corridor']
            : azalea_wrap_label($room['name'], $maxChars);
        $lineH = $font * 1.18;
        $block = count($lines) * $lineH + 2.3;
        $cx = $x + $w / 2;
        $cy = $y + $h / 2;
        $textY = $cy - $block / 2 + $font;

        $svg[] = '<a href="' . h($href) . '" data-room="' . h($room['id']) . '" data-wing="' . h($room['wing']) . '">';
        $svg[] = '<rect x="' . round($x, 2) . '" y="' . round($y, 2) . '" width="' . round($w, 2) . '" height="' . round($h, 2) . '" rx="0.8" fill="' . $fill . '"/>';
        $svg[] = '<text x="' . round($cx, 2) . '" y="' . round($textY, 2) . '" text-anchor="middle" fill="' . $ink . '" font-size="' . $font . '" font-weight="650">';
        foreach ($lines as $i => $line) {
            $dy = $i === 0 ? '0' : (string) round($lineH, 2);
            $svg[] = '<tspan x="' . round($cx, 2) . '" dy="' . $dy . '">' . h($line) . '</tspan>';
        }
        $svg[] = '</text>';
        if ($room['id'] !== 'corridor' && $h > 12) {
            $sfY = $textY + count($lines) * $lineH + 0.4;
            $svg[] = '<text x="' . round($cx, 2) . '" y="' . round($sfY, 2) . '" text-anchor="middle" fill="' . $ink . '" opacity="0.75" font-size="2.15">' . h(number_format((int) $room['sf']) . ' sf') . '</text>';
        }
        $svg[] = '</a>';
    }
    $svg[] = '</svg>';
    return implode('', $svg);
}
