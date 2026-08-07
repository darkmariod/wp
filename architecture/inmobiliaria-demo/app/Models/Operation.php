<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Operation extends Model
{
    protected $fillable = ['name', 'slug', 'visibility'];

    protected function casts(): array
    {
        return ['visibility' => 'string'];
    }

    protected static function booted(): void
    {
        static::creating(fn (Operation $op) => $op->slug ??= Str::slug($op->name));
    }

    public function properties(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Property::class);
    }
}
