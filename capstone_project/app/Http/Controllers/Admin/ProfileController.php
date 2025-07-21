<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class ProfileController extends Controller
{
    public function editAdmin()
    {
        return Inertia::render('admin/Profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateAdmin(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = $validated['password']; // sudah hashed via casts
        }

        $user->save();

        return back()->with('success', 'Profil admin berhasil diperbarui.');
    }

    public function editAdminPassword()
    {
        return Inertia::render('auth/ResetPassword', [
            'role' => Auth::user()->role,
        ]);
    }

    public function updateAdminPassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah']);
        }

        $user->password = $request->password; // karena sudah casts hashed
        $user->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Password berhasil diubah.');
    }

    public function editUser()
    {
        return Inertia::render('user/ProfileUser', [
            'user' => Auth::user(),
        ]);
    }

    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return back()->with('success', 'Profil user berhasil diperbarui.');
    }

    public function editUserPassword()
    {
        return Inertia::render('auth/ResetPassword', [
            'role' => Auth::user()->role,
        ]);
    }

    public function updateUserPassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah']);
        }

        $user->password = $request->password;
        $user->save();

        return redirect()->route('user.profile.edit')->with('success', 'Password berhasil diubah.');
    }
}
