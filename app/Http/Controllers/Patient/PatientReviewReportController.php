<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientReviewReportController extends Controller
{
    /**
     * Tampilkan lembar E-Report Klinis digital untuk pasien.
     */
    public function showReport(Order $order): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($order->patient_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke lembar rekam medis ini.',
            ], 403);
        }

        $order->load(['eReport.photos', 'nurse', 'serviceCatalog']);

        if (!$order->eReport) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lembar E-Report klinis untuk pesanan ini belum diterbitkan oleh perawat.',
            ], 404);
        }

        $report = $order->eReport;

        return response()->json([
            'status' => 'success',
            'data' => [
                'order_number' => $order->order_number,
                'service_name' => $order->serviceCatalog?->name,
                'nurse_name' => $order->nurse?->name ?? 'Ns. CareAtHome',
                'scheduled_date' => $order->scheduled_date?->format('d M Y'),
                'scheduled_time' => $order->scheduled_time,
                'patient_name' => $order->patient_name,
                'created_at' => $report->created_at->format('d M Y, H:i') . ' WIB',

                // Tanda-Tanda Vital (TTV)
                'vital_signs' => [
                    'blood_pressure' => [
                        'value' => "{$report->systolic}/{$report->diastolic}",
                        'unit' => 'mmHg',
                        'category' => $report->bp_category,
                    ],
                    'body_temp' => [
                        'value' => number_format((float) $report->body_temp, 1),
                        'unit' => '°C',
                        'category' => $report->temp_category,
                    ],
                    'pulse_rate' => [
                        'value' => $report->pulse_rate,
                        'unit' => 'bpm',
                        'category' => $report->pulse_category,
                    ],
                    'spo2' => [
                        'value' => $report->spo2,
                        'unit' => '%',
                        'category' => $report->spo2_category,
                    ],
                    'gds' => [
                        'value' => $report->gds ?: '-',
                        'unit' => 'mg/dL',
                        'category' => $report->gds_category,
                    ],
                ],

                // Catatan Klinis & Edukasi
                'clinical_notes' => $report->clinical_notes,
                'family_education' => $report->family_education ?: 'Tidak ada instruksi khusus.',

                // Foto Dokumentasi
                'photos' => $report->photos->map(fn($p) => [
                    'id' => $p->id,
                    'url' => $p->url,
                    'caption' => $p->caption,
                ]),
            ],
        ]);
    }

    /**
     * Simpan penilaian rating dan ulasan dari pasien untuk perawat.
     */
    public function storeReview(Request $request, Order $order)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($order->patient_id !== $user->id && !$user->hasRole('admin')) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk pesanan ini.');
        }

        if (!in_array($order->status, ['selesai', 'completed'])) {
            return back()->with('error', 'Penilaian hanya dapat diberikan setelah pesanan berstatus Selesai.');
        }

        if ($order->review) {
            return back()->with('error', 'Anda sudah pernah memberikan ulasan untuk pesanan ini.');
        }

        if (!$order->nurse_id) {
            return back()->with('error', 'Pesanan ini tidak terhubung dengan perawat yang bertugas.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Silakan pilih jumlah bintang rating (1 - 5).',
            'rating.between' => 'Rating harus bernilai antara 1 sampai 5 bintang.',
        ]);

        $review = Review::create([
            'order_id' => $order->id,
            'patient_id' => $user->id,
            'nurse_id' => $order->nurse_id,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        AuditLog::log('patient_review_submitted', $review, null, [
            'order_id' => $order->id,
            'nurse_id' => $order->nurse_id,
            'rating' => $review->rating,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Terima kasih! Ulasan dan rating Anda berhasil disimpan.',
                'review' => $review,
            ]);
        }

        return back()->with('status', 'Terima kasih atas ulasan Anda! Penilaian bintang berhasil diterbitkan untuk perawat.');
    }
}

