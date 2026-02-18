<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function clientUser()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function freelancerUser()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function offer()
    {
        return $this->belongsTo(TaskOffer::class, 'task_offer_id');
    }
}
