<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:audit.view']);
    }

    public function index()
    {
        $rows = Activity::with('causer') // User, der die Aktion ausgelöst hat
            ->latest()
            ->paginate(50);

        return view('admin.logs.index', compact('rows'));
    }
}
