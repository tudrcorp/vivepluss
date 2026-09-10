<?php

namespace App\Models;

use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use UsesDefaultConnection;

    protected $table = 'bitacoras';

    protected $fillable = [
        'individual_quote_id',
        'user_id',
        'accion',
        'detalle',
    ];

    public function individual_quote()
    {
        return $this->belongsTo(IndividualQuote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
