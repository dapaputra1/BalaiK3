<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SuperadminUserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.superadmin_manageusers', compact('users'));
    }

    public function destroy(Request $request, User $user)
    {
        if (!$request->boolean('confirm')) {
            return redirect()->route('superadmin.users.index')->withErrors(['hapus' => 'Konfirmasi hapus diperlukan.']);
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
