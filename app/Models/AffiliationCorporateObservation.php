<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliationCorporateObservation extends Model
{
    protected $fillable = [
        'affiliation_corporate_id',
        'description',
        'created_by',
    ];

    public function affiliationCorporate(): BelongsTo
    {
        return $this->belongsTo(AffiliationCorporate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
