<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $admin = auth()->user();

        return view('admin.profile.edit', compact('admin'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $admin = auth()->user();
        $data = $request->only(['name', 'email', 'phone']);

        // The Super Admin's email is the canonical, hardcoded identity — it can
        // never be changed, not even by the account owner. (Password changes
        // below are still allowed.)
        if ($admin->isSuperAdmin()) {
            unset($data['email']);
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($admin->profile_image && Storage::exists('public/'.$admin->profile_image)) {
                Storage::delete('public/'.$admin->profile_image);
            }
            $path = $request->file('profile_image')->store('admin_profiles', 'public');
            $data['profile_image'] = $path;
        }

        // Handle password update
        if ($request->filled('current_password') && $request->filled('password')) {
            if (! Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match'])->withInput();
            }
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return back()->with('success', __('cms.profile.profile_updated'));
    }
}
