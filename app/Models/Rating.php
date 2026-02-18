<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    public function freelancer() {
        return $this->belongsTo(User::class, 'task_freelancer_id');
    }

    public function offer() {
        return $this->belongsTo(TaskOffer::class, 'task_offer_id');
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
