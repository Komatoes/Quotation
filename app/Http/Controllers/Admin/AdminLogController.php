<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    /**
     * Display a listing of system logs with filtering and pagination
     */
    public function index(Request $request)
    {
        // Only require authentication
        if (!auth()->check()) {
            abort(401, 'Unauthorized');
        }

        // Build query
        $query = SystemLog::query();

        // Filter by action
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by model
        if ($request->filled('model')) {
            $query->byModel($request->model);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by search (searches in description)
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('action', 'like', '%' . $request->search . '%');
        }

        // Get available actions for filter dropdown
        $actions = SystemLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Get available models for filter dropdown
        $models = SystemLog::select('model')
            ->distinct()
            ->whereNotNull('model')
            ->orderBy('model')
            ->pluck('model');

        // Paginate results
        $logs = $query->recent()->paginate(25);

        return view('admin.logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'models' => $models,
            'filters' => $request->only(['action', 'user_id', 'model', 'start_date', 'end_date', 'search']),
        ]);
    }

    /**
     * Show details of a specific log
     */
    public function show(SystemLog $log)
    {
        if (!auth()->check()) {
            abort(401, 'Unauthorized');
        }

        return view('admin.logs.show', ['log' => $log]);
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        if (!auth()->check()) {
            abort(401, 'Unauthorized');
        }

        // Build query with same filters as index
        $query = SystemLog::query();

        if ($request->filled('action')) {
            $query->byAction($request->action);
        }
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->recent()->get();

        // Create CSV
        $csv = "ID,User,Action,Description,Model,Model ID,IP Address,Date Time\n";
        foreach ($logs as $log) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $log->id,
                $log->user?->name ?? 'System',
                $log->action,
                str_replace('"', '""', $log->description ?? ''),
                $log->model,
                $log->model_id,
                $log->ip_address,
                $log->created_at->format('Y-m-d H:i:s')
            );
        }

        return response()
            ->streamDownload(
                function () use ($csv) {
                    echo $csv;
                },
                'system-logs-' . date('Y-m-d-H-i-s') . '.csv'
            );
    }

    /**
     * Clear old logs (keep last 90 days)
     */
    public function clearOldLogs(Request $request)
    {
        if (!auth()->check()) {
            abort(401, 'Unauthorized');
        }

        $deletedCount = SystemLog::where('created_at', '<', now()->subDays(90))->delete();

        return back()->with('success', "Cleared {$deletedCount} old logs (older than 90 days)");
    }
}
