<?php

namespace Glumen\Authorization\Gateway\Kong;

use App\Data\Models\User;
use Glumen\Authorization\Gateway\Contracts\AuthContract;
use Illuminate\Http\Request;

/**
 * Class Auth
 *
 * @package Awok\Autrhorization\Gateway\Kong
 */
class Auth implements AuthContract
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return int|null authenticated userid or null
     */
    public function handle(Request $request)
    {
        if (
            ! $request->headers->has('x-anonymous-consumer')
            && $request->headers->has('Authorization')
            && $request->headers->has('x-consumer-id')
            && $request->headers->has('x-authenticated-userid')
        ) {
            return User::find($request->headers->get('x-authenticated-userid'));
        }

        return null;
    }
}