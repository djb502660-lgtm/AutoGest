<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(
        string $module,
        string $action,
        string $description,
        ?int $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
    }

    public function logUserAction(
        string $action,
        string $description,
        ?int $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return $this->log('users', $action, $description, $userId, $oldValues, $newValues);
    }

    public function logOrderAction(
        string $action,
        string $description,
        ?int $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return $this->log('orders', $action, $description, $userId, $oldValues, $newValues);
    }

    public function logInventoryAction(
        string $action,
        string $description,
        ?int $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return $this->log('inventory', $action, $description, $userId, $oldValues, $newValues);
    }

    public function logReportAction(
        string $action,
        string $description,
        ?int $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return $this->log('reports', $action, $description, $userId, $oldValues, $newValues);
    }

    public function logVehicleAction(
        string $action,
        string $description,
        ?int $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return $this->log('vehicles', $action, $description, $userId, $oldValues, $newValues);
    }

    public function getAuditLogsByModule(string $module, int $limit = 50)
    {
        return AuditLog::forModule($module)
            ->with('user')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getAuditLogsByUser(int $userId, int $limit = 50)
    {
        return AuditLog::forUser($userId)
            ->with('user')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getRecentAuditLogs(int $days = 30, int $limit = 100)
    {
        return AuditLog::recent($days)
            ->with('user')
            ->latest()
            ->take($limit)
            ->get();
    }
}
