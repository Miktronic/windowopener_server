<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingsResource;
use Illuminate\Http\Request;

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

    public function update(Request $request){
        $attrs = $request->validate([
            'is_auto' => ['nullable', 'boolean'],
            'low_temperature' => ['nullable', 'numeric'],
            'high_temperature' => ['nullable', 'numeric'],
        ]);

        $user = auth()->user();
        foreach($attrs as $key => $value){
            if($value === null) unset($attrs[$key]);
        }

        $user->settings()->update($attrs);
        $settings = $user->settings;

        return SettingsResource::make($settings);
    }
}
