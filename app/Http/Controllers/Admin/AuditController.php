<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    private AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $module = $request->string('module')->toString();
        $action = $request->string('action')->toString();
        $userId = $request->input('user_id');
        $days = $request->integer('days', 30);

        $query = AuditLog::with('user')
            ->when($module !== '', fn ($q) => $q->where('module', $module))
            ->when($action !== '', fn ($q) => $q->where('action', $action))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->recent($days)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $modules = AuditLog::distinct()->pluck('module')->filter()->sort();
        $actions = AuditLog::distinct()->pluck('action')->filter()->sort();
        $users = User::where('role', '!=', 'cliente')->orderBy('name')->get();

        return view('admin.audit.index', compact('query', 'module', 'action', 'userId', 'days', 'modules', 'actions', 'users'));
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('view', $auditLog);

        $auditLog->load('user');

        return view('admin.audit.show', compact('auditLog'));
    }
}
