<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function clientTasks()
    {
        return $this->hasMany(Task::class, 'created_by')->withCount(['offers', 'messages']);
    }

    public function freelancerTasks()
    {
        return $this->hasMany(Task::class, 'freelancer_id');
    }

    public function freelancerTaskOffers()
    {
        return $this->hasMany(TaskOffer::class, 'freelancer_id');
    }

    public function clientTaskOffers()
    {
        return $this->hasMany(TaskOffer::class, 'client_id');
    }

    public function myCreatedRatings()
    {
        return $this->hasMany(Rating::class, 'created_by');
    }

    public function getBgColor($status) {

        $allStatus = [
            ['name'=>'approved', 'bgColor'=>'success'],
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'suspended', 'bgColor'=>'danger'],
        ];

        foreach ($allStatus as $statusItem) {
            if ($statusItem['name'] === $status) {
                return $statusItem['bgColor'];
            }
        }
        return null; // Return null or a default value if the status is not found
    }

    public function getProfilePictureAttribute($value)
    {
        $image = $value ? $value : null;

        if ($image) {
            return asset('/storage/users/' . $image);
        }
        return null;
    }

    // public function getPortfolioImagesAttribute($value)
    // {
    //     $images = $value ? json_decode($value, true) : null;

    //     if (is_array($images)) {
    //         return array_map(function ($image) {
    //             // return Storage::url('tasks/' . $image); // Return the full storage URL
    //             return asset('/storage/portfolios/' . $image);
    //         }, $images);
    //     }

    //     return [];
    // }

    public function getPortfolioImagesAttribute($value)
    {
        $images = $value ? json_decode($value, true) : null;

        if (is_array($images)) {
            return array_map(function ($image) {
                // Only prepend the URL if it's not already a full URL
                return str_starts_with($image, 'http') ? $image : asset('/storage/portfolios/' . $image);
            }, $images);
        }

        return [];
    }


}
