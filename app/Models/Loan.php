<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    /**
     * @var mixed
     */
    protected $fillable = [
        'title','amount', 'term',
    ];

    protected $casts = [
        'status' => LoanStatus::class,
    ];
}
