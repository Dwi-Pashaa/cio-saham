<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Mail\CashNotificationMail;
use App\Mail\DailyFinancialReportMail;
use App\Mail\ResetPasswordOtpMail;
use App\Models\CashIncome;
use App\Models\CashOutcome;
use App\Models\Setting;
use App\Models\Shareholder;
use App\Models\User;
use App\Services\CashNotificationService;
use App\Services\FonnteService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CashNotificationFonnteTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->seed(RolePermissionSeeder::class);

        $this->adminUser = User::where('email', 'admin@cionetwork.id')->first();
        Storage::fake('public');

        // Set up Settings with Fonnte Token & Target WA
        Setting::updateOrCreate(
            ['id' => 1],
            [
                'telp'                 => '628123456789',
                'target_wa_kas'        => '628123456789',
                'fonnte_token'         => 'mock_fonnte_token_12345',
                'notification_channel' => 'whatsapp',
                'admin_fee'            => 0,
            ]
        );
    }

    public function test_fonnte_service_formats_phone_number_correctly(): void
    {
        $service = app(FonnteService::class);
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('081234567890'));
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('6281234567890'));
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('+62 812-3456-7890'));
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('81234567890'));
    }

    public function test_fonnte_service_sends_message_with_http_mock(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['628123456789'],
                'detail' => 'success',
            ], 200),
        ]);

        $service = app(FonnteService::class);
        $result = $service->sendMessage('08123456789', 'Halo ini test pesan');

        $this->assertTrue($result['status']);
        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'mock_fonnte_token_12345') &&
                   $request['target'] === '628123456789' &&
                   $request['message'] === 'Halo ini test pesan';
        });
    }

    public function test_cash_notification_service_formats_income_message_properly(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $file = UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');
        $path = $file->store('incomes', 'public');

        $income = CashIncome::create([
            'transaction_number' => 'KM-202610-0001',
            'transaction_date'   => '2026-10-05',
            'sender_name'        => 'PT Sinergi Mitra',
            'bank_name'          => 'BCA PT CIO',
            'account_number'     => '8830112233',
            'amount'             => 10000000.00,
            'has_admin_fee'      => true,
            'admin_fee'          => 6500.00,
            'net_amount'         => 9993500.00,
            'proof_file'         => $path,
            'notes'              => 'Setoran modal termin 1',
            'created_by'         => $this->adminUser->id,
        ]);

        $service = app(CashNotificationService::class);
        $result = $service->notifyIncomeCreated($income);

        $this->assertTrue($result['status']);

        Http::assertSent(function ($request) {
            $msg = $request['message'];
            return str_contains($msg, 'NOTIFIKASI saldo masuk ke rekening PT CIO NETWORK') &&
                   str_contains($msg, 'PT Sinergi Mitra') &&
                   str_contains($msg, 'BCA PT CIO') &&
                   str_contains($msg, 'Rp 10.000.000') &&
                   str_contains($msg, 'SALDO TERBARU   : Rp 9.993.500') &&
                   str_contains($msg, 'SALDO SEBELUMNYA: Rp 0');
        });
    }

    public function test_cash_notification_service_formats_outcome_message_properly(): void
    {
        // Add initial income to have previous balance
        CashIncome::create([
            'transaction_number' => 'KM-INIT',
            'transaction_date'   => '2026-10-01',
            'sender_name'        => 'Kas Awal',
            'bank_name'          => 'BCA',
            'account_number'     => '123',
            'amount'             => 20000000.00,
            'has_admin_fee'      => false,
            'admin_fee'          => 0,
            'net_amount'         => 20000000.00,
            'proof_file'         => 'incomes/mock.jpg',
            'notes'              => 'Modal Awal',
            'created_by'         => $this->adminUser->id,
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $file = UploadedFile::fake()->create('proof_out.jpg', 100, 'image/jpeg');
        $path = $file->store('outcomes/proofs', 'public');

        $outcome = CashOutcome::create([
            'transaction_number' => 'KK-202610-0001',
            'transaction_date'   => '2026-10-05',
            'recipient_name'     => 'Vendor Hardware',
            'bank_name'          => 'Mandiri',
            'account_number'     => '9988776655',
            'amount'             => 5000000.00,
            'has_admin_fee'      => true,
            'admin_fee'          => 2500.00,
            'total_amount'       => 5002500.00,
            'proof_file'         => $path,
            'receipt_file'       => null,
            'notes'              => 'Pembelian router rackmount',
            'is_asset'           => false,
            'created_by'         => $this->adminUser->id,
        ]);

        $service = app(CashNotificationService::class);
        $result = $service->notifyOutcomeCreated($outcome);

        $this->assertTrue($result['status']);

        Http::assertSent(function ($request) {
            $msg = $request['message'];
            return str_contains($msg, 'NOTIFIKASI saldo Keluar 05/10/2026') &&
                   str_contains($msg, 'Vendor Hardware') &&
                   str_contains($msg, 'Mandiri') &&
                   str_contains($msg, 'Rp 5.000.000') &&
                   str_contains($msg, 'pake admin ya/ tidak    : Ya, Rp 2.500') &&
                   str_contains($msg, 'SALDO SEBELUMNYA: Rp 20.000.000') &&
                   str_contains($msg, 'SALDO TERBARU   : Rp 14.997.500');
        });
    }

    public function test_notification_fault_tolerance_does_not_crash_income_or_outcome_store(): void
    {
        // Mock Fonnte failing with 500 Server Error
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['message' => 'Internal server error'], 500),
        ]);

        $fileIn = UploadedFile::fake()->create('proof_in.jpg', 100, 'image/jpeg');

        // Storing income should succeed and redirect normally
        $responseIn = $this->actingAs($this->adminUser)->post(route('cash-incomes.store'), [
            'transaction_date'      => '2026-10-05',
            'source_account_name'   => 'Klien PT Sukses',
            'bank_name'             => 'BCA',
            'account_number'        => '1122334455',
            'amount'                => '2.000.000',
            'has_admin_fee'         => 'tidak',
            'proof_file'            => $fileIn,
            'notes'                 => 'Pemasukan normal',
        ]);

        $responseIn->assertRedirect(route('cash-incomes.index'));
        $this->assertDatabaseHas('cash_incomes', [
            'sender_name' => 'Klien PT Sukses',
            'amount'      => 2000000.00,
        ]);
    }

    public function test_cash_notification_service_broadcasts_to_all_active_shareholders(): void
    {
        // Create active shareholders with phone numbers
        \App\Models\Shareholder::create([
            'name'           => 'Investor A',
            'id_card_number' => '3201111122223331',
            'email'          => 'investorA@cionetwork.id',
            'phone'          => '081299991111',
            'status'         => 'active',
        ]);
        \App\Models\Shareholder::create([
            'name'           => 'Investor B',
            'id_card_number' => '3201111122223332',
            'email'          => 'investorB@cionetwork.id',
            'phone'          => '081299992222',
            'status'         => 'active',
        ]);

        $service = app(CashNotificationService::class);
        $recipients = $service->getRecipientPhoneNumbers();

        $this->assertStringContainsString('6281299991111', $recipients);
        $this->assertStringContainsString('6281299992222', $recipients);
        $this->assertStringContainsString('628123456789', $recipients); // Management number from settings
    }

    public function test_admin_can_update_fonnte_settings_and_test_connection(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        // 1. Update settings
        $response = $this->actingAs($this->adminUser)->post(route('setting.store'), [
            'target_wa_kas'        => '6289988776655',
            'fonnte_token'         => 'new_secret_token_abc',
            'notification_channel' => 'whatsapp',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('settings', [
            'target_wa_kas' => '6289988776655',
            'fonnte_token'  => 'new_secret_token_abc',
        ]);

        // 2. Test connection
        $testResponse = $this->actingAs($this->adminUser)->postJson(route('setting.test-fonnte'), [
            'target' => '6289988776655',
        ]);

        $testResponse->assertStatus(200);
        $testResponse->assertJson(['status' => 'success']);
    }

    public function test_cash_notification_channel_both_sends_to_both_whatsapp_and_email(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        Setting::first()->update([
            'notification_channel' => 'both',
        ]);

        Shareholder::create([
            'name'           => 'Investor Dual',
            'id_card_number' => '3201111122223399',
            'email'          => 'dual@cionetwork.id',
            'phone'          => '081299998888',
            'status'         => 'active',
        ]);

        $file = UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');
        $path = $file->store('incomes', 'public');

        $income = CashIncome::create([
            'transaction_number' => 'KM-TEST-DUAL',
            'transaction_date'   => '2026-10-10',
            'sender_name'        => 'Klien Test Dual',
            'bank_name'          => 'BCA',
            'account_number'     => '1234567890',
            'amount'             => 5000000.00,
            'has_admin_fee'      => false,
            'admin_fee'          => 0,
            'net_amount'         => 5000000.00,
            'proof_file'         => $path,
            'notes'              => 'Dual notification test',
            'created_by'         => $this->adminUser->id,
        ]);

        $service = app(CashNotificationService::class);
        $result = $service->notifyIncomeCreated($income);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['email']);
        $this->assertNotNull($result['whatsapp']);

        // Assert WhatsApp sent via Fonnte HTTP
        Http::assertSent(function ($request) {
            return str_contains($request['message'], 'Klien Test Dual') &&
                   str_contains($request['message'], '5.000.000');
        });

        // Assert Email sent
        Mail::assertSent(CashNotificationMail::class, function ($mail) {
            return $mail->type === 'income';
        });
    }

    public function test_cash_notification_channel_email_only_does_not_send_whatsapp(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        Setting::first()->update([
            'notification_channel' => 'email',
        ]);

        Shareholder::create([
            'name'           => 'Investor Email',
            'id_card_number' => '3201111122223377',
            'email'          => 'emailonly@cionetwork.id',
            'phone'          => '081299997777',
            'status'         => 'active',
        ]);

        $file = UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');
        $path = $file->store('incomes', 'public');

        $income = CashIncome::create([
            'transaction_number' => 'KM-TEST-EMAIL',
            'transaction_date'   => '2026-10-10',
            'sender_name'        => 'Klien Email Only',
            'bank_name'          => 'Mandiri',
            'account_number'     => '9876543210',
            'amount'             => 3000000.00,
            'has_admin_fee'      => false,
            'admin_fee'          => 0,
            'net_amount'         => 3000000.00,
            'proof_file'         => $path,
            'notes'              => 'Email only notification test',
            'created_by'         => $this->adminUser->id,
        ]);

        $service = app(CashNotificationService::class);
        $result = $service->notifyIncomeCreated($income);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['email']);
        $this->assertNull($result['whatsapp']);

        // Assert Email sent, WhatsApp NOT sent
        Mail::assertSent(CashNotificationMail::class);
        Http::assertNotSent(function ($request) {
            return str_contains($request['message'] ?? '', 'Klien Email Only');
        });
    }

    public function test_cash_notification_channel_whatsapp_only_does_not_send_email(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        Setting::first()->update([
            'notification_channel' => 'whatsapp',
        ]);

        $file = UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');
        $path = $file->store('incomes', 'public');

        $income = CashIncome::create([
            'transaction_number' => 'KM-TEST-WA',
            'transaction_date'   => '2026-10-10',
            'sender_name'        => 'Klien WA Only',
            'bank_name'          => 'BNI',
            'account_number'     => '5544332211',
            'amount'             => 2500000.00,
            'has_admin_fee'      => false,
            'admin_fee'          => 0,
            'net_amount'         => 2500000.00,
            'proof_file'         => $path,
            'notes'              => 'WA only notification test',
            'created_by'         => $this->adminUser->id,
        ]);

        $service = app(CashNotificationService::class);
        $result = $service->notifyIncomeCreated($income);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['whatsapp']);
        $this->assertNull($result['email']);

        // Assert WhatsApp sent, Email NOT sent
        Http::assertSent(function ($request) {
            return str_contains($request['message'], 'Klien WA Only') &&
                   str_contains($request['message'], '2.500.000');
        });
        Mail::assertNotSent(CashNotificationMail::class);
    }

    public function test_cash_notification_channel_none_sends_neither(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        Setting::first()->update([
            'notification_channel' => 'none',
        ]);

        $file = UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');
        $path = $file->store('incomes', 'public');

        $income = CashIncome::create([
            'transaction_number' => 'KM-TEST-NONE',
            'transaction_date'   => '2026-10-10',
            'sender_name'        => 'Klien None',
            'bank_name'          => 'BCA',
            'account_number'     => '1122334455',
            'amount'             => 1000000.00,
            'has_admin_fee'      => false,
            'admin_fee'          => 0,
            'net_amount'         => 1000000.00,
            'proof_file'         => $path,
            'notes'              => 'None notification test',
            'created_by'         => $this->adminUser->id,
        ]);

        $service = app(CashNotificationService::class);
        $result = $service->notifyIncomeCreated($income);

        $this->assertFalse($result['success']);
        $this->assertNull($result['whatsapp']);
        $this->assertNull($result['email']);

        // Assert neither sent
        Mail::assertNotSent(CashNotificationMail::class);
        Http::assertNotSent(function ($request) {
            return str_contains($request['message'] ?? '', 'Klien None');
        });
    }

    public function test_forgot_password_sends_to_both_email_and_whatsapp_when_channel_is_both(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        Setting::first()->update([
            'notification_channel' => 'both',
        ]);

        $this->adminUser->update([
            'phone' => '081234567890',
        ]);

        $response = $this->post(route('password.email'), [
            'username_or_email' => $this->adminUser->email,
        ]);

        $response->assertRedirect(route('password.verify.form'));
        $response->assertSessionHas('success');

        // Both Email and WhatsApp must have received OTP
        Mail::assertSent(ResetPasswordOtpMail::class, function ($mail) {
            return $mail->hasTo($this->adminUser->email);
        });

        $this->assertStringContainsString('Email', session('reset_password_sent_to'));
        $this->assertStringContainsString('WhatsApp', session('reset_password_sent_to'));
    }

    public function test_daily_financial_report_command_respects_notification_channel_both_and_none(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        Shareholder::create([
            'name'             => 'Daily Investor',
            'id_card_number'   => '3201111122223388',
            'email'            => 'daily@cionetwork.id',
            'phone'            => '081234560000',
            'status'           => 'active',
            'total_percentage' => 20.0,
        ]);

        // 1. Channel = both
        Setting::first()->update(['notification_channel' => 'both']);
        $this->artisan('finance:send-daily-report --date=2026-10-10')
            ->assertSuccessful();

        Mail::assertSent(DailyFinancialReportMail::class);
        Http::assertSent(function ($request) {
            return str_contains($request['message'] ?? '', 'LAPORAN KEUANGAN HARIAN') &&
                   str_contains($request['target'] ?? '', '6281234560000');
        });

        // 2. Channel = none
        Mail::fake();
        Http::fake();
        Setting::first()->update(['notification_channel' => 'none']);
        $this->artisan('finance:send-daily-report --date=2026-10-10')
            ->assertSuccessful();

        Mail::assertNotSent(DailyFinancialReportMail::class);
        Http::assertNothingSent();
    }
}
