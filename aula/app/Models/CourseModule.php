<?php

namespace App\Models;

use Database\Factories\CourseModuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Módulo (unidad) de un curso. La tabla es `modules`; el nombre de clase
 * lleva el prefijo Course para no confundirlo con otros "módulos".
 */
#[Fillable(['course_id', 'name', 'description', 'sort_order'])]
class CourseModule extends Model
{
    /** @use HasFactory<CourseModuleFactory> */
    use HasFactory;

    protected $table = 'modules';

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Recursos que el curso colocó dentro de este módulo (pivote course_resource).
     */
    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(LibraryResource::class, 'course_resource', 'module_id', 'resource_id')
            ->withPivot(['course_id', 'sort_order'])
            ->withTimestamps();
    }
}
