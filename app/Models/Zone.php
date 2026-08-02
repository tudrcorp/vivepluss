<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $table = 'zones';

    protected $fillable = [
        'code',
        'zone',
        'status',
        'created_by',
        'position',
    ];

    public function downloadzones(): HasMany
    {
        return $this->hasMany(DownloadZone::class);
    }
}
