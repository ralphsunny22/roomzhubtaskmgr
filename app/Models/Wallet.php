<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function taskOffer()
    {
        return $this->belongsTo(TaskOffer::class, 'task_offer_id');
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getBgColor($status) {

        $allStatus = [
            ['name'=>'paid', 'bgColor'=>'success'],
            ['name'=>'unpaid', 'bgColor'=>'danger'],
        ];

        foreach ($allStatus as $statusItem) {
            if ($statusItem['name'] === $status) {
                return $statusItem['bgColor'];
            }
        }
        return null; // Return null or a default value if the status is not found
    }

}
