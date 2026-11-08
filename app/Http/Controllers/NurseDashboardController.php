<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NurseDashboardController extends Controller
{
    public function index()
    {
        return app(\App\Http\Controllers\Nurse\NurseDashboardController::class)->index();
    }

    public function toggleOnline(Request $request)
    {
        return app(\App\Http\Controllers\Nurse\NurseDashboardController::class)->toggleOnline($request);
    }

    public function updateLocation(Request $request)
    {
        return app(\App\Http\Controllers\Nurse\NurseDashboardController::class)->updateLocation($request);
    }

    public function updateServices(Request $request)
    {
        return app(\App\Http\Controllers\Nurse\NurseDashboardController::class)->updateServices($request);
    }

    public function requestPayout(Request $request)
    {
        return app(\App\Http\Controllers\Nurse\NurseDashboardController::class)->requestPayout($request);
    }
}
