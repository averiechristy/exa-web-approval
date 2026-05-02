<?php

namespace App\Http\Controllers;

use App\Models\UserAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if ($user->systemRole->id === 1) {

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

        return redirect('/dashboard');
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
}
