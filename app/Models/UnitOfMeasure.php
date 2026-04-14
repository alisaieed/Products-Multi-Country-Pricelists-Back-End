<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;

class UnitOfMeasure extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'units_of_measures';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'abbreviation',
        'name',
        'dimension_type',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'name'      => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Tenant relationship.
     * Each unit of measure belongs to one tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to filter active units of measure.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by dimension type.
     */
    public function scopeOfDimension($query, string $dimensionType)
    {
        return $query->where('dimension_type', $dimensionType);
    }
}
