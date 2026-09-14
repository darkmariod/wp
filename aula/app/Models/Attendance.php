<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['child_id', 'date', 'status', 'recorded_by', 'notes'])]
class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    protected $table = 'attendances';

    public const STATUS_PRESENTE = 'presente';

    public const STATUS_ATRASO = 'atraso';

    public const STATUS_FALTA_JUSTIFICADA = 'falta_justificada';

    public const STATUS_FALTA_INJUSTIFICADA = 'falta_injustificada';

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
