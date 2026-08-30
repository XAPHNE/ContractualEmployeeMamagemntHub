<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ddo
 */
class DdoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ddoId' => $this->ddoId,
            'ddoName' => $this->ddoName,
            'pan' => $this->pan,
            'departmentName' => $this->departmentName,
            'directorate' => $this->directorate,
            'postName' => $this->postName,
            'officeName' => $this->officeName,
            'officeAddress' => $this->officeAddress,
            'mobileNumber' => $this->mobileNumber,
            'treasuryName' => $this->treasuryName,
            'treasuryCode' => $this->treasuryCode,
            'email' => $this->email,
            'districtName' => $this->districtName,
        ];
    }
}
