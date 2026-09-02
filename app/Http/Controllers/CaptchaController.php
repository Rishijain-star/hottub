<?php

namespace App\Http\Controllers;

use App\Services\ImageCaptchaService;
use Illuminate\Http\Request;

class CaptchaController extends Controller
{
    public function image(Request $request, ImageCaptchaService $captcha)
    {
        return $captcha->renderPngResponse($request);
    }

    public function refresh(Request $request, ImageCaptchaService $captcha)
    {
        return response()->json([
            'url' => $captcha->issue($request),
        ]);
    }
}
