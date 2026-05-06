<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpenApiController extends Controller
{
    public function index(): View
    {
        return view('docs.openapi');
    }

    public function json(Request $request): JsonResponse
    {
        $document = config('openapi');
        $document['servers'][0]['url'] = rtrim($request->getSchemeAndHttpHost(), '/').'/api';

        return response()->json($document);
    }
}
