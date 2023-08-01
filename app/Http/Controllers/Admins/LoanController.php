<?php

namespace App\Http\Controllers\Admins;

use App\Enums\LoanStatus;
use App\Enums\RepayStatus;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Repay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.customer');
    }

    public function index(Request $request)
    {
        $defaultPageSize = 10;

        $validator = Validator::make($request->all(), [
            'page_size' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid page_size parameter'], 400);
        }

        $pageSize = $request->get('page_size', $defaultPageSize);

        $loans = Loan::query()->orderBy('created_at', 'desc')->paginate($pageSize);

        $pagination = [
            'page_size' => $loans->perPage(),
            'total_pages' => $loans->lastPage(),
            'current_page' => $loans->currentPage(),
        ];

        $response = [
            'loans' => $loans->items(),
            'pagination' => $pagination,
        ];

        return response()->json($response);
    }


    public function show($id){
        $loan = Loan::find($id);

        if (!$loan) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        $repayment = Repay::query()->where('loan_id', $id)->where('status', RepayStatus::PENDING)->orderBy('id')->with('loan')->first();

        $rsp = [
            'id' => $repayment['loan']['id'],
            'title' => $repayment['loan']['title'],
            'totalAmount' => $repayment['loan']['amount'],
            'startDate' => $repayment['loan']['startDate'],
            'payDate' => $repayment->payDate,
            'amount' => $repayment->amount
        ];

        return response()->json($rsp);
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'status' => 'required|enum_value:' . LoanStatus::class,
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid status parameter'], 400);
        }


        $loan = Loan::find($id);

        if (!$loan) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        $loan->status = $request->get('status');
        $loan->save();

        return response()->json($loan);
    }
}
