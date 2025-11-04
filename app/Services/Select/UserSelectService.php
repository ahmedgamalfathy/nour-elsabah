<?php

namespace App\Services\Select;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserSelectService
{
    public function getAllUsers()
    {
        return User::all(['id as value','name as label']);
    }
}
