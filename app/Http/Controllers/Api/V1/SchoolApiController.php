<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\Setting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class SchoolApiController extends Controller
{
    /**
     * Get School Profile and Settings
     */
    public function show()
    {
        $school = School::first();

        $waUrl     = Setting::get('wa_gateway_url', config('services.wa_gateway.url', 'https://api-gateway.smkmuthiaharapanclk.com'));
        $waKey     = Setting::get('wa_gateway_key', config('services.wa_gateway.key', 'wag_68507eab'));
        $waEnabled = Setting::get('wa_gateway_enabled', '1');

        $defaultTemplate = implode("\n", [
            '🏫 *NOTIFIKASI PRESENSI*',
            '*{sekolah}*',
            str_repeat('─', 30),
            '👤 Nama    : {nama}',
            '🏫 Kelas   : {kelas}',
            '📊 Status  : {status}',
            '📅 Tanggal : {tanggal}',
            '🕐 Waktu   : {waktu}',
            str_repeat('─', 30),
            '_Pesan ini dikirim otomatis oleh sistem MHC Smart School._',
        ]);

        $waTemplate = Setting::get('wa_template_attendance', $defaultTemplate);

        $timeEntry = Setting::get('attendance_time_entry', '07:00');
        $timeLate  = Setting::get('attendance_time_late', '07:15');
        $timeExit  = Setting::get('attendance_time_exit', '15:30');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'school' => $school,
                'wa_settings' => [
                    'url'     => $waUrl,
                    'key'     => $waKey,
                    'enabled' => $waEnabled,
                    'template' => $waTemplate,
                ],
                'attendance_times' => [
                    'entry' => $timeEntry,
                    'late'  => $timeLate,
                    'exit'  => $timeExit,
                ],
            ],
        ]);
    }

    /**
     * Update School Profile
     */
    public function update(Request $request)
    {
        $school = School::first();

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'npsn'           => 'nullable|string|max:50',
            'nss'            => 'nullable|string|max:50',
            'principal_name' => 'required|string|max:255',
            'accreditation'  => 'required|string|max:10',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:255',
            'website'        => 'nullable|url|max:255',
            'address'        => 'nullable|string',
            'village'        => 'nullable|string|max:100',
            'district'       => 'nullable|string|max:100',
            'regency'        => 'nullable|string|max:100',
            'province'       => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:10',
            'vision'         => 'nullable|string',
            'mission'        => 'nullable|string',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/logo');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $filename);
            $validated['logo'] = 'uploads/logo/' . $filename;
        }

        $oldValues = $school->toArray();
        $school->update($validated);

        AuditLog::create([
            'school_id'   => $school->id,
            'user_id'     => $request->user()->id,
            'user_name'   => $request->user()->name,
            'action'      => 'update_school_profile',
            'module'      => 'Master Data',
            'description' => 'Mengubah Profil Sekolah via Mobile App',
            'old_values'  => $oldValues,
            'new_values'  => $school->toArray(),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil sekolah berhasil diperbarui',
            'data'    => $school,
        ]);
    }

    /**
     * Update WA Gateway Settings
     */
    public function updateWaSettings(Request $request)
    {
        $validated = $request->validate([
            'wa_gateway_url'     => 'required|url',
            'wa_gateway_key'     => 'required|string',
            'wa_gateway_enabled' => 'required|in:0,1',
        ]);

        Setting::set('wa_gateway_url', $validated['wa_gateway_url']);
        Setting::set('wa_gateway_key', $validated['wa_gateway_key']);
        Setting::set('wa_gateway_enabled', $validated['wa_gateway_enabled']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengaturan WA Gateway berhasil disimpan',
        ]);
    }

    /**
     * Update WA Template
     */
    public function updateWaTemplate(Request $request)
    {
        $validated = $request->validate([
            'wa_template_attendance' => 'required|string',
        ]);

        Setting::set('wa_template_attendance', $validated['wa_template_attendance']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Template WA Presensi berhasil disimpan',
        ]);
    }

    /**
     * Update Attendance Times
     */
    public function updateAttendanceTimes(Request $request)
    {
        $validated = $request->validate([
            'attendance_time_entry' => 'required|date_format:H:i',
            'attendance_time_late'  => 'required|date_format:H:i',
            'attendance_time_exit'  => 'required|date_format:H:i',
        ]);

        Setting::set('attendance_time_entry', $validated['attendance_time_entry']);
        Setting::set('attendance_time_late', $validated['attendance_time_late']);
        Setting::set('attendance_time_exit', $validated['attendance_time_exit']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Batas jam presensi berhasil disimpan',
        ]);
    }

    /**
     * Test WA Message
     */
    public function sendTestWa(Request $request)
    {
        $validated = $request->validate([
            'phone'          => 'required|string',
            'recipient_name' => 'nullable|string',
            'message'        => 'required|string',
        ]);

        $waService = app(WhatsAppService::class);
        $result = $waService->sendSingle(
            phone:         $validated['phone'],
            message:       $validated['message'],
            recipientName: $validated['recipient_name'] ?? 'Penerima Uji Coba',
            type:          'single'
        );

        return response()->json([
            'status'  => $result ? 'success' : 'info',
            'message' => $result ? 'Pesan uji coba WhatsApp berhasil terkirim' : 'Proses kirim dicatat',
        ]);
    }
}
