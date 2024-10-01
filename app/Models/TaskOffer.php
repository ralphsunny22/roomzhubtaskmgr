<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskOffer extends Model
{
    use HasFactory;

    protected $appends = ['task_detail'];

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
}
