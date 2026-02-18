<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectedAccount extends Model
{
    use HasFactory;
    protected $table = 'connected_accounts';
    protected $guarded = [];
    // protected $fillable = [
    //     'user_id',
    //     'stripe_account_id',
    //     'details_submitted', //boolean
    //     'charges_enabled', //boolean
    //     'payouts_enabled', //boolean
    //     'requirements_currently_due', //json
    //     'requirements_eventually_due', //json
    // ];

}
