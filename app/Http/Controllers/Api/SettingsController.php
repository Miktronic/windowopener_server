<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingsResource;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $settings = $user->settings;

        if($settings){
            return SettingsResource::make($settings);
        }

        return response()->json(['message' => 'Settings not Found!'], 404);
    }
}
