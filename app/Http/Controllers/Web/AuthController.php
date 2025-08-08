<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Support\Carbon;
use App\Models\Setting;

class AuthController extends Controller
{


    public function login_form()
    {
        $settings = Setting::first();

        return view('auth.login', ['settings' => $settings]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();


        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }


        if ($user->role_id != 3) {
            return back()->withErrors(['email' => 'Sorry, you do not have the necessary privileges.'])->withInput();
        }

        Auth::login($user);
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function dashboard()
    {

        $settings = Setting::first();

        $today = Carbon::today();

        $totalUsers = User::count();
        $totalBusinesses = Business::count();
        $totalCategories = Category::count();

        $todayUsers = User::whereDate('created_at', $today)->count();
        $todayCategory = Category::whereDate('created_at', $today)->count();
        $todayBusiness = Business::whereDate('created_at', $today)->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalBusinesses',
            'totalCategories',
            'todayUsers',
            'todayCategory',
            'todayBusiness',
            'settings'
        ));
    }
}
