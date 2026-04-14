<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;


class Batch extends Model
{
    protected $table = 'batches';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'batch_number',
        'production_date',
        'expiry_date',
    ];

    protected $casts = [
        'production_date' => 'date',
        'expiry_date'     => 'date',
    ];

    /**
     * Tenant relationship.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Product relationship.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope: filter batches expiring soon.
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<=', now()->addDays($days));
    }
}
