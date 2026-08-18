<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateQuoteRequestObservation extends Model
{
    protected $connection = 'mysql_vivepluss';

    protected $fillable = [
        'corporate_quote_request_id',
        'description',
        'created_by',
    ];

    public function corporateQuoteRequest(): BelongsTo
    {
        return $this->belongsTo(CorporateQuoteRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
