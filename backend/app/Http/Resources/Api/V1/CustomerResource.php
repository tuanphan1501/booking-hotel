<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Customer profile returned by the API (sign-in response and GET /api/v1/me).
 *
 * @mixin User
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class CustomerResource extends JsonResource
{
    /**
     * Transform the customer into an array.
     *
     * - `member_tier` is included only when the relation is loaded.
     *
     * @param  Request  $request  Current request.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_code' => $this->member_code,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'language' => $this->language,
            'point_balance' => $this->point_balance,
            'member_tier' => $this->whenLoaded('memberTier', fn () => [
                'code' => $this->memberTier->code->value,
                'name' => $this->memberTier->localizedName(),
            ]),
        ];
    }
}
