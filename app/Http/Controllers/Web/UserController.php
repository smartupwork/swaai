<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {

        $users = User::where('role_id', '!=', 3)->get();

        return view('admin.user.index', ['users' => $users]);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // This is now a soft delete

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
