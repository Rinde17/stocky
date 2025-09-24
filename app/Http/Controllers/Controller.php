<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function user(): User
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            throw new \LogicException('Authenticated user is not an instance of App\Models\User');
        }

        return $user;
    }

}
