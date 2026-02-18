<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskOffer extends Model
{
    use HasFactory;

    protected $appends = ['task_detail'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function getTaskDetailAttribute()
    {
        return $this->task()->first();
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function rating()
    {
        return $this->hasOne(Rating::class, 'task_offer_id');
    }

    public function freelancerRatings()
    {
        return $this->hasMany(Rating::class, 'task_freelancer_id', 'freelancer_id');
    }

    public function ratings() // ratings received by this freelancer (for historical + average)
    {
        return $this->hasMany(Rating::class, 'task_freelancer_id', 'freelancer_id');
    }


    protected static function booted()
    {
        // Include Freelancer
        static::addGlobalScope('withFreelancer', function ($query) {
            $query->with(['freelancer:id,name,email,profile_picture']);
        });

        // Include the specific offer rating + review
        static::addGlobalScope('withOfferRating', function ($query) {
            $query->with(['rating' => function($q) {
                $q->select(
                    'id',
                    'task_offer_id',
                    'rating_value',
                    'review'
                );
            }]);
        });

        static::addGlobalScope('withOfferRatingUser', function ($query) {
            $query->with([
                'rating.createdBy:id,name,profile_picture'
            ]);
        });

        // Average rating of freelancer
        static::addGlobalScope('withAvgFreelancerRating', function ($query) {
            $query->withAvg('ratings as freelancer_avg_rating', 'rating_value');
        });

        // Total reviews for the freelancer
        static::addGlobalScope('withFreelancerReviewCount', function ($query) {
            $query->withCount('ratings as freelancer_review_count');
        });
    }

    public function getBgColor($status) {

        $allStatus = [
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'accepted', 'bgColor'=>'success'],
            ['name'=>'cancelled', 'bgColor'=>'dark'],
            ['name'=>'declined', 'bgColor'=>'danger'],
        ];

        foreach ($allStatus as $statusItem) {
            if ($statusItem['name'] === $status) {
                return $statusItem['bgColor'];
            }
        }
        return null; // Return null or a default value if the status is not found
    }
}
