<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualQuoteObservation extends Model
{
    protected $fillable = [
        'individual_quote_id',
        'description',
        'created_by',
    ];

    public function individualQuote(): BelongsTo
    {
        return $this->belongsTo(IndividualQuote::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
