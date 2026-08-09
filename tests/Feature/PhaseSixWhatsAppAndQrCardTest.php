<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ScheduleItem;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\WhatsAppService;
use Tests\TestCase;

class PhaseSixWhatsAppAndQrCardTest extends TestCase
{
    public function test_real_school_data_exists_in_database(): void
    {
        $school = School::first();

        $this->assertNotNull($school);
        $this->assertEquals('SMK MUTHIA HARAPAN CICALENGKA', $school->name);
        $this->assertEquals('69725846', $school->npsn);
        $this->assertEquals('Jl. Babakan Peuteuy No. 300', $school->address);
        $this->assertEquals('Cicalengka', $school->district);
        $this->assertEquals('A', $school->accreditation);
    }

    public function test_teacher_can_scan_student_id_card_qr_code(): void
    {
        $admin   = User::where('username', 'admin')->first();
        $student = Student::first();
        $si      = ScheduleItem::first();

        if (! $admin || ! $student) {
            $this->markTestSkipped('Data admin atau student belum tersedia.');
        }

        $response = $this->actingAs($admin)
            ->postJson('/attendance/scan-student', [
                'nisn'             => $student->nisn,
                'schedule_item_id' => $si?->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'       => 'success',
                'student_name' => $student->name,
                'student_nisn' => $student->nisn,
            ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'method'     => 'qr_card',
            'status'     => 'H',
        ]);
    }

    public function test_scan_student_qr_code_with_invalid_nisn_returns_404(): void
    {
        $admin = User::where('username', 'admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Data admin belum tersedia.');
        }

        $response = $this->actingAs($admin)
            ->postJson('/attendance/scan-student', [
                'nisn' => '999999999999',
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Siswa dengan NISN 999999999999 tidak ditemukan.',
            ]);
    }

    public function test_admin_can_view_student_id_card(): void
    {
        $admin   = User::where('username', 'admin')->first();
        $student = Student::first();

        if (! $admin || ! $student) {
            $this->markTestSkipped('Data admin atau student belum tersedia.');
        }

        $response = $this->actingAs($admin)
            ->get("/master/students/{$student->id}/id-card");

        $response->assertStatus(200);
        $response->assertSee($student->name);
        $response->assertSee($student->nisn);
        $response->assertSee('SMK MUTHIA HARAPAN CICALENGKA');
        $response->assertSee('KARTU TANDA PELAJAR');
    }

    public function test_admin_can_view_bulk_student_id_cards(): void
    {
        $admin = User::where('username', 'admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Data admin belum tersedia.');
        }

        $response = $this->actingAs($admin)
            ->get('/master/students/id-cards');

        $response->assertStatus(200);
        $response->assertSee('Cetak Massal Kartu Pelajar');
        $response->assertSee('SMK MUTHIA HARAPAN CICALENGKA');
    }

    public function test_whatsapp_service_handles_formatting_and_disabled_gracefully(): void
    {
        \App\Models\Setting::set('wa_gateway_enabled', '0');
        $service = app(WhatsAppService::class);

        $result = $service->sendSingle('08123456789', 'Test message');
        $this->assertFalse($result);

        $bulkResult = $service->sendBulk([
            ['phone' => '08123456789', 'message' => 'Halo 1'],
            ['phone' => '08222222222', 'message' => 'Halo 2'],
        ]);
        $this->assertFalse($bulkResult);

        $this->assertDatabaseHas('wa_logs', [
            'phone' => '08123456789',
        ]);
    }

    public function test_admin_can_view_wa_logs_page(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)
            ->get('/attendance/wa-logs');

        $response->assertStatus(200);
        $response->assertSee('Log Notifikasi WhatsApp Terkirim');
    }

    public function test_admin_can_update_wa_gateway_settings(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post('/master/school/wa-settings', [
                'wa_gateway_url'     => 'https://api-gateway.smkmuthiaharapanclk.com',
                'wa_gateway_key'     => 'wag_custom_key_12345',
                'wa_gateway_enabled' => '1',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_admin_can_send_test_wa_message(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post('/master/school/wa-test', [
                'phone'          => '081234567890',
                'recipient_name' => 'Testing User',
                'message'        => 'Pesan Uji Coba WhatsApp Gateway',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('wa_logs', [
            'phone' => '081234567890',
        ]);
    }

    public function test_admin_can_update_wa_template(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post('/master/school/wa-template', [
                'wa_template_attendance' => "PRESENSI: {nama} kelas {kelas} status {status} tgl {tanggal}",
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_admin_can_update_attendance_times(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post('/master/school/attendance-times', [
                'attendance_time_entry' => '07:00',
                'attendance_time_late'  => '07:15',
                'attendance_time_exit'  => '15:30',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
