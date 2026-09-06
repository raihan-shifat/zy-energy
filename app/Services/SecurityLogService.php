<?php

namespace App\Services;

use App\Models\SecurityLog;

class SecurityLogService
{
    /**
     * Record a security-sensitive event to the security_logs table.
     */
    public static function log(
        string $event,
        ?string $targetEmail = null,
        ?int $actorId = null,
        array $meta = []
    ): void {
        try {
            SecurityLog::create([
                'event' => $event,
                'target_email' => $targetEmail,
                'actor_id' => $actorId ?: auth()->id(),
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
                'meta' => $meta,
            ]);
        } catch (\Throwable $e) {
            // Logging must never break the request flow.
            report($e);
        }
    }
}
