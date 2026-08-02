<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateQuoteObservation extends Model
{
    protected $fillable = [
        'corporate_quote_id',
        'description',
        'created_by',
    ];

    public function corporateQuote(): BelongsTo
    {
        return $this->belongsTo(CorporateQuote::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
