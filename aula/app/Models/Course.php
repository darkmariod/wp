<?php

namespace App\Models;

use App\Models\Concerns\GeneratesUniqueSlug;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'teacher_id', 'is_active'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use GeneratesUniqueSlug, HasFactory;

    protected $table = 'courses';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    protected function slugFallback(): string
    {
        return 'curso';
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class, 'course_id')->orderBy('sort_order')->orderBy('id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments', 'course_id', 'user_id')
            ->withPivot('enrolled_at')
            ->withTimestamps();
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(LibraryResource::class, 'course_resource', 'course_id', 'resource_id')
            ->withPivot(['module_id', 'sort_order'])
            ->withTimestamps();
    }
}
