<?php


namespace App\Http\Utils;

class ResponseUtil{

    public static function failedResponse($msg = 'Something went wrong! please try again.',$code=400){
        return response()->json(['message'=>$msg],$code);
    }
}