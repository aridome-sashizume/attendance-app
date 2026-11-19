<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;

class AttendanceApiController extends Controller
{
    //
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'API v1 ready',
        ]);
    }
}
