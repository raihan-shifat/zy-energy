<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by the User model guards when any operation attempts to delete,
 * downgrade, or deactivate the hardcoded Super Admin account.
 */
class SuperAdminGuardException extends RuntimeException
{
    //
}
