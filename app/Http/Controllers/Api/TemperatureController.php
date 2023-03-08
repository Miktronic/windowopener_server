<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TemperatureController extends Controller
{
    public function outsideTemperature(User $user)
    {
        if ($user->temperature()->exists()) {
            return response()->json(['success' => true, 'code' => 200, 'data' => $user->temperature->makeHidden(['created_at', 'updated_at'])]);
        }
        return response()->json(['success' => false, 'code' => 404, 'data' => []]);
    }
}
