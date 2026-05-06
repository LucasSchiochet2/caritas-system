<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OpenApiController extends Controller
{
    public function index(): View
    {
        return view('docs.openapi');
    }

    public function json(): JsonResponse
    {
        return response()->json(config('openapi'));
    }
}
