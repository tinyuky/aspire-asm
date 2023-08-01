<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected $allowedRole = [];

    public function authenticate(Request $request)
    {
        $this->checkForToken($request);

        try {
            if (! $this->auth->parseToken()->authenticate()) {
                throw new UnauthorizedHttpException('jwt-auth', 'User not found');
            }
            if (!$this->verifyRole()) {
                throw new UnauthorizedHttpException('jwt-auth', 'User do not have permission');
            }
        } catch (JWTException $e) {
            throw new UnauthorizedHttpException('jwt-auth', $e->getMessage(), $e, $e->getCode());
        }
    }

    private function verifyRole(): bool
    {
        $result = true;
        $payload = $this->auth->payload();
        if(isset($payload['role']) && !in_array($payload['role'], $this->allowedRole)){
            $result = false;
        }

        return $result;
    }
}
