<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\JsonResponse;

trait PreventsManagerDelete
{
    protected function rejectManagerDelete(): ?JsonResponse
    {
        $user = auth()->user();

        if ($user && $user->role === User::ROLE_MANAGER) {
            return response()->json([
                'success' => false,
                'message' => 'Managers are not allowed to delete records.',
            ], 403);
        }

        return null;
    }
}
