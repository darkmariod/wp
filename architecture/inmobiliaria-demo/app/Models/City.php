<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class City extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::creating(fn (City $city) => $city->slug ??= Str::slug($city->name));
    }

    public function sectors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sector::class);
    }
}
