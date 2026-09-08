<?php

namespace App\Models;

use App\Observers\FeedbackObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['observation_id', 'teacher_id', 'message'])]
#[ObservedBy(FeedbackObserver::class)]
class Feedback extends Model
{
    use SoftDeletes;

    protected $table = 'feedback';

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
