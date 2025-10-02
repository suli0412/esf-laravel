<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class LogController extends Controller
{
    /**
     * Zeigt activity-Logs (daily) mit Filtern: datum, level, q
     */
    public function index(Request $request)
    {
        // Filter lesen
        $date  = $request->query('date', now()->format('Y-m-d')); // YYYY-MM-DD
        $level = strtolower(trim((string)$request->query('level', ''))); // info|warning|error|...
        $q     = trim((string)$request->query('q', ''));

        // unterstützte Level
        $levels = ['debug','info','notice','warning','error','critical','alert','emergency'];

        // Log-Dateiname (daily channel)
        $filename = "activity-{$date}.log";
        $path = storage_path('logs/'.$filename);

        $entries = collect();

        if (is_file($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

            foreach ($lines as $line) {
                // Beispiel Monolog-Laravel-Zeile:
                // [2025-10-02 13:05:33] local.INFO: Gruppe angelegt {"gruppe_id":1,"name":"X","by_user":5}
                // Regex: [timestamp] env.LEVEL: message {json}
                if (preg_match('/^\[(.*?)\]\s+([a-zA-Z0-9_-]+)\.([A-Z]+):\s(.*)$/', $line, $m)) {
                    $timestamp = $m[1] ?? null;
                    $env       = $m[2] ?? null;
                    $lvl       = strtolower($m[3] ?? '');
                    $rest      = $m[4] ?? '';

                    $message = $rest;
                    $context = [];

                    // Kontext JSON am Ende erkennen (geschweifte Klammer)
                    // Wir suchen das letzte " {" – damit auch Doppelpunkte in Message okay sind
                    $pos = strrpos($rest, '{');
                    if ($pos !== false) {
                        $maybeJson = substr($rest, $pos);
                        $maybeMsg  = trim(substr($rest, 0, $pos));
                        // Versuchen zu dekodieren
                        $decoded = json_decode($maybeJson, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $message = $maybeMsg;
                            $context = $decoded;
                        }
                    }

                    $entries->push([
                        'timestamp' => $timestamp,
                        'env'       => $env,
                        'level'     => $lvl,
                        'message'   => $message,
                        'context'   => $context,
                        'raw'       => $line,
                    ]);
                } else {
                    // Fallback: ungeparste Zeile
                    $entries->push([
                        'timestamp' => null,
                        'env'       => null,
                        'level'     => null,
                        'message'   => $line,
                        'context'   => [],
                        'raw'       => $line,
                    ]);
                }
            }
        }

        // Filtern nach Level
        if ($level && in_array($level, $levels, true)) {
            $entries = $entries->filter(fn ($e) => ($e['level'] ?? '') === $level);
        }

        // Volltextsuche in message + context(JSON as string)
        if ($q !== '') {
            $qLower = mb_strtolower($q);
            $entries = $entries->filter(function ($e) use ($qLower) {
                $msg = mb_strtolower((string)($e['message'] ?? ''));
                $ctx = mb_strtolower(json_encode($e['context'] ?? [], JSON_UNESCAPED_UNICODE));
                return str_contains($msg, $qLower) || str_contains($ctx, $qLower);
            });
        }

        // Neueste zuerst
        $entries = $entries->values()->reverse();

        // Pagination (in-memory)
        $page     = (int) max(1, $request->query('page', 1));
        $perPage  = (int) max(10, min(100, $request->query('per_page', 25)));
        $total    = $entries->count();
        $slice    = $entries->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.logs.index', [
            'entries' => $paginator,
            'date'    => $date,
            'level'   => $level,
            'q'       => $q,
            'levels'  => $levels,
            'fileExists' => is_file($path),
            'filename'   => $filename,
        ]);
    }
}
