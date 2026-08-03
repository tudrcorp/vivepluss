<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadZone extends Model
{
    protected $connection = 'mysql_vivepluss';

    protected $table = 'download_zones';

    protected $fillable = [
        'zone_id',
        'position',
        'document',
        'status',
        'image_icon',
        'description',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
