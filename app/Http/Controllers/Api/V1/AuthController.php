<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Mobile App Login Endpoint
     * Supports Login using Username, Email, NISN (Siswa), NIP/NUPTK (Guru)
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'       => 'required|string',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ], [
            'login.required'    => 'Username / Email / NISN / NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput  = trim($request->login);
        $password    = $request->password;
        $deviceName  = $request->input('device_name', 'Android App');

        // 1. Search directly in users table (Username / Email)
        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        // 2. Search in Student table by NISN or NIS
        if (!$user) {
            $student = Student::where('nisn', $loginInput)
                ->orWhere('nis', $loginInput)
                ->first();

            if ($student && $student->user) {
                $user = $student->user;
            }
        }

        // 3. Search in Teacher table by NIP or NUPTK
        if (!$user) {
            $teacher = Teacher::where('nip', $loginInput)
                ->orWhere('nuptk', $loginInput)
                ->first();

            if ($teacher && $teacher->user) {
                $user = $teacher->user;
            }
        }

        // 4. Validate user existence and password
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kombinasi ID Pengguna / Email / NISN / NIP dan Password tidak sesuai.',
            ], 401);
        }

        // 5. Check user status
        if ($user->status !== 'active') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi administrator sekolah.',
            ], 403);
        }

        // 6. Generate Sanctum Token
        $token = $user->createToken($deviceName)->plainTextToken;

        // Update last login metadata
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // 7. Format user role & profile details
        $userPayload = $this->formatUserPayload($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Login berhasil',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => $userPayload,
            ],
        ]);
    }

    /**
     * Get Current Authenticated User Info
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user' => $this->formatUserPayload($user),
            ],
        ]);
    }

    /**
     * Logout & Revoke Current API Token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil. Token API telah dicabut.',
        ]);
    }

    /**
     * Format User details with specialized profile (Guru / Siswa / Admin)
     */
    private function formatUserPayload(User $user): array
    {
        $user->load(['roles', 'teacher.homeroomClasses', 'student.currentClass.major']);

        $roleName  = $user->roles->first()?->name ?? 'user';
        $roleLabel = $user->roles->first()?->label ?? 'User';

        $profile = null;

        if ($user->teacher) {
            $t = $user->teacher;
            $homeroomClass = $t->homeroomClasses->first()?->name;
            $profile = [
                'teacher_id'     => $t->id,
                'nip'            => $t->nip,
                'nuptk'          => $t->nuptk,
                'phone'          => $t->phone,
                'gender'         => $t->gender,
                'is_homeroom'    => $t->homeroomClasses->isNotEmpty(),
                'homeroom_class' => $homeroomClass,
            ];
        } elseif ($user->student) {
            $s = $user->student;
            $profile = [
                'student_id'   => $s->id,
                'nisn'         => $s->nisn,
                'nis'          => $s->nis,
                'class_id'     => $s->current_class_id,
                'class_name'   => $s->currentClass?->name ?? '-',
                'major_name'   => $s->currentClass?->major?->name ?? '-',
                'gender'       => $s->gender,
                'phone'        => $s->phone,
                'parent_phone' => $s->parent_phone,
            ];
        }

        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'username'      => $user->username,
            'email'         => $user->email,
            'role'          => $roleName,
            'role_label'    => $roleLabel,
            'avatar'        => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'last_login_at' => $user->last_login_at?->toIso8601String(),
            'profile'       => $profile,
        ];
    }
}
