<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'teacher', 'student']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $roles = Role::all();

        // Metrics
        $totalUsers = User::count();
        $totalAdmins = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'admin_sekolah']);
        })->count();
        $totalTeachers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%guru%');
        })->count();
        $totalStudents = User::whereHas('roles', function ($q) {
            $q->where('name', 'siswa');
        })->count();

        return view('users.index', compact('users', 'roles', 'totalUsers', 'totalAdmins', 'totalTeachers', 'totalStudents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $school = School::first();

        $user = User::create([
            'school_id' => $school?->id,
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'status' => $validated['status'],
        ]);

        $user->roles()->attach($validated['role_id']);

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} berhasil ditambahkan!");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => "required|string|max:50|unique:users,username,{$user->id}",
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->roles()->sync([$validated['role_id']]);

        return redirect()->route('users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui!");
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->route('users.index')->with('success', "Password untuk akun {$user->username} berhasil direset!");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan!');
        }

        if ($user->username === 'admin') {
            return back()->with('error', 'Akun Super Administrator Utama tidak boleh dihapus demi keamanan sistem!');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dinonaktifkan / dihapus dari sistem!');
    }
}
