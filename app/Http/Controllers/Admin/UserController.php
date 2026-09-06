<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\SuperAdminGuardException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityLogService;
use App\Traits\PreventsManagerDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    use PreventsManagerDelete;
    /**
     * Display a listing of manageable users.
     */
    public function index()
    {
        return view('admin.users.index');
    }

    public function getData()
    {
        $actor = auth()->user();
        $allowedRoles = User::rolesManageableBy($actor);

        $users = User::select(['id', 'name', 'email', 'role', 'is_active', 'phone'])
            ->whereIn('role', $allowedRoles);

        return DataTables::of($users)
            ->addColumn('status', function (User $user) {
                return $user->is_active ? 'active' : 'inactive';
            })
        ->addColumn('action', function (User $user) use ($actor) {
            // Admin cannot edit/delete other Admins
            if (! $actor->isSuperAdmin() && $user->role === User::ROLE_ADMIN) {
                return '';
            }

            $editBtn = '<a class="btn btn-sm btn-outline-primary me-1"
                       href="'.route('admin.users.edit', $user).'">
                        <i class="bi bi-pencil"></i>
                    </a>';

            $deleteBtn = '<span class="border border-danger dt-trash rounded-3 d-inline-block"
                          onclick="deleteUser('.$user->id.')">
                        <i class="bi bi-trash-fill text-danger"></i>
                    </span>';

            return $editBtn . ' ' . (auth()->user()->role === 'manager' ? '' : $deleteBtn);
        })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $actor = auth()->user();

        return view('admin.users.create', [
            'roles' => User::rolesManageableBy($actor),
        ]);
    }

    public function store(Request $request)
    {
        $actor = auth()->user();
        $allowedRoles = User::rolesManageableBy($actor);
        $data = $this->validateUser($request, null, $allowedRoles);

        // Admin cannot create other Admins
        if (! $actor->isSuperAdmin() && $data['role'] === User::ROLE_ADMIN) {
            abort(403, 'You cannot create Admin accounts.');
        }

        User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'is_active' => true,
        ]);

        SecurityLogService::log('admin.user_created', $data['email'], auth()->id(), ['role' => $data['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', __('cms.users.success_created'));
    }

    public function edit(User $user)
    {
        $this->guardManageable($user);
        $actor = auth()->user();

        // Admin cannot edit other Admins
        if (! $actor->isSuperAdmin() && $user->role === User::ROLE_ADMIN) {
            abort(403, 'You cannot edit Admin accounts.');
        }

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => User::rolesManageableBy($actor),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->guardManageable($user);
        $actor = auth()->user();

        // Admin cannot update other Admins
        if (! $actor->isSuperAdmin() && $user->role === User::ROLE_ADMIN) {
            abort(403, 'You cannot update Admin accounts.');
        }

        $allowedRoles = User::rolesManageableBy($actor);
        $data = $this->validateUser($request, $user, $allowedRoles);
        $updates = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
        ];

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->update($updates);

        SecurityLogService::log('admin.user_updated', $data['email'], auth()->id(), ['role' => $data['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', __('cms.users.success_updated'));
    }

    public function destroy(User $user)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $this->guardManageable($user);
        $actor = auth()->user();

        // Admin cannot delete other Admins
        if (! $actor->isSuperAdmin() && $user->role === User::ROLE_ADMIN) {
            abort(403, 'You cannot delete Admin accounts.');
        }

        try {
            $email = $user->email;
            $user->delete();
        } catch (SuperAdminGuardException $e) {
            abort(403, $e->getMessage());
        }

        SecurityLogService::log('admin.user_deleted', $email, auth()->id());

        return response()->json([
            'success' => true,
            'message' => __('cms.users.success_deleted'),
        ]);
    }

    /**
     * Reject any operation targeting an account that is not manageable
     * (i.e. never the Super Admin), regardless of who is asking.
     */
    protected function guardManageable(User $user): void
    {
        if (! in_array($user->role, User::MANAGEABLE_ROLES, true) || $user->isSuperAdmin()) {
            SecurityLogService::log('super_admin_guard.managed_attempt_blocked', $user->email, auth()->id());
            abort(403, 'This account cannot be managed.');
        }
    }

    /**
     * Shared validation for store + update. Email uniqueness ignores the
     * current user on update. $allowedRoles limits which roles can be assigned.
     */
    protected function validateUser(Request $request, ?User $except = null, array $allowedRoles = []): array
    {
        $request->merge([
            'phone' => $request->input('phone') ?: null,
        ]);

        $emailRule = ['required', 'email', 'max:255'];

        if ($except) {
            $emailRule[] = Rule::unique('users', 'email')->ignore($except->id);
        } else {
            $emailRule[] = Rule::unique('users', 'email');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRule,
            'role' => ['required', Rule::in($allowedRoles ?: User::MANAGEABLE_ROLES)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]+$/'],
        ];

        if ($except) {
            $rules['password'] = ['nullable', 'confirmed', Password::min(8)];
        } else {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        return $request->validate($rules);
    }
}
