<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Carbon\Carbon;

class GoldMarketService
{
    public function dashboard(): array
    {
        $ttl = max(15, (int) config('goldmarket.refresh_seconds', 60));
        return Cache::remember('goldmarket:dashboard', now()->addSeconds($ttl), fn () => $this->buildDashboard());
    }

    private function buildDashboard(): array
    {
        $latest = DB::table('gold_rates')->latest('effective_at')->first();

        try {
            $live = $this->fetchLiveMarket();
            $this->persistSnapshot($live);
            $latest = DB::table('gold_rates')->latest('effective_at')->first();
        } catch (\Throwable $e) {
            Log::warning('Gold market provider unavailable', ['message' => $e->getMessage()]);
            $live = $this->fallbackMarket($latest);
        }

        $history = DB::table('gold_rate_snapshots')
            ->orderByDesc('captured_at')
            ->limit(48)
            ->get(['price_per_gram_toman', 'captured_at'])
            ->reverse()
            ->values()
            ->map(fn ($row) => [
                'price' => (int) $row->price_per_gram_toman,
                'time' => Carbon::parse($row->captured_at)->format('H:i'),
            ])->all();

        return [
            'provider' => config('goldmarket.provider'),
            'source' => 'TGJU',
            'live' => (bool) ($live['live'] ?? false),
            'gold18_toman' => (int) ($live['gold18_toman'] ?? $latest?->price_per_gram ?? config('goldmarket.fallback_18_toman')),
            'gold24_toman' => (int) ($live['gold24_toman'] ?? 0),
            'ounce_usd' => (float) ($live['ounce_usd'] ?? 0),
            'change_percent' => (float) ($live['change_percent'] ?? 0),
            'updated_at' => $live['updated_at'] ?? optional($latest?->effective_at)->toIso8601String(),
            'history' => !empty($live['history'] ?? []) ? $live['history'] : $history,
        ];
    }

    private function fetchLiveMarket(): array
    {
        // Fetch the three market indicators concurrently so a slow provider cannot
        // block the storefront for three sequential network timeouts.
        $headers = [
            'User-Agent' => 'Gold3D/3.0 (+local-storefront)',
            'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.7',
        ];

        $responses = Http::pool(function (Pool $pool) use ($headers) {
            return [
                'gold18' => $pool->as('gold18')->connectTimeout(2)->timeout(3)->withHeaders($headers)->get(config('goldmarket.gold18_url')),
                'gold24' => $pool->as('gold24')->connectTimeout(2)->timeout(3)->withHeaders($headers)->get(config('goldmarket.gold24_url')),
                'ounce' => $pool->as('ounce')->connectTimeout(2)->timeout(3)->withHeaders($headers)->get(config('goldmarket.ounce_url')),
            ];
        });

        $gold18 = $this->parseMetricResponse($responses['gold18'] ?? null, 10);
        $gold24 = $this->parseMetricResponse($responses['gold24'] ?? null, 10);
        $ounce = $this->parseMetricResponse($responses['ounce'] ?? null, 1);

        if ($gold18['value'] <= 0) {
            throw new \RuntimeException('طلای ۱۸ عیار از منبع بازار دریافت نشد.');
        }

        return [
            'live' => true,
            'gold18_toman' => (int) round($gold18['value']),
            'gold24_toman' => (int) round($gold24['value']),
            'ounce_usd' => (float) $ounce['value'],
            'change_percent' => (float) $gold18['change_percent'],
            'updated_at' => now()->toIso8601String(),
            'history' => $this->fetchHistory(config('goldmarket.gold18_url')),
        ];
    }

    /**
     * TGJU publishes these indicators in Rial. The storefront displays Toman.
     */
    private function fetchMetric(string $url, int $rialDivisor, int $timeoutSeconds = 3): array
    {
        $response = Http::connectTimeout(min(2, $timeoutSeconds))
            ->timeout($timeoutSeconds)
            ->retry(0)
            ->withHeaders([
                'User-Agent' => 'Gold3D/3.0 (+local-storefront)',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.7',
            ])
            ->get($url);

        return $this->parseMetricResponse($response, $rialDivisor);
    }

