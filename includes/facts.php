<?php
declare(strict_types=1);

function azalea_mission(): string
{
    return 'Azalea Yard gives young adults in Valdosta and Lowndes County, ages 16 to 24, a year-round place to make art, learn job-ready skills from digital media and coding to the skilled trades, take care of their well-being, and connect with the local resources already meant for them.';
}

function azalea_vision(): string
{
    return 'Every young adult in South Georgia knows where they belong, what they can build, and who has their back.';
}

function azalea_tracks(): array
{
    return [
        [
            'id' => 'create',
            'name' => 'Create',
            'color' => '#E07A5F',
            'summary' => 'Art studio, ceramics, music and recording booths, film and media, and a screening room.',
        ],
        [
            'id' => 'tech',
            'name' => 'Tech',
            'color' => '#2A9D8F',
            'summary' => 'A computer, AI, and coding lab, plus a 3D printing lab.',
        ],
        [
            'id' => 'build',
            'name' => 'Build',
            'color' => '#C9852A',
            'summary' => 'A wood and metal shop for carpentry, welding, and tool safety, with referrals toward credentials at Wiregrass Georgia Technical College.',
        ],
        [
            'id' => 'well',
            'name' => 'Well',
            'color' => '#3E8F6E',
            'summary' => 'Counseling rooms, a youth lounge, and fitness and recreation.',
        ],
        [
            'id' => 'navigate',
            'name' => 'Navigate',
            'color' => '#3D6A9A',
            'summary' => 'A career and resource center connecting young people to GED options, job-training funds, college, and health coverage.',
        ],
    ];
}

function azalea_need_stats(): array
{
    return [
        [
            'figure' => '23,474',
            'label' => 'Lowndes County residents ages 15 to 24',
            'detail' => '19.6% of the county. In the city of Valdosta the same age group is 13,205 people, 23.9% of the city.',
            'source' => 'ACS 2024 5-year table B01001, via Census Reporter',
            'url' => 'https://censusreporter.org/profiles/05000US13185-lowndes-county-ga/',
        ],
        [
            'figure' => '25.9%',
            'label' => 'of Lowndes County 18- to 24-year-olds live below the poverty line',
            'detail' => '4,253 people. In Valdosta the rate is 33.7% (3,310 people). This count includes off-campus college students.',
            'source' => 'ACS 2024 5-year table B17001, via Census Reporter',
            'url' => 'https://censusreporter.org/profiles/05000US13185-lowndes-county-ga/',
        ],
        [
            'figure' => '11.5%',
            'label' => 'of Lowndes County 18- to 24-year-olds have no high school diploma',
            'detail' => '2,136 people. In Valdosta the share is 9.4% (1,078 people).',
            'source' => 'ACS 2024 5-year table B15001, via Census Reporter',
            'url' => 'https://censusreporter.org/profiles/05000US13185-lowndes-county-ga/',
        ],
        [
            'figure' => '6.8%',
            'label' => 'unemployment in the Lowndes County civilian labor force, ages 16 to 24',
            'detail' => '800 of 11,809 people. In Valdosta the rate is 5.8%.',
            'source' => 'ACS 2024 5-year table B23001, via Census Reporter',
            'url' => 'https://censusreporter.org/profiles/05000US13185-lowndes-county-ga/',
        ],
        [
            'figure' => '6.0%',
            'label' => 'of Lowndes County youth ages 16 to 19 were not in school and not working in 2024',
            'detail' => 'The same measure was 11.0% in 2020. A county figure for ages 16 to 24 was not found; Measure of America publishes that wider rate for states, metros, and congressional districts, not for this county.',
            'source' => 'FRED series B14005DCYACS013185, U.S. Census Bureau',
            'url' => 'https://fred.stlouisfed.org/series/B14005DCYACS013185',
        ],
        [
            'figure' => '15.3%',
            'label' => 'of Lowndes County residents under 65 have no health insurance',
            'detail' => 'Median household income is $58,541. The poverty rate for all ages is 18.1% in Census QuickFacts and 19.2% in ACS table B17001. County population is 119,965 in the ACS 2020–24 estimate and 122,867 in the Census estimate for July 2025.',
            'source' => 'U.S. Census Bureau QuickFacts, Lowndes County, Georgia',
            'url' => 'https://www.census.gov/quickfacts/lowndescountygeorgia',
        ],
    ];
}

