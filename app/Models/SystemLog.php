<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'model',
        'model_id',
        'ip_address',
        'user_agent',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Logs belong to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter logs by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter logs by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter logs by model
     */
    public function scopeByModel($query, $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Scope: Filter logs by model ID
     */
    public function scopeByModelId($query, $modelId)
    {
        return $query->where('model_id', $modelId);
    }

    /**
     * Scope: Get recent logs
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Filter logs from today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Filter logs from this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereDate('created_at', '>=', now()->subWeek());
    }

    /**
     * Scope: Filter logs from this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereDate('created_at', '>=', now()->subMonth());
    }

    /**
     * Get action badge color
     */
    public function getActionBadgeColor()
    {
        return match($this->action) {
            'created' => '#198754',
            'updated' => '#0dcaf0',
            'deleted' => '#dc3545',
            'approved' => '#20c997',
            'rejected' => '#fd7e14',
            'commented' => '#0d6efd',
            'viewed' => '#6c757d',
            'downloaded' => '#6f42c1',
            default => '#adb5bd',
        };
    }

    /**
     * Get action icon
     */
    public function getActionIcon()
    {
        return match($this->action) {
            'created' => 'fa-plus-circle',
            'updated' => 'fa-edit',
            'deleted' => 'fa-trash',
            'approved' => 'fa-check-circle',
            'rejected' => 'fa-times-circle',
            'commented' => 'fa-comment',
            'viewed' => 'fa-eye',
            'downloaded' => 'fa-download',
            default => 'fa-circle',
        };
    }
}
