<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentNotification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'student_id',
        'title',
        'body',
        'type',
        'data',
        'status',
        'read_at'
    ];
    
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];
    
    /**
     * Get the student that owns the notification.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        if ($this->status !== 'read') {
            $this->status = 'read';
            $this->read_at = now();
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }
}