function azalea_local_programs(): array
{
    return [
        [
            'name' => 'Boys & Girls Club of Valdosta',
            'note' => 'K–5 units and the Donald F. Reames Teen Center for middle and high school. FY2023 revenue was $3.39 million, of which $3.05 million (about 90%) came from contributions.',
            'url' => 'https://www.bgcsga.org/club-locations---maps',
            'source_name' => 'ProPublica Nonprofit Explorer, EIN 23-7067775',
            'source_url' => 'https://projects.propublica.org/nonprofits/organizations/237067775',
        ],
        [
            'name' => 'Annette Howell Turner Center for the Arts',
            'note' => 'Youth visual and digital art classes, including digital art for ages 12 to 18, pottery, and a Young Writers League.',
            'url' => 'https://turnercenter.org/for-youth-visual-digital-arts/',
            'source_name' => 'Turner Center for the Arts',
            'source_url' => 'https://turnercenter.org/for-youth-visual-digital-arts/',
        ],
        [
            'name' => 'WorkSource Southern Georgia',
            'note' => 'WIOA youth services for ages 16 to 24, including training, tuition, GED support, employment help, transportation, and childcare. The office listed in the plan is 221 S. Ashley St.',
            'url' => 'https://www.sgrc.us/wioa-assistance-location.html',
            'source_name' => 'Southern Georgia Regional Commission',
            'source_url' => 'https://www.sgrc.us/wioa-assistance-location.html',
        ],
        [
            'name' => 'Wiregrass Georgia Technical College and Valdosta State University',
            'note' => 'Named in the plan as credential and degree pathways. Specific programs had not been researched as of the October 30, 2026 drafts.',
            'url' => '',
            'source_name' => '',
            'source_url' => '',
        ],
        [
            'name' => 'Valdosta-Lowndes Parks & Recreation Authority',
            'note' => 'Parks and recreation facilities in the county.',
            'url' => 'https://www.vlpra.com/',
            'source_name' => 'VLPRA',
            'source_url' => 'https://www.vlpra.com/',
        ],
    ];
}

