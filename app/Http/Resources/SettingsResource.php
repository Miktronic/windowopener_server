<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
          'outside_temperature' => $this->outside_temperature,
            'inside_temperature' => $this->insideTemp(),
            'status' => $this->status,
            'is_auto' => $this->is_auto,
        ];
    }
}
