<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SecurityLogService;
use App\Support\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle a login request, with a stricter, logged throttle for the
     * hardcoded Super Admin account.
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically
        // throttle too.
        if (method_exists($this, 'hasTooManyLoginAttempts')
            && $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $isSuperAdmin = strtolower((string) $request->input('email')) === strtolower(config('auth.super_admin.email'));
        $throttleKey = 'super-admin-login:'.strtolower((string) $request->input('email')).'|'.$request->ip();
        $maxAttempts = (int) config('auth.super_admin.max_login_attempts', 3);
        $decaySeconds = (int) config('auth.super_admin.login_decay_seconds', 900);

        if ($isSuperAdmin && RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            SecurityLogService::log(
                'super_admin.login_throttled',
                strtolower((string) $request->input('email')),
                null,
                ['ip' => $request->ip()]
            );

            return $this->sendSuperAdminLockoutResponse($request, RateLimiter::availableIn($throttleKey));
        }

        // If the login attempt was unsuccessful we will increment the number of
        // attempts for this user and redirect them back to the login form.
        if (! $this->attemptLogin($request)) {
            if ($isSuperAdmin) {
                RateLimiter::hit($throttleKey, $decaySeconds);
                SecurityLogService::log(
                    'super_admin.login_failed',
                    strtolower((string) $request->input('email')),
                    null,
                    ['ip' => $request->ip()]
                );
            }

            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse($request);
        }

        if ($isSuperAdmin) {
            RateLimiter::clear($throttleKey);
            SecurityLogService::log(
                'super_admin.login_success',
                strtolower((string) $request->input('email')),
                auth()->id(),
                ['ip' => $request->ip()]
            );
        }

        return $this->sendLoginResponse($request);
    }

    /**
     * Build the lockout response for the stricter Super Admin throttle.
     */
    protected function sendSuperAdminLockoutResponse(Request $request, int $seconds)
    {
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => __('auth.throttle', ['seconds' => $seconds]),
            ]);
    }
}
