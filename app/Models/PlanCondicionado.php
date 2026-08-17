<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PlanCondicionado extends Model
{
    protected $connection = 'mysql_vivepluss';

    protected $table = 'plan_condicionados';

    protected $fillable = [
        'plan_id',
        'disk',
        'disk_path',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk($this->disk)->exists($this->disk_path);
    }

    public function absolutePath(): string
    {
        return Storage::disk($this->disk)->path($this->disk_path);
    }
}
