<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','can:activity.view']);
    }

    public function index(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $log      = trim($request->get('log', ''));
        $dateFrom = $request->get('from');
        $dateTo   = $request->get('to');

        $rows = Activity::with(['causer','subject'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('description', 'like', "%{$q}%")
                      ->orWhere('event', 'like', "%{$q}%")
                      ->orWhere('log_name', 'like', "%{$q}%")
                      ->orWhere('properties->attributes', 'like', "%{$q}%")
                      ->orWhere('properties->old', 'like', "%{$q}%");
                });
            })
            ->when($log !== '', fn($qq) => $qq->where('log_name', $log))
            ->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($qq) => $qq->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $logNames = Activity::select('log_name')->distinct()->pluck('log_name');

        return view('activity.index', compact('rows','q','log','logNames','dateFrom','dateTo'));
    }
}
