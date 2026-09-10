<?php

namespace App\Models;

use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use UsesDefaultConnection;

    protected $table = 'states';

    protected $fillable = [
        'country_id',
        'region_id',
        'definition',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }
}
