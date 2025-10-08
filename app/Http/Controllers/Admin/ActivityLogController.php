<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:audit.view']);
    }

    public function index(Request $request)
    {
        // ---- Eingaben / Defaults ----
        $perPage = (int) ($request->integer('perPage') ?? 50);
        if ($perPage <= 0 || $perPage > 500) {
            $perPage = 50;
        }

        $date    = $request->input('date', now()->toDateString()); // YYYY-MM-DD
        $channel = $request->input('channel', 'db');               // 'db' | 'file'
        $q       = trim((string) $request->input('q', ''));

        $filename    = null;  // nur bei file-Channel belegt
        $filePreview = null;  // optionale Textvorschau der Datei
        $count       = 0;

        if ($channel === 'file') {
            // ---- Datei-Logs (optional) ----
            // Erwartetes Pattern: storage/logs/activity-YYYY-MM-DD.log
            $filename = storage_path('logs/activity-' . $date . '.log');

            if (is_file($filename) && is_readable($filename)) {
                // kleine Vorschau der letzten ~500 Zeilen (kein Muss für die View)
                $lines = @file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                $slice = array_slice($lines, -500);
                // einfache "Suche" im Preview
                if ($q !== '') {
                    $slice = array_values(array_filter($slice, fn ($line) => stripos($line, $q) !== false));
                }
                $filePreview = implode("\n", $slice);
                $count = count($lines);
            } else {
                $filePreview = null; // Datei fehlt; View zeigt „Datenbank“/Hinweis an
                $count = 0;
            }

            // Bei file-Channel liefern wir zur View eine leere Collection für items
            // (damit das Template einfach ist)
            $items = collect();
        } else {
            // ---- DB-Logs (Standard) ----
            $query = Activity::with('causer')->latest();

            if ($q !== '') {
                // einfache Suche über description + properties (JSON)
                $query->where(function ($qq) use ($q) {
                    $qq->where('description', 'like', "%{$q}%")
                       ->orWhere('event', 'like', "%{$q}%")
                       ->orWhere('log_name', 'like', "%{$q}%")
                       ->orWhere('properties', 'like', "%{$q}%");
                });
            }

            $items = $query->paginate($perPage)->appends($request->query());
            $count = Activity::count();
        }

        return view('admin.logs.index', [
            'items'       => $items,       // Paginator (db) oder leere Collection (file)
            'count'       => $count,       // Gesamtanzahl (db) oder Zeilen (file)
            'date'        => $date,
            'perPage'     => $perPage,
            'channel'     => $channel,     // 'db' oder 'file'
            'filename'    => $filename,    // Pfad bei file, sonst null
            'filePreview' => $filePreview, // optionaler Text-Auszug
            'q'           => $q,
        ]);
    }
}
