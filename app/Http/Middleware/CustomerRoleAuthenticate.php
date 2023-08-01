<?php

namespace App\Http\Middleware;

class CustomerRoleAuthenticate extends Authenticate
{
    protected $allowedRole = ['customer'];
}
