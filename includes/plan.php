<?php
declare(strict_types=1);

function azalea_plan(): array
{
    static $plan;
    if ($plan === null) {
        $path = AZALEA_PUBLIC . '/tour/data/rooms.json';
        $raw = file_get_contents($path);
        $decoded = json_decode($raw === false ? '' : $raw, true);
        if (!is_array($decoded)) {
            http_response_code(500);
            echo 'Plan data is missing.';
            exit;
        }
        $plan = $decoded;
    }
    return $plan;
}

function azalea_money(int|float $amount, int $decimals = 0): string
{
    return '$' . number_format((float) $amount, $decimals);
}

function azalea_tier_amount(array $tier): string
{
    return azalea_money((int) $tier['amount']) . (!empty($tier['plus']) ? '+' : '');
}

function azalea_tiers_by_id(): array
{
    $out = [];
    foreach (azalea_plan()['tiers'] as $tier) {
        $out[$tier['id']] = $tier;
    }
    return $out;
}

function azalea_interest_catalog(): array
{
    $plan = azalea_plan();
    $tiers = azalea_tiers_by_id();
    $ranges = [];
    foreach ($plan['tiers'] as $tier) {
        $ranges[] = [
            'value' => 'tier:' . $tier['id'],
            'label' => $tier['name'] . ' · about ' . azalea_tier_amount($tier) . ' · ' . $tier['funds'],
        ];
    }
    $slots = [];
    foreach ($plan['slots'] as $slot) {
        $tier = $tiers[$slot['tier']] ?? null;
        $amount = $tier ? azalea_tier_amount($tier) : '';
        $proposed = (($slot['status'] ?? '') === 'proposed') ? ' · proposed use, needs board approval' : '';
        $slots[] = [
            'value' => $slot['id'],
            'label' => $slot['label'] . ' · ' . ($tier['name'] ?? 'Naming') . ' ' . $amount . $proposed,
        ];
    }
    return [
        'Amount ranges' => $ranges,
        'Naming opportunities' => $slots,
    ];
}

function azalea_interest_labels(): array
{
    $labels = [];
    foreach (azalea_interest_catalog() as $options) {
        foreach ($options as $option) {
            $labels[$option['value']] = $option['label'];
        }
    }
    return $labels;
}
