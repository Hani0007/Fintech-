<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'target_amount',
        'saved_amount',
        'deadline',
        'status',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'saved_amount' => 'decimal:2',
        'deadline' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRemainingAmountAttribute()
    {
        return $this->target_amount - $this->saved_amount;
    }

    public function getProgressPercentageAttribute()
    {
        return $this->target_amount > 0 ? ($this->saved_amount / $this->target_amount) * 100 : 0;
    }
}
