<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    /**
     * [SCRUM-60] Manajemen Pengguna: Daftar Akun, Filter Role & Status.
     */
    public function index(Request $request): View
    {
        $query = User::with(['roles', 'nurseProfile', 'wallet'])->latest();

        if ($request->filled('role')) {
            $roleName = (string) $request->role;
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Toggle status aktif / penangguhan akun pengguna.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        // Admin tidak boleh menonaktifkan akunnya sendiri
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menangguhkan akun Anda sendiri!');
        }

        $request->validate([
            'status' => 'required|in:active,suspended',
            'suspended_reason' => 'required_if:status,suspended|nullable|string|max:500',
        ]);

        $oldStatus = $user->status;
        $newStatus = $request->status;

        DB::transaction(function () use ($user, $newStatus, $oldStatus, $request) {
            if ($newStatus === 'suspended') {
                $user->update([
                    'status' => 'suspended',
                    'suspended_reason' => $request->suspended_reason,
                    'suspended_at' => now(),
                ]);

                // Jika user adalah perawat, otomatis set is_online = false
                if ($user->nurseProfile) {
                    $user->nurseProfile->update(['is_online' => false]);
                }
            } else {
                $user->update([
                    'status' => 'active',
                    'suspended_reason' => null,
                    'suspended_at' => null,
                ]);
            }

            AuditLog::log(
                'update_user_status',
                $user,
                ['status' => $oldStatus],
                ['status' => $newStatus, 'reason' => $request->suspended_reason]
            );
        });

        $message = $newStatus === 'suspended' 
            ? "Akun {$user->name} berhasil ditangguhkan (Suspended)." 
            : "Akun {$user->name} berhasil diaktifkan kembali.";

        return back()->with('success', $message);
    }
}
