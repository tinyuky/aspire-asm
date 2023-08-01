<?php

namespace Tests\Unit\Customers;

use App\Enums\RepayStatus;
use App\Http\Controllers\Customers\LoanController;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Repay;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\TestCase;

class LoanControllerTest extends TestCase
{
    use WithoutMiddleware;

    public function testCreate()
    {
        // Mock the request data
        $requestData = [
            'title' => 'Test Loan',
            'amount' => 10000,
            'term' => 3,
            'startDate' => Carbon::now()->addDay()->toDateString(),
        ];

        Validator::shouldReceive('make')->once()->with($requestData, [
            'title' => 'required|string',
            'amount' => 'required|integer',
            'term' => 'required|integer',
            'startDate' => 'required|date|after:today',
        ])->andReturn(app('validator'));
        app('validator')->shouldReceive('fails')->once()->andReturn(false);

        $customer = factory(Customer::class)->create();
        $this->actingAs($customer, 'api-customer');

        Loan::shouldReceive('query->where->paginate->perPage')->once()->andReturn(10);
        Loan::shouldReceive('query->where->orderBy->first')->once()->andReturn(null);

        $response = (new LoanController())->create(new Request($requestData));

        $response->assertStatus(201)
            ->assertJsonStructure(['title', 'amount', 'term', 'created_by', 'startDate']);

        $loan = Loan::query()->where('title', 'Test Loan')->first();
        $this->assertNotNull($loan);

        $repayments = Repay::query()->where('loan_id', $loan->id)->get();
        $this->assertCount(3, $repayments);
        $this->assertEquals(3333.34, $repayments[0]->amount);
        $this->assertEquals(3333.33, $repayments[1]->amount);
        $this->assertEquals(3333.33, $repayments[2]->amount);
    }

    public function testShow()
    {
        $loan = factory(Loan::class)->create();
        $repayment = factory(Repay::class)->create(['loan_id' => $loan->id]);

        Loan::shouldReceive('find')->once()->with($loan->id)->andReturn($loan);
        Repay::shouldReceive('query->where->where->orderBy->with->first')->once()->with($loan->id, RepayStatus::PENDING)->andReturn($repayment);

        $response = (new \App\Http\Controllers\Admins\LoanController())->show($loan->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'totalAmount',
                'startDate',
                'payDate',
                'amount',
            ]);
    }

    public function testPay()
    {
        $requestData = [
            'amount' => 3333.34,
        ];

        Validator::shouldReceive('make')->once()->with($requestData, [
            'amount' => 'integer|min:1',
        ])->andReturn(app('validator'));
        app('validator')->shouldReceive('fails')->once()->andReturn(false);

        $loan = factory(Loan::class)->create();
        $repayment = factory(Repay::class)->create([
            'loan_id' => $loan->id,
            'amount' => 3333.34,
            'status' => RepayStatus::PENDING,
        ]);

        Loan::shouldReceive('find')->once()->with($loan->id)->andReturn($loan);
        Repay::shouldReceive('query->where->where->orderBy->with->first')->once()->with($loan->id, RepayStatus::PENDING)->andReturn($repayment);
        Repay::shouldReceive('query->where->where->count')->once()->with($loan->id, RepayStatus::PENDING)->andReturn(1);

        $response = (new LoanController())->pay(new Request($requestData), $loan->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'title', 'amount', 'term', 'created_by', 'startDate', 'status']);
    }

    public function testIndex()
    {
        $requestData = [
            'page_size' => 5,
        ];

        Validator::shouldReceive('make')->once()->with($requestData, [
            'page_size' => 'integer|min:1',
        ])->andReturn(app('validator'));
        app('validator')->shouldReceive('fails')->once()->andReturn(false);

        $customer = factory(Customer::class)->create();
        $this->actingAs($customer, 'api-customer');

        $loans = factory(Loan::class, 5)->create();
        Loan::shouldReceive('query->where->orderBy->paginate')->once()->with(5)->andReturn($loans);

        $response = (new \App\Http\Controllers\Admins\LoanController())->index(new Request($requestData));

        $response->assertStatus(200)
            ->assertJsonStructure(['loans', 'pagination']);
    }
}
