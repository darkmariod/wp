<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Property extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'price', 'address',
        'bedrooms', 'bathrooms', 'area_m2', 'parking_spaces',
        'sector_id', 'property_type_id', 'operation_id',
        'status', 'is_featured', 'published_at',
        'code', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'area_m2'        => 'decimal:2',
            'bedrooms'       => 'integer',
            'bathrooms'      => 'integer',
            'parking_spaces' => 'integer',
            'is_featured'    => 'boolean',
            'published_at'   => 'datetime',
            'status'         => 'string',
            'latitude'       => 'decimal:7',
            'longitude'      => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Property $p) {
            $p->slug ??= Str::slug($p->title);
            if (!$p->code) {
                $last = static::max('id') ?? 0;
                $p->code = 'INM-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function sector(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function propertyType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function operation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }
}
