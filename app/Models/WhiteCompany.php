<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteCompany extends Model
{
    protected $table = 'white_companies';

    protected $fillable = [
        'name',
        'logo',
        'rif',
        'email',
        'phone',
        'address',
        'city_id',
        'state_id',
        'country_id',
        'created_by',
        'updated_by',
        'assigned_credit',
    ];

    protected $casts = [
        'assigned_credit' => 'decimal:2',
    ];
}