    private function parseMetricResponse(?Response $response, int $rialDivisor): array
    {
        if (!$response || !$response->successful()) {
            return ['value' => 0, 'change_percent' => 0];
        }

        $text = html_entity_decode(strip_tags($response->body()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        preg_match('/نرخ\s*فعلی\s*[:：\s]+([0-9۰-۹,\.]+)/u', $text, $priceMatch);
        $rawPrice = $priceMatch[1] ?? '';
        $value = $this->normalizeNumber($rawPrice);
        if ($rialDivisor === 10) {
            $value /= 10;
        }

        preg_match('/درصد\s*تغییر\s*نسبت\s*به\s*روز\s*گذشته\s*[:：\s]*([+-]?[0-9۰-۹]+(?:\.[0-9۰-۹]+)?)\s*%/u', $text, $changeMatch);
        $change = $this->normalizeNumber($changeMatch[1] ?? '0');

        return [
            'value' => $value,
            'change_percent' => $change,
        ];
    }


    private function fetchHistory(string $todayUrl): array
    {
        $historyUrl = preg_replace('/\/today\/?$/', '/history', $todayUrl) ?: $todayUrl;
        return Cache::remember('goldmarket:history', now()->addMinutes(5), function () use ($historyUrl) {
            try {
            $response = Http::connectTimeout(3)->timeout(3)->withHeaders([
                'User-Agent' => 'Gold3D/3.0 (+local-storefront)',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.7',
            ])->get($historyUrl);
            if (!$response->successful()) return [];

            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->body());
            $xpath = new \DOMXPath($dom);
            $rows = [];
            foreach ($xpath->query('//tr[td]') as $tr) {
                $cells = [];
                foreach ($xpath->query('./td', $tr) as $td) {
                    $cells[] = trim(preg_replace('/\s+/u', ' ', html_entity_decode($td->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                }
                if (count($cells) < 8) continue;
                if (!preg_match('/^(?:13|14|20)\d{2}\/\d{2}\/\d{2}$/', $cells[6])) continue;
                $close = $this->normalizeNumber($cells[3]);
                if ($close <= 0) continue;
                $rows[] = [
                    'price' => (int) round($close / 10),
                    'time' => $cells[6],
                ];
                if (count($rows) >= 14) break;
            }
            return array_reverse($rows);
        } catch (\Throwable $e) {
            return [];
            }
        });
    }

    private function normalizeNumber(string $value): float
    {
        $value = strtr($value, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٫'=>'.','٬'=>',',
        ]);
        $value = str_replace(',', '', trim($value));
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function persistSnapshot(array $live): void
    {
        $latest = DB::table('gold_rates')->latest('effective_at')->first();
        $lastSnapshot = DB::table('gold_rate_snapshots')->latest('captured_at')->first();
        $due = !$lastSnapshot || now()->diffInMinutes(Carbon::parse($lastSnapshot->captured_at)) >= config('goldmarket.snapshot_minutes');

        if (!$latest || abs((float) $latest->price_per_gram - $live['gold18_toman']) >= 1) {
            DB::table('gold_rates')->insert([
                'price_per_gram' => $live['gold18_toman'],
                'labor_percent' => $latest?->labor_percent ?? 12,
                'profit_percent' => $latest?->profit_percent ?? 8,
                'tax_percent' => $latest?->tax_percent ?? 10,
                'source' => 'tgju-live',
                'effective_at' => now(),
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($due) {
            DB::table('gold_rate_snapshots')->insert([
                'price_per_gram_toman' => $live['gold18_toman'],
                'captured_at' => now(),
                'source' => 'tgju-live',
            ]);
        }
    }

    private function fallbackMarket(?object $latest): array
    {
        return [
            'live' => false,
            'gold18_toman' => (int) ($latest?->price_per_gram ?? config('goldmarket.fallback_18_toman')),
            'gold24_toman' => 0,
            'ounce_usd' => 0,
            'change_percent' => 0,
            'updated_at' => $latest?->effective_at,
        ];
    }
}
