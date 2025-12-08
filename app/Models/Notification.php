<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'related_model',
        'related_id',
        'read',
        'read_at',
    ];

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    // Mark as read
    public function markAsRead()
    {
        if (!$this->read) {
            $this->update([
                'read' => true,
                'read_at' => now(),
            ]);
        }
    }

    // Mark as unread
    public function markAsUnread()
    {
        $this->update([
            'read' => false,
            'read_at' => null,
        ]);
    }

    // Get notification icon based on type
    public function getIcon()
    {
        return match($this->type) {
            'comment' => 'fa-comment',
            'approval' => 'fa-check-circle',
            'rejection' => 'fa-times-circle',
            'project_update' => 'fa-sync-alt',
            'new_quotation' => 'fa-file-alt',
            'status_change' => 'fa-exchange-alt',
            default => 'fa-bell',
        };
    }

    // Get notification color based on type
    public function getColor()
    {
        return match($this->type) {
            'comment' => 'info',
            'approval' => 'success',
            'rejection' => 'danger',
            'project_update' => 'warning',
            'new_quotation' => 'primary',
            'status_change' => 'secondary',
            default => 'light',
        };
    }
}
