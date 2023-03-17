<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseUtil;
use Illuminate\Http\Request;
use App\Models\DeviceLog;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class DeviceLogController extends Controller
{
    public function get(Request $request)
    {
        $query = DeviceLog::orderBy('timestamp', 'DESC');
        if(!is_null($request->alias)){
            $deviceIds = Device::where('alias', 'LIKE','%'.$request->alias.'%')->pluck('id')->toArray();;
            $query->whereIn('device_id', $deviceIds);
        }
        if(!is_null($request->id)) {
            $deviceIds = Device::where('device_address', 'LIKE','%'.$request->id.'%')->pluck('id')->toArray();
            $query->whereIn('device_id', $deviceIds);
        }
        if(!is_null($request->from) && !is_null($request->to)) {
            $query->whereBetween('timestamp', [date("Y-m-d", strtotime($request->from)), date("Y-m-d", strtotime($request->to))]);
        }
        $logs = $query->get();
        return response()->json(['data' => $logs]);
    }

    public function getLogsByApp(Request $request)
    {
        $request->validate([
            'rows' => ['required', 'integer'],
            'page' => ['required', 'integer'],
        ]);
        try {
            $user_id = auth()->user()->id;
            $device_ids = Device::where('user_id',$user_id)->pluck('id');
            
            $logs = DeviceLog::whereIn('device_id',$device_ids)->orderBy('timestamp', 'DESC')->skip($request->rows * ($request->page - 1))->take($request->rows)->get();
            return response()->json(['data' => [
                'total' => sizeOf($logs),
                'items' => $logs->makeHidden(['device_id', 'status_label', 'device_alias', 'status', 'is_auto', 'temperature', 'device_address'])
            ]]);
            
        } catch (\Throwable $th) {
            //throw $th;
            Log::error("DeviceLogController getLogsByApp method",$th->getTrace());
            return ResponseUtil::failedResponse();
        }
    }

    public function deleteLogByApp($id)
    {
        DeviceLog::destroy($id);
        return response()->json(['data' => $id]);
    }
}
