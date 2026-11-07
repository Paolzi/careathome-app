<?php

use App\Models\NurseProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', function () {
    /** @var User|null $user */
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('perawat')) {
        $nurseProfile = NurseProfile::where('user_id', $user->id)->first();
        if ($nurseProfile && $nurseProfile->verification_status === 'verified') {
            return redirect()->route('perawat.dashboard');
        }
        return redirect()->route('perawat.verifikasi');
    }

    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    // Portal Perawat (Role: perawat atau admin)
    Route::middleware(['role:perawat|admin'])->group(function () {
        Route::get('perawat/dashboard', [\App\Http\Controllers\Nurse\NurseDashboardController::class, 'index'])->name('perawat.dashboard');
        Route::post('perawat/toggle-online', [\App\Http\Controllers\Nurse\NurseDashboardController::class, 'toggleOnline'])->name('perawat.toggle-online');
        Route::post('perawat/lokasi', [\App\Http\Controllers\Nurse\NurseDashboardController::class, 'updateLocation'])->name('perawat.lokasi');
        Route::post('perawat/layanan', [\App\Http\Controllers\Nurse\NurseDashboardController::class, 'updateServices'])->name('perawat.layanan.update');
        Route::post('perawat/payout', [\App\Http\Controllers\Nurse\NurseDashboardController::class, 'requestPayout'])->name('perawat.payout.request');
        Route::get('perawat/verifikasi', [\App\Http\Controllers\NurseVerificationController::class, 'index'])->name('perawat.verifikasi');
        Route::post('perawat/verifikasi/draft', [\App\Http\Controllers\NurseVerificationController::class, 'saveDraft'])->name('perawat.verifikasi.draft');
        Route::post('perawat/verifikasi/submit', [\App\Http\Controllers\NurseVerificationController::class, 'submit'])->name('perawat.verifikasi.submit');
        Route::delete('perawat/verifikasi/document/{type}', [\App\Http\Controllers\NurseVerificationController::class, 'deleteDocument'])->name('perawat.verifikasi.delete-doc');
        Route::post('perawat/documents/upload', [\App\Http\Controllers\NurseDocumentController::class, 'upload'])->name('perawat.documents.upload');
        Route::get('perawat/documents/{document}/preview', [\App\Http\Controllers\NurseDocumentController::class, 'preview'])->name('perawat.documents.preview');
        Route::post('perawat/notifications/{id}/read', [\App\Http\Controllers\NurseDocumentController::class, 'markNotificationAsRead'])->name('perawat.notifications.read');
        Route::post('perawat/notifications/read-all', [\App\Http\Controllers\NurseDocumentController::class, 'markAllNotificationsAsRead'])->name('perawat.notifications.read-all');
    });

    // Portal Pasien (Role: pasien atau admin)
    Route::middleware(['role:pasien|admin'])->group(function () {
        Route::get('pasien/katalog', [\App\Http\Controllers\Patient\CatalogController::class, 'index'])->name('pasien.katalog');
        Route::get('pasien/pesanan', [\App\Http\Controllers\Patient\OrderScheduleController::class, 'index'])->name('pasien.pesanan');
        Route::get('pasien/pembayaran', [\App\Http\Controllers\Patient\OrderScheduleController::class, 'paymentView'])->name('pasien.pembayaran');
        Route::get('pasien/pembatalan', [\App\Http\Controllers\Patient\OrderScheduleController::class, 'cancellationView'])->name('pasien.pembatalan');
        Route::post('pasien/pesanan/{order}/bayar/token', [\App\Http\Controllers\Patient\OrderScheduleController::class, 'getPaymentToken'])->name('pasien.pesanan.payment-token');
        Route::post('pasien/pesanan/{order}/bayar', [\App\Http\Controllers\Patient\OrderScheduleController::class, 'pay'])->name('pasien.pesanan.pay');
        Route::post('pasien/pesanan/{order}/batal', [\App\Http\Controllers\Patient\OrderScheduleController::class, 'cancel'])->name('pasien.pesanan.cancel');
    });

    // Portal Admin (Role Admin)
    Route::prefix('admin')->middleware(['role:admin'])->name('admin.')->group(function () {
        Route::get('dashboard', function () {
            return redirect()->route('admin.nurses.index');
        })->name('dashboard');

        // Audit Legalitas Nakes
        Route::get('nurses', [\App\Http\Controllers\Admin\AdminNurseVerificationController::class, 'index'])->name('nurses.index');
        Route::post('nurses/{nurseProfile}/approve', [\App\Http\Controllers\Admin\AdminNurseVerificationController::class, 'approve'])->name('nurses.approve');
        Route::post('nurses/{nurseProfile}/reject', [\App\Http\Controllers\Admin\AdminNurseVerificationController::class, 'reject'])->name('nurses.reject');
        Route::post('verifikasi-nakes/{nurseProfile}/setujui', [\App\Http\Controllers\Admin\AdminNurseVerificationController::class, 'approve'])->name('nurses.setujui');
        Route::post('verifikasi-nakes/{nurseProfile}/tolak', [\App\Http\Controllers\Admin\AdminNurseVerificationController::class, 'reject'])->name('nurses.tolak');
        Route::post('nurses/{nurseProfile}/verify-ktki', [\App\Http\Controllers\Admin\AdminNurseVerificationController::class, 'verifyKtki'])->name('nurses.verify-ktki');
        Route::get('documents/{document}/preview', [\App\Http\Controllers\Admin\AdminNurseVerificationController::class, 'previewDocument'])->name('documents.preview');

        // SCRUM-57: Master Data Layanan & Tarif
        Route::resource('services', \App\Http\Controllers\Admin\AdminServiceCatalogController::class)->names('services');

        // SCRUM-58: Monitoring Escrow & Audit Saldo
        Route::get('escrow', [\App\Http\Controllers\Admin\AdminEscrowMonitoringController::class, 'index'])->name('escrow.index');
        Route::get('escrow/export', [\App\Http\Controllers\Admin\AdminEscrowMonitoringController::class, 'exportCsv'])->name('escrow.export');

        // SCRUM-59: Otorisasi Payout & Input Bukti Transfer
        Route::get('payouts', [\App\Http\Controllers\Admin\AdminPayoutController::class, 'index'])->name('payouts.index');
        Route::post('payouts/{payout}/transfer', [\App\Http\Controllers\Admin\AdminPayoutController::class, 'transfer'])->name('payouts.transfer');
        Route::post('payouts/{payout}/reject', [\App\Http\Controllers\Admin\AdminPayoutController::class, 'reject'])->name('payouts.reject');

        // SCRUM-60: Manajemen Pengguna
        Route::get('users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/toggle-status', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
    });
});

