<?php

namespace App\Helpers;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SystemLogHelper
{
    /**
     * Log a system action
     *
     * @param string $action (created, updated, deleted, approved, rejected, etc.)
     * @param string $description
     * @param string|null $model Model class name
     * @param int|null $modelId Related model ID
     * @param array|null $changes Array of changes (before/after)
     */
    public static function log(
        string $action,
        string $description,
        ?string $model = null,
        ?int $modelId = null,
        ?array $changes = null
    ): SystemLog {
        return SystemLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'model' => $model,
            'model_id' => $modelId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'changes' => $changes,
        ]);
    }

    /**
     * Log a quotation action
     */
    public static function logQuotation(string $action, string $description, int $quotationId, ?array $changes = null): SystemLog
    {
        return self::log($action, $description, 'App\Models\Quotation', $quotationId, $changes);
    }

    /**
     * Log a project action
     */
    public static function logProject(string $action, string $description, int $projectId, ?array $changes = null): SystemLog
    {
        return self::log($action, $description, 'App\Models\Project', $projectId, $changes);
    }

    /**
     * Log a comment action
     */
    public static function logComment(string $action, string $description, int $commentId, ?array $changes = null): SystemLog
    {
        return self::log($action, $description, 'App\Models\Comment', $commentId, $changes);
    }

    /**
     * Log an approval action
     */
    public static function logApproval(string $quotationNumber, string $clientName, ?array $changes = null): SystemLog
    {
        return self::log(
            'approved',
            "Customer {$clientName} approved quotation: {$quotationNumber}",
            null,
            null,
            $changes
        );
    }

    /**
     * Log a rejection action
     */
    public static function logRejection(string $quotationNumber, string $clientName, ?string $reason = null, ?array $changes = null): SystemLog
    {
        $description = "Quotation {$quotationNumber} rejected";
        if ($clientName) {
            $description .= " by {$clientName}";
        }
        if ($reason) {
            $description .= ": {$reason}";
        }

        return self::log(
            'rejected',
            $description,
            null,
            null,
            $changes
        );
    }

    /**
     * Log a download action
     */
    public static function logDownload(string $type, string $name, ?int $id = null): SystemLog
    {
        return self::log(
            'downloaded',
            "Downloaded {$type}: {$name}",
            null,
            $id
        );
    }

    /**
     * Get logs for a specific quotation
     */
    public static function getQuotationLogs(int $quotationId)
    {
        return SystemLog::where('model', 'App\Models\Quotation')
            ->where('model_id', $quotationId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get logs for a specific user
     */
    public static function getUserLogs(int $userId, $limit = 10)
    {
        return SystemLog::where('user_id', $userId)
            ->recent()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent logs
     */
    public static function getRecentLogs($limit = 10)
    {
        return SystemLog::recent()
            ->limit($limit)
            ->get();
    }
}
