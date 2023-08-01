<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\RepayStatus;
use Illuminate\Database\Eloquent\Model;

class Repay extends Model
{
    /**
     * @var mixed
     */
    protected $fillable = [
        'amount', 'payDate',
    ];

    protected $casts = [
        'status' => RepayStatus::class,
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'id');
    }
}
