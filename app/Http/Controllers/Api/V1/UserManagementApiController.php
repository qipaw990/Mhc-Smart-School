<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementApiController extends Controller
{
    /**
     * List Users with search & filter
     */
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

        $users = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 15));
        $roles = Role::all();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'roles' => $roles,
                'users' => $users,
            ],
        ]);
    }

    /**
     * Store new User
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:20',
            'role_id'  => 'required|exists:roles,id',
            'status'   => 'required|in:active,inactive',
        ]);

        $school = School::first();

        $user = User::create([
            'school_id' => $school?->id,
            'name'      => $validated['name'],
            'username'  => $validated['username'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'],
            'status'    => $validated['status'],
        ]);

        $user->roles()->attach($validated['role_id']);

        return response()->json([
            'status'  => 'success',
            'message' => "Pengguna {$user->name} berhasil ditambahkan!",
            'data'    => $user->load('roles'),
        ], 201);
    }

    /**
     * Update User
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => "required|string|max:50|unique:users,username,{$user->id}",
            'email'    => "required|email|max:255|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:6',
            'phone'    => 'nullable|string|max:20',
            'role_id'  => 'required|exists:roles,id',
            'status'   => 'required|in:active,inactive',
        ]);

        $data = [
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'],
            'status'   => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->roles()->sync([$validated['role_id']]);

        return response()->json([
            'status'  => 'success',
            'message' => "Data pengguna {$user->name} berhasil diperbarui!",
            'data'    => $user->load('roles'),
        ]);
    }

    /**
     * Reset Password for User
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Password untuk akun {$user->username} berhasil direset!",
        ]);
    }

    /**
     * Delete User
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan!',
            ], 400);
        }

        if ($user->username === 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun Super Administrator Utama tidak boleh dihapus!',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengguna berhasil dihapus dari sistem.',
        ]);
    }
}
