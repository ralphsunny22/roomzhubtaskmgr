<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    use HasFactory;

    protected $casts = [
        'task_images' => 'array', // cast to an array
    ];

    // Accessor to return the full path of each image in task_images
    public function getTaskImagesAttribute($value)
    {
        $images = $value ? json_decode($value, true) : null;

        if (is_array($images)) {
            return array_map(function ($image) {
                // return Storage::url('tasks/' . $image); // Return the full storage URL
                return asset('/storage/tasks/' . $image);
            }, $images);
        }

        return [];
    }

    //client, task owner
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    //user that did the job
    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }
}