// Midtrans Webhook Handler Callback
Route::post('payments/webhook', [\App\Http\Controllers\Api\PaymentWebhookController::class, 'handleWebhook'])->name('payments.webhook');

Route::get('demo-login/{role}', function (string $role) {
    if (!app()->environment('local')) {
        abort(403, 'Akses simulasi instan hanya tersedia pada environment local testing.');
    }

    if ($role === 'admin') {
        abort(403, 'Akses simulasi untuk akun Administrator dinonaktifkan demi alasan keamanan data.');
    }

    $email = match ($role) {
        'perawat' => 'perawat@careathome.com',
        'pasien' => 'pasien@careathome.com',
        default => 'pasien@careathome.com',
    };

    /** @var User|null $user */
    $user = User::where('email', $email)->first();

    if ($user) {
        Auth::login($user);
        request()->session()->regenerate();

        if ($role === 'perawat') {
            // Jika sudah verified, langsung ke dashboard kerja
            $nurseProfile = NurseProfile::where('user_id', $user->id)->first();
            if ($nurseProfile && $nurseProfile->verification_status === 'verified') {
                return redirect()->route('perawat.dashboard');
            }
            return redirect()->route('perawat.verifikasi');
        }

        if ($role === 'pasien') {
            return redirect()->route('pasien.katalog');
        }

        return redirect()->route('dashboard');
    }

    return redirect()->route('login')->with('status', 'Silakan jalankan seeder terlebih dahulu.');
})->name('demo.login');

Route::match(['get', 'post'], 'logout', function (\App\Livewire\Actions\Logout $logout) {
    $logout();
    return redirect('/');
})->name('logout');

require __DIR__.'/auth.php';
