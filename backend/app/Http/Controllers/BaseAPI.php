<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Consts\AuthConsts;

class BaseAPI extends BaseController
{
    protected $roles = false;
    protected $user;

    public function __construct(Request $request)
    {
        $this->user = $request->user();
    }

    protected function error($message, $errCode=500) {
        throw new HttpResponseException(new JsonResponse([
            'message' => $message
        ], $errCode));
    }

    protected function auth(Request $request, $roles = false) {
        if (!$roles) $roles = $this->roles;
        $user = $request->user();
        if ($user->role !== AuthConsts::ROLE_SYS_ADMIN && $roles != false && !in_array($user->role, $roles)) {
            $this->error('Not Allowed.', 403);
        }
        $this->user = $user;
    }

}
