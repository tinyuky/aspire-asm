<?php

namespace App\Http\Middleware;

class AdminRoleAuthenticate extends Authenticate
{
    protected $allowedRole = ['admin'];
}
