<?php

namespace App\Http\Controllers;

use App\Models\UserAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'username' => 'Incorrect username or password'
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'username' => 'Your account is inactive. Please contact the administrator.'
            ]);
        }

        if ($user->systemRole?->id === 1) {
            session([
                'is_superadmin' => true
            ]);

            return redirect('/dashboard');
        }

        $userAccesses = $user->userAccesses;

        if ($userAccesses->isEmpty()) {
            Auth::logout();
            return back()->withErrors([
                'username' => 'User does not have organization access'
            ]);
        }

        $access = $userAccesses->first();

        session([
            'is_superadmin' => false,
            'active_access_id' => $access->id,
            'active_organization_id' => $access->organization_id,
            'active_division_id' => $access->division_id,
            'active_role_id' => $access->role_id,
        ]);

        return redirect('/dashboard/sla');
    }

    public function switchContext(Request $request)
    {
        $access = UserAccess::where('id', $request->access_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        session([
            'active_access_id' => $access->id,
            'active_organization_id' => $access->organization_id,
            'active_division_id' => $access->division_id,
            'active_role_id' => $access->role_id,
        ]);

        return response()->json([
            'message' => 'Context switched'
        ]);
    }

    public function logout()
    {
        Auth::logout();
        session()->flush();

        return redirect('/login');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}