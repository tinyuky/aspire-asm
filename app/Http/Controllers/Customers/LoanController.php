<?php

namespace App\Http\Controllers\Customers;

use App\Enums\LoanStatus;
use App\Enums\RepayStatus;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Repay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth.customer');
    }

    public function create(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|string',
            'amount' => 'required|integer',
            'term' => 'required|integer',
            'startDate' => 'required|date|after:today',
        ]);

        if($validator->fails()){
            return response()->json(["error" => $validator->errors()], 400);
        }

        $customer = auth('api-customer')->user();

        $term = $request->input('term');
        $title = $request->input('title');
        $amount = $request->input('amount');
        $startDate = Carbon::parse($request->input('startDate'));

        $loan = new Loan();
        $loan->title = $title;
        $loan->amount = $amount;
        $loan->term = $term;
        $loan->created_by = $customer['id'];
        $loan->startDate = $startDate;
        $loan->save();

        $installmentAmount = $amount / $term;
        for ($i = 0; $i < $term; $i++) {
            $repaymentDate = $startDate->addWeek();
            $repaymentAmount = $i === $term - 1 ? $amount - ($installmentAmount * ($term - 1)) : $installmentAmount;

            $repay = new Repay;
            $repay->amount = $repaymentAmount;
            $repay->payDate = $repaymentDate;
            $repay->loan_id = $loan->id;
            $repay->save();
        }

        return response()->json($loan, 201);
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

        $customer = auth('api-customer')->user();
        $loans = Loan::query()->where('created_by', $customer->id)->orderBy('created_at', 'desc')->paginate($pageSize);

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

    public function pay(Request $request, $id){
        $loan = Loan::find($id);

        if (!$loan) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid page_size parameter'], 400);
        }

        $repayment = Repay::query()->where('loan_id', $id)->where('status', RepayStatus::PENDING)->orderBy('id')->with('loan')->first();

        if ($repayment and $request->input('amount') < $repayment->amount) {
            return response()->json(['message' => 'Invalid amount'], 404);
        }

        if ($repayment) {
            $repayment->status = RepayStatus::PAID;
            $repayment->save();

            $remainingRepaymentCount = Repay::query()->where('loan_id', $id)->where('status', RepayStatus::PENDING)->count();
            if ($remainingRepaymentCount == 0){
                $loan->status = LoanStatus::PAID;
                $loan->save();
            }
        }

        return response()->json($loan);
    }
}
