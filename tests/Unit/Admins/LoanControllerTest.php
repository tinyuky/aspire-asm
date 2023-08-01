<?php

namespace Tests\Unit\Admins;

use App\Enums\LoanStatus;
use App\Enums\RepayStatus;
use App\Http\Controllers\Admins\LoanController;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Repay;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\TestCase;

class LoanControllerTest extends TestCase
{
    use WithoutMiddleware;

    public function testIndex()
    {
        // Mock the request data
        $requestData = ['page_size' => 5];

        // Mock the Validator facade to return no errors
        Validator::shouldReceive('make')->once()->with($requestData, [
            'page_size' => 'integer|min:1',
        ])->andReturn(app('validator'));
        app('validator')->shouldReceive('fails')->once()->andReturn(false);

        // Mock the Loan model and the paginate method
        $loans = factory(Loan::class, 5)->create();
        Loan::shouldReceive('query->orderBy->paginate')->once()->with(5)->andReturn($loans);

        // Perform the index request
        $response = (new LoanController())->index(new Request($requestData));

        // Assertions
        $response->assertStatus(200)
            ->assertJsonStructure(['loans', 'pagination']);
    }

    public function testShow()
    {
        // Mock the Loan and Repay models
        $loan = factory(Loan::class)->create();
        $repayment = factory(Repay::class)->create(['loan_id' => $loan->id]);

        Loan::shouldReceive('find')->once()->with($loan->id)->andReturn($loan);
        Repay::shouldReceive('query->where->where->orderBy->with->first')->once()->with($loan->id, RepayStatus::PENDING)->andReturn($repayment);

        // Perform the show request
        $response = (new LoanController())->show($loan->id);

        // Assertions
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

    public function testUpdate()
    {
        // Mock the request data
        $requestData = ['status' => LoanStatus::APPROVED];

        // Mock the Validator facade to return no errors
        Validator::shouldReceive('make')->once()->with($requestData, [
            'status' => 'required|enum_value:' . LoanStatus::class,
        ])->andReturn(app('validator'));
        app('validator')->shouldReceive('fails')->once()->andReturn(false);

        // Mock the Loan model
        $loan = factory(Loan::class)->create();
        Loan::shouldReceive('find')->once()->with($loan->id)->andReturn($loan);

        // Perform the update request
        $response = (new LoanController())->update(new Request($requestData), $loan->id);

        // Assertions
        $response->assertStatus(200)
            ->assertJson(['status' => LoanStatus::APPROVED]);

        $this->assertEquals(LoanStatus::APPROVED, $loan->status);
    }
}
