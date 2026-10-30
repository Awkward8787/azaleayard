<?php
declare(strict_types=1);

/**
 * Tax-language gate. Wording that mentions a deduction, 501(c)(3) status,
 * or a fiscal sponsor is returned only when the matching name and EIN
 * are both set. Otherwise the site stays on the formation sentence.
 */
function azalea_tax_mode(?array $cfg = null): string
{
    $cfg = $cfg ?? azalea_config();
    $status = (string) ($cfg['tax_status'] ?? 'none');
    $legal = trim((string) ($cfg['org_legal_name'] ?? ''));
    $ein = trim((string) ($cfg['org_ein'] ?? ''));
    $sponsor = trim((string) ($cfg['sponsor_name'] ?? ''));
    $sponsorEin = trim((string) ($cfg['sponsor_ein'] ?? ''));

    if ($status === '501c3' && $legal !== '' && $ein !== '') {
        return '501c3';
    }
    if (in_array($status, ['fiscal_sponsor', 'fractured_atlas'], true) && $sponsor !== '' && $sponsorEin !== '') {
        return 'fiscal_sponsor';
    }
    return 'none';
}

function azalea_status_notice(?array $cfg = null): array
{
    $cfg = $cfg ?? azalea_config();
    $mode = azalea_tax_mode($cfg);
    if ($mode === '501c3') {
        return [
            'title' => $cfg['org_legal_name'] . ' is a 501(c)(3) organization.',
            'body' => 'Contributions may be tax-deductible to the extent the law allows. EIN ' . $cfg['org_ein'] . '.',
        ];
    }
    if ($mode === 'fiscal_sponsor') {
        return [
            'title' => 'Contributions are received by ' . $cfg['sponsor_name'] . '.',
            'body' => 'Azalea Yard is a project of that organization. EIN ' . $cfg['sponsor_ein'] . '. Deductibility follows the sponsor’s determination.',
        ];
    }
    return [
        'title' => 'Azalea Yard is in formation and is not yet accepting donations.',
        'body' => 'The Founding Donor list records interest only. This site does not take a payment.',
    ];
}