function azalea_budget_tables(): array
{
    $reno = 'https://noboconstruction.com/blog/commercial-tenant-improvement-costs-georgia';
    $sos = 'https://sos.ga.gov/sites/default/files/forms/Filing%20Procedure%20-%20Corporation_1.pdf';
    $irs = 'https://www.irs.gov/charities-non-profits/form-1023-and-1023-ez-amount-of-user-fee';
    $hwy = 'https://www.loopnet.com/Listing/19358-Valdosta-Hwy-Valdosta-GA/36559625/';
    $north = 'https://www.cimls.com/lease-listing/492817/industrial-4354-val-north-drive-Valdosta-GA';
    $eia = 'https://www.eia.gov/consumption/commercial/data/2018/ce/pdf/c18.pdf';
    $rate = 'https://www.eia.gov/electricity/monthly/epm_table_grapher.php?t=epmt_5_6_a';
    $bls = 'https://www.bls.gov/oes/current/oes_46660.htm';
    $ecec = 'https://www.bls.gov/news.release/ecec.t01.htm';
    $ins = 'https://www.insureon.com/nonprofit-business-insurance/cost';
    $reg = 'https://law.justia.com/codes/georgia/title-14/chapter-3/article-1/part-2/section-14-3-122/';

    return [
        'full' => [
            'title' => 'Full concept, 12,000 sf',
            'note' => 'Lease-and-renovate working plan. The site has not been chosen. One story, 120 by 100 feet. Startup plus the first year of operating is $2,657,074.',
            'startup_total' => 1322538,
            'ops_total' => 1334536,
            'combined' => 2657074,
            'equipment' => 81633,
            'startup' => [
                ['Renovation hard cost', '12,000 sf × $80/sf, low end of the cited office full build-out range', 960000, $reno],
                ['Architectural and engineering drawings', '12,000 sf × $7/sf', 84000, $reno],
                ['Permits and impact fees', 'High end of the cited $2,000–$15,000 range', 15000, $reno],
                ['Signage', 'Low end of the cited $10,000–$75,000 range', 10000, $reno],
                ['Soft-cost and contingency buffer', '15% of hard cost', 144000, $reno],
                ['Lease security deposit', 'Assumption: two months of rent at the cited rate', 27160, $hwy],
                ['Equipment and furniture', 'Sum of the itemized list', 81633, '/support/#equipment'],
                ['Georgia nonprofit incorporation', '$100 SOS fee + $5 online + $40 legal notice', 145, $sos],
                ['IRS Form 1023 user fee', 'Full Form 1023. The budget is above the Form 1023-EZ limits.', 600, $irs],
            ],
            'operating' => [
                ['Staff wages', '13 roles, 16 FTE, BLS OEWS May 2025 mean wages', 797290, $bls],
                ['Benefits and payroll taxes', '30% of total compensation, equal to 42.9% of wages (BLS ECEC, private industry, June 2026)', 341696, $ecec],
                ['Facility rent', '12,000 sf × $13.58/sf per year. Utilities and property expenses excluded. Reference listing, not a chosen site.', 162960, $hwy],
                ['Electricity', '12,000 sf × 14.4 kWh/sf × $0.1381/kWh', 23864, $rate],
                ['Insurance package', 'General liability, business owner’s policy, workers’ compensation, professional liability, directors and officers, and abuse and molestation', 8696, $ins],
                ['Georgia annual registration', 'Nonprofit annual registration fee', 30, $reg],
            ],
        ],
        'phase' => [
            'title' => 'Phase 1 concept, 4,750 sf',
            'note' => 'A smaller first step in the same lease-and-renovate draft. Startup plus the first year of operating is $857,555.',
            'startup_total' => 290026,
            'ops_total' => 567529,
            'combined' => 857555,
            'equipment' => 40478,
            'startup' => [
                ['Renovation hard cost', '4,750 sf × $40/sf, low end of the cited office light-refresh range', 190000, $reno],
                ['Architectural and engineering drawings', '4,750 sf × $4/sf', 19000, $reno],
                ['Permits and impact fees', 'Low end of the cited $2,000–$15,000 range', 2000, $reno],
                ['Signage', 'Low end of the cited $10,000–$75,000 range', 10000, $reno],
                ['Soft-cost and contingency buffer', '10% of hard cost', 19000, $reno],
                ['Lease security deposit', 'Assumption: two months of rent at the cited rate', 8803, $north],
                ['Equipment and furniture', 'Sum of the phase 1 itemized list', 40478, '/support/#equipment'],
                ['Georgia nonprofit incorporation', '$100 SOS fee + $5 online + $40 legal notice', 145, $sos],
                ['IRS Form 1023 user fee', 'Full Form 1023', 600, $irs],
            ],
            'operating' => [
                ['Staff wages', '8 roles, 7.0 FTE, BLS OEWS May 2025 mean wages', 347590, $bls],
                ['Benefits and payroll taxes', 'Same 42.9% of wages method as the full plan', 148967, $ecec],
                ['Facility rent', '4,750 sf × $11.12/sf per year, NNN. Taxes and CAM were not quoted. Reference listing, not a chosen site.', 52800, $north],
                ['Electricity', '4,750 sf × 14.4 kWh/sf × $0.1381/kWh', 9446, $rate],
                ['Insurance package', 'Same coverage list as the full plan', 8696, $ins],
                ['Georgia annual registration', 'Nonprofit annual registration fee', 30, $reg],
            ],
        ],
    ];
}

function azalea_tier_sources(): array
{
    return [
        'seed' => 'https://www.homedepot.com/p/reviews/Lifetime-6-ft-White-Plastic-Fold-In-Half-Table-Light-Commercial-280857/324972590/5',
        'maker' => 'https://www.bestbuy.com/product/bambu-lab-p1s-combo-3d-printer-black/CZTZV9Z2C4/sku/6609661',
        'studio' => 'https://www.brandsmartusa.com/products/electronics-computers-tablets-and-home-office-computers-24-imac-desktop-computer-with-m4-chip-blue-late-2024',
        'kiln' => 'https://www.clay-king.com/product/skutt-km-818-ceramic-kiln/',
        'mentor' => 'https://www.bls.gov/oes/current/oes_46660.htm',
        'founding' => '',
    ];
}

function azalea_unpriced(): array
{
    return [
        'Program consumables (clay, glaze, filament, lumber, welding wire and gas)',
        'Software licenses, internet, and phone',
        'Water, sewer, and gas',
        'NNN taxes and common-area maintenance',
        '240-volt power and ventilation for the kiln and for welding, unless they are scoped inside the renovation contingency',
        'A van or other transportation',
        'Member meals or stipends',
        'An annual audit, marketing, and background checks',
    ];
}

function azalea_funding_mix(): array
{
    return [
        ['Foundation and government grants', '50%'],
        ['Individual donors', '20%'],
        ['Corporate sponsors', '15%'],
        ['Earned revenue', '10%'],
        ['In-kind support', '5%'],
    ];
}
