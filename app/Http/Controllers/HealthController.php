<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Throwable;

class HealthController extends Controller
{
    public function __invoke()
    {
        $started = microtime(true);
        $checks = ['database' => false, 'cache' => false];

        try {
            DB::select('select 1');
            $checks['database'] = true;
        } catch (Throwable) {
        }

        try {
            $key = 'gold3d:health:' . now()->format('YmdHi');
            Cache::put($key, true, 10);
            $checks['cache'] = Cache::get($key) === true;
        } catch (Throwable) {
        }

        $healthy = !in_array(false, $checks, true);

        return response()->json([
            'ok' => $healthy,
            'service' => 'gold3d',
            'version' => 'apex-1.0',
            'checks' => $checks,
            'latency_ms' => round((microtime(true) - $started) * 1000, 2),
            'time' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }
}
