<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Notification extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'organization_id',
        'recipient_email',
        'recipient_phone',
        'subject',
        'message',
        'channel',
        'delivery_status',
        'sent_at',
        'evervault_metadata',
        'error_message',
        'retry_count',
        'next_retry_at'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'id' => 'string',
        'data' => 'array',
        'evervault_metadata' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'retry_count' => 'integer'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the notifiable entity that the notification belongs to.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the organization that the notification belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Determine if a notification has been read.
     */
    public function read(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     */
    public function unread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): void
    {
        if (!is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determine if the notification is pending delivery.
     */
    public function isPending(): bool
    {
        return $this->delivery_status === 'pending';
    }

    /**
     * Determine if the notification has been sent.
     */
    public function isSent(): bool
    {
        return $this->delivery_status === 'sent';
    }

    /**
     * Determine if the notification delivery failed.
     */
    public function isFailed(): bool
    {
        return $this->delivery_status === 'failed';
    }

    /**
     * Determine if the notification was delivered.
     */
    public function isDelivered(): bool
    {
        return $this->delivery_status === 'delivered';
    }

    /**
     * Mark the notification as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'delivery_status' => 'sent',
            'sent_at' => now()
        ]);
    }

    /**
     * Mark the notification as failed.
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'delivery_status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
            'next_retry_at' => $this->calculateNextRetry()
        ]);
    }

    /**
     * Mark the notification as delivered.
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'delivery_status' => 'delivered'
        ]);
    }

    /**
     * Calculate the next retry time.
     */
    protected function calculateNextRetry(): ?\Carbon\Carbon
    {
        if ($this->retry_count >= 3) {
            return null; // Stop retrying after 3 attempts
        }

        // Exponential backoff: 5 minutes, 15 minutes, 1 hour
        $delays = [5, 15, 60];
        $delay = $delays[$this->retry_count] ?? 60;

        return now()->addMinutes($delay);
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->delivery_status) {
            'pending' => 'En attente',
            'sent' => 'Envoyée',
            'failed' => 'Échouée',
            'delivered' => 'Livrée',
            default => ucfirst($this->delivery_status)
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->delivery_status) {
            'pending' => 'warning',
            'sent' => 'info',
            'failed' => 'danger',
            'delivered' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get the channel label for display.
     */
    public function getChannelLabelAttribute(): string
    {
        return match($this->channel) {
            'email' => 'Email',
            'sms' => 'SMS',
            'push' => 'Push',
            'database' => 'Base de données',
            default => ucfirst($this->channel)
        };
    }

    /**
     * Get the Evervault notification type.
     */
    public function getEvervaultTypeAttribute(): ?string
    {
        return $this->evervault_metadata['type'] ?? null;
    }

    /**
     * Determine if this is an Evervault notification.
     */
    public function isEvervaultNotification(): bool
    {
        return !is_null($this->evervault_metadata);
    }

    /**
     * Scope to get notifications by organization.
     */
    public function scopeByOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to get notifications by delivery status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('delivery_status', $status);
    }

    /**
     * Scope to get notifications by channel.
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to get pending notifications.
     */
    public function scopePending($query)
    {
        return $query->where('delivery_status', 'pending');
    }

    /**
     * Scope to get failed notifications that can be retried.
     */
    public function scopeRetryable($query)
    {
        return $query->where('delivery_status', 'failed')
                    ->where('retry_count', '<', 3)
                    ->where(function ($q) {
                        $q->whereNull('next_retry_at')
                          ->orWhere('next_retry_at', '<=', now());
                    });
    }

    /**
     * Scope to get Evervault notifications.
     */
    public function scopeEvervault($query)
    {
        return $query->whereNotNull('evervault_metadata');
    }

    /**
     * Scope to get notifications by Evervault type.
     */
    public function scopeByEvervaultType($query, string $type)
    {
        return $query->whereJsonContains('evervault_metadata->type', $type);
    }

    /**
     * Scope to get recent notifications.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get notifications with email recipients.
     */
    public function scopeWithEmail($query)
    {
        return $query->whereNotNull('recipient_email');
    }

    /**
     * Scope to get notifications by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}

