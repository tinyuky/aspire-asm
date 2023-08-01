<?php

namespace Tests\Unit\Customers;

use App\Http\Controllers\Customers\AuthController;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Validator;
use Mockery;
use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    use WithoutMiddleware;

    public function testLogin()
    {
        $requestData = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        Validator::shouldReceive('make')->once()->with($requestData, [
            'email' => 'required|email',
            'password' => 'required',
        ])->andReturn(app('validator'));
        app('validator')->shouldReceive('fails')->once()->andReturn(false);

        $token = 'mocked-access-token';
        auth('api-customer')->shouldReceive('setTTL')->once()->andReturnSelf();
        auth('api-customer')->shouldReceive('claims')->once()->andReturnSelf();
        auth('api-customer')->shouldReceive('attempt')->once()->with($requestData)->andReturn($token);

        $response = (new AuthController())->login();

        $response->assertStatus(200)
            ->assertJson([
                'access_token' => $token,
                'token_type' => 'bearer',
            ]);
    }

    public function testLoginWithValidationErrors()
    {
        Validator::shouldReceive('make')->once()->andReturn(app('validator'));
        app('validator')->shouldReceive('fails')->once()->andReturn(true);
        app('validator')->shouldReceive('errors')->once()->andReturn(['email' => ['The email field is required.']]);

        $response = (new AuthController())->login();

        $response->assertStatus(400)
            ->assertJsonStructure(['error']);
    }

    public function testRegister()
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
        ];

        Validator::shouldReceive('make')->once()->andReturn(app('validator'));
        app('validator')->shouldReceive('fails')->once()->andReturn(false);

        $customerMock = Mockery::mock('alias:App\Models\Customer');
        $customerMock->shouldReceive('create')->once()->with($requestData)->andReturnSelf();

        $response = (new AuthController())->register();

        $response->assertStatus(201)
            ->assertJson($requestData);
    }

    public function testRegisterWithValidationErrors()
    {
        Validator::shouldReceive('make')->once()->andReturn(app('validator'));
        app('validator')->shouldReceive('fails')->once()->andReturn(true);
        app('validator')->shouldReceive('errors')->once()->andReturn(['email' => ['The email field is required.']]);

        $response = (new AuthController())->register();

        $response->assertStatus(400)
            ->assertJsonStructure(['email']);
    }
}
