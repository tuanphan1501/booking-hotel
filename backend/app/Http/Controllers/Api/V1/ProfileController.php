<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CustomerResource;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Profile of the signed-in customer (MB-16 Account).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class ProfileController extends Controller
{
    /**
     * Get the signed-in customer's profile.
     *
     * - GET /api/v1/me (Bearer access token required)
     *
     * @param  Request  $request  Authenticated request.
     *
     * @return CustomerResource
     */
    public function show(Request $request): CustomerResource
    {
        /** @var User $customer */
        $customer = $request->user();

        return new CustomerResource($customer->load('memberTier'));
    }
}
