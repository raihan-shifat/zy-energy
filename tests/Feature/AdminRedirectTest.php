<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * /admin itself: guests go to the login page, staff go to the dashboard.
 */
class AdminRedirectTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_staff_is_sent_to_dashboard(): void
    {
        $admin = User::query()->get()->first(fn (User $u) => $u->isStaff())
            ?? $this->markTestSkipped('No staff user in the database.');

        $this->actingAs($admin)->get('/admin')->assertRedirect('/admin/dashboard');
    }
}
