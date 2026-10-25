<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    // Portal Perawat
    Route::get('perawat/verifikasi', [\App\Http\Controllers\NurseVerificationController::class, 'index'])->name('perawat.verifikasi');
    Route::post('perawat/verifikasi/draft', [\App\Http\Controllers\NurseVerificationController::class, 'saveDraft'])->name('perawat.verifikasi.draft');
    Route::post('perawat/verifikasi/submit', [\App\Http\Controllers\NurseVerificationController::class, 'submit'])->name('perawat.verifikasi.submit');
    Route::delete('perawat/verifikasi/document/{type}', [\App\Http\Controllers\NurseVerificationController::class, 'deleteDocument'])->name('perawat.verifikasi.delete-doc');

    // Portal Pasien (SCRUM-40: FE Katalog Layanan, Filter Kategori, Tarif & Search)
    Route::get('pasien/katalog', [\App\Http\Controllers\Patient\CatalogController::class, 'index'])->name('pasien.katalog');
});

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

    $user = \App\Models\User::where('email', $email)->first();

    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        request()->session()->regenerate();

        if ($role === 'perawat') {
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
