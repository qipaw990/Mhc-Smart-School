<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\WaLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $baseUrl;
    private string $apiKey;
    private bool   $enabled;

    public function __construct()
    {
        $this->baseUrl = rtrim(Setting::get('wa_gateway_url', config('services.wa_gateway.url', 'https://api-gateway.smkmuthiaharapanclk.com')), '/');
        $this->apiKey  = Setting::get('wa_gateway_key', config('services.wa_gateway.key', 'wag_68507eab'));
        $enabledSetting = Setting::get('wa_gateway_enabled', null);
        $this->enabled = $enabledSetting !== null ? (bool) $enabledSetting : (bool) config('services.wa_gateway.enabled', true);
    }

    /**
     * Kirim pesan WA tunggal & catat log
     */
    public function sendSingle(string $phone, string $message, ?string $recipientName = null, string $type = 'single'): bool
    {
        $phoneFormatted = $this->normalizePhone($phone);

        if (! $this->enabled || empty($this->apiKey) || empty($phoneFormatted)) {
            Log::info('[WA] Gateway disabled, key empty, or invalid phone.', compact('phone', 'message'));

            // Record log as failed / skipped
            WaLog::create([
                'phone'          => $phoneFormatted ?: $phone,
                'recipient_name' => $recipientName,
                'message'        => $message,
                'type'           => $type,
                'status'         => 'failed',
                'response_info'  => 'Gateway terdaftar non-aktif / API key belum dikonfigurasi.',
            ]);

            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-API-KEY' => $this->apiKey, 'Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/api/messages/send", [
                    'phone'   => $phoneFormatted,
                    'message' => $message,
                ]);

            $isSuccess = $response->successful();

            WaLog::create([
                'phone'          => $phoneFormatted,
                'recipient_name' => $recipientName,
                'message'        => $message,
                'type'           => $type,
                'status'         => $isSuccess ? 'success' : 'failed',
                'response_info'  => 'HTTP ' . $response->status() . ' - ' . substr($response->body(), 0, 250),
            ]);

            if ($isSuccess) {
                Log::info('[WA] Pesan terkirim ke ' . $phoneFormatted);
                return true;
            }

            Log::warning('[WA] Gagal kirim ke ' . $phoneFormatted, ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::error('[WA] Exception saat kirim pesan: ' . $e->getMessage());

            WaLog::create([
                'phone'          => $phoneFormatted,
                'recipient_name' => $recipientName,
                'message'        => $message,
                'type'           => $type,
                'status'         => 'failed',
                'response_info'  => 'Exception: ' . $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Kirim pesan WA massal & catat log
     * @param array $messages [['phone' => '...', 'message' => '...', 'name' => '...'], ...]
     */
    public function sendBulk(array $messages, string $type = 'bulk'): bool
    {
        if (empty($messages)) {
            return false;
        }

        $payload = collect($messages)
            ->map(fn($m) => [
                'phone'   => $this->normalizePhone($m['phone'] ?? ''),
                'message' => $m['message'] ?? '',
                'name'    => $m['name'] ?? null,
            ])
            ->filter(fn($m) => ! empty($m['phone']))
            ->values()
            ->toArray();

        if (empty($payload)) return false;

        if (! $this->enabled || empty($this->apiKey)) {
            foreach ($payload as $item) {
                WaLog::create([
                    'phone'          => $item['phone'],
                    'recipient_name' => $item['name'] ?? null,
                    'message'        => $item['message'],
                    'type'           => $type,
                    'status'         => 'failed',
                    'response_info'  => 'Gateway non-aktif / API key belum dikonfigurasi.',
                ]);
            }
            return false;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-API-KEY' => $this->apiKey, 'Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/api/messages/bulk", [
                    'messages' => array_map(fn($item) => ['phone' => $item['phone'], 'message' => $item['message']], $payload)
                ]);

            $isSuccess = $response->successful();

            foreach ($payload as $item) {
                WaLog::create([
                    'phone'          => $item['phone'],
                    'recipient_name' => $item['name'] ?? null,
                    'message'        => $item['message'],
                    'type'           => $type,
                    'status'         => $isSuccess ? 'success' : 'failed',
                    'response_info'  => 'Bulk API HTTP ' . $response->status(),
                ]);
            }

            if ($isSuccess) {
                Log::info('[WA] Bulk kirim ' . count($payload) . ' pesan berhasil');
                return true;
            }

            Log::warning('[WA] Bulk gagal', ['status' => $response->status()]);
        } catch (\Throwable $e) {
            Log::error('[WA] Exception bulk: ' . $e->getMessage());

            foreach ($payload as $item) {
                WaLog::create([
                    'phone'          => $item['phone'],
                    'recipient_name' => $item['name'] ?? null,
                    'message'        => $item['message'],
                    'type'           => $type,
                    'status'         => 'failed',
                    'response_info'  => 'Bulk Exception: ' . $e->getMessage(),
                ]);
            }
        }

        return false;
    }

    /**
     * Kirim notifikasi absensi ke orang tua/siswa
     */
    public function sendAttendanceNotification(
        string $studentName,
        string $className,
        string $statusCode,
        string $date,
        string $time,
        ?string $phone = null,
        ?string $parentPhone = null
    ): void {
        $statusLabel = match ($statusCode) {
            'H'     => '✅ HADIR',
            'T'     => '⏰ TERLAMBAT',
            'S'     => '🏥 SAKIT',
            'I'     => '📋 IZIN',
            'A'     => '❌ ALPA (TIDAK HADIR)',
            'D'     => '🏠 DISPEN',
            'P'     => '🏕️ PRAKERIN',
            default => $statusCode,
        };

        $dateFormatted = \Carbon\Carbon::parse($date)
            ->locale('id')
            ->isoFormat('dddd, D MMMM Y');

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

        $rawTemplate = \App\Models\Setting::get('wa_template_attendance', $defaultTemplate);
        $schoolName  = \App\Models\School::first()?->name ?? 'SMK MUTHIA HARAPAN CICALENGKA';

        $message = str_replace(
            ['{nama}', '{kelas}', '{status}', '{tanggal}', '{waktu}', '{sekolah}'],
            [$studentName, $className, $statusLabel, $dateFormatted, $time, $schoolName],
            $rawTemplate
        );

        if ($phone) {
            $this->sendSingle($phone, $message, $studentName . ' (Siswa)', 'attendance');
        }

        if ($parentPhone && $parentPhone !== $phone) {
            $this->sendSingle($parentPhone, $message, 'Orang Tua ' . $studentName, 'attendance');
        }
    }

    /**
     * Normalisasi nomor HP ke format 08xxx atau 628xxx
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (empty($phone)) return '';

        if (str_starts_with($phone, '62')) {
            $phone = '0' . substr($phone, 2);
        } elseif (str_starts_with($phone, '+62')) {
            $phone = '0' . substr($phone, 3);
        }

        if (strlen($phone) < 10 || strlen($phone) > 14) return '';

        return $phone;
    }
}
