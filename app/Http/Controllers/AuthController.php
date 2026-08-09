<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\School;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $school = School::first();
        return view('auth.login', compact('school'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Username, Email, NISN, atau NUPTK wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = $credentials['login'];
        $password = $credentials['password'];

        // Determine if login input is email, username, or matched against teacher/student NISN/NUPTK/NIP
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $attempt = Auth::attempt([$fieldType => $loginInput, 'password' => $password], $request->boolean('remember'));

        // If simple username/email failed, try finding student by NISN/NIS or teacher by NUPTK/NIP
        if (!$attempt) {
            $userByStudent = \App\Models\Student::where('nisn', $loginInput)
                ->orWhere('nis', $loginInput)
                ->first()?->user;

            if ($userByStudent && Auth::attempt(['id' => $userByStudent->id, 'password' => $password], $request->boolean('remember'))) {
                $attempt = true;
            } else {
                $userByTeacher = \App\Models\Teacher::where('nuptk', $loginInput)
                    ->orWhere('nip', $loginInput)
                    ->first()?->user;
                if ($userByTeacher && Auth::attempt(['id' => $userByTeacher->id, 'password' => $password], $request->boolean('remember'))) {
                    $attempt = true;
                }
            }
        }

        if ($attempt) {
            $user = Auth::user();
            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['login' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator Sekolah.']);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            AuditLog::create([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'login',
                'module' => 'Authentication',
                'description' => 'User ' . $user->name . ' berhasil login ke dalam sistem.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['login' => 'Kombinasi ID Pengguna / Email / NISN / NIP dan Password tidak sesuai.'])->withInput();
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'logout',
                'module' => 'Authentication',
                'description' => 'User ' . $user->name . ' logout dari sistem.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
