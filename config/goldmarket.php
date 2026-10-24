<?php
return [
    'provider' => env('GOLD_PRICE_PROVIDER', 'tgju'),
    'gold18_url' => env('GOLD_PRICE_18_URL', 'https://www.tgju.org/profile/geram18/today'),
    'gold24_url' => env('GOLD_PRICE_24_URL', 'https://www.tgju.org/profile/geram24/today'),
    'ounce_url' => env('GOLD_PRICE_OUNCE_URL', 'https://www.tgju.org/profile/ons/today'),
    'refresh_seconds' => (int) env('GOLD_PRICE_REFRESH_SECONDS', 60),
    'snapshot_minutes' => (int) env('GOLD_PRICE_SNAPSHOT_MINUTES', 5),
    'fallback_18_toman' => (int) env('GOLD_PRICE_FALLBACK_TOMAN', 8500000),
];
