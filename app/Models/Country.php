<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $fillable = [
        'name',
        'code',
        'currency',
    ];

    // Categories related to this country (through products)
    public function categories()
    {
        return $this->hasManyThrough(Category::class, Product::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    // Many-to-many with Product through ProductPrice
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_prices')
                    ->withPivot('price')
                    ->withTimestamps();
    }
    /**
     * Example scope: find by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * Example scope: find by currency.
     */
    public function scopeByCurrency($query, string $currency)
    {
        return $query->where('currency', strtoupper($currency));
    }
}
