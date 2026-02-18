<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreelancerSubscriptionPlan extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'user_id',
    //     'plan_name',
    //     'amount',
    //     'currency',
    //     'start_date',
    //     'end_date',
    //     'payment_intent',
    //     'status',
    // ];
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        return now()->greaterThan($this->end_date);
    }

    /**
     * Static method to expire all outdated subscriptions.
     */
    public static function expireOldPlans(): int
    {
        return static::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'expired']);
    }
}
