<?php

namespace BDPay\LaravelBDPay\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BDPayTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'type',
        'status',
        'amount',
        'currency',
        'customer_name',
        'customer_email',
        'customer_phone',
        'recipient_name',
        'recipient_account',
        'bank_code',
        'description',
        'request_data',
        'response_data',
        'payment_method',
        'va_number',
        'payment_link',
        'expired_at',
        'paid_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Scope for payment transactions.
     */
    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    /**
     * Scope for disbursement transactions.
     */
    public function scopeDisbursements($query)
    {
        return $query->where('type', 'disbursement');
    }

    /**
     * Scope for pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for successful transactions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if transaction is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if transaction is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Check if transaction is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            'expired' => 'gray',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }
}
