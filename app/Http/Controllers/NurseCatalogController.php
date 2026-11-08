<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\NurseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class NurseCatalogController extends Controller
{
    public function getNearbyNakes(Request $request): JsonResponse
    {
        return app(NurseApiController::class)->nearby($request);
    }

    public function nearby(Request $request): JsonResponse
    {
        return app(NurseApiController::class)->nearby($request);
    }
}

