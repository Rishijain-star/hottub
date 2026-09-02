<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageCaptchaService
{
    private const SESSION_HASH_KEY = 'image_captcha_hash';

    private const SESSION_CODE_KEY = 'image_captcha_code';

    public function issue(Request $request): string
    {
        $this->issueCode($request);

        return route('captcha.image', ['t' => (string) now()->timestamp]);
    }

    public function verify(Request $request, mixed $input): bool
    {
        $stored = $request->session()->get(self::SESSION_HASH_KEY);
        if (! is_string($stored) || $stored === '') {
            return false;
        }

        $answer = preg_replace('/\D/', '', trim((string) $input)) ?? '';
        if (strlen($answer) !== 6) {
            return false;
        }

        $ok = hash_equals($stored, $this->hashAnswer($answer));
        if ($ok) {
            $request->session()->forget(self::SESSION_HASH_KEY);
            $request->session()->forget(self::SESSION_CODE_KEY);
        }

        return $ok;
    }

    public function peekCode(Request $request): ?string
    {
        $code = $request->session()->get(self::SESSION_CODE_KEY);

        return is_string($code) && preg_match('/^\d{6}$/', $code) ? $code : null;
    }

    public function renderPngResponse(Request $request): Response
    {
        if (! extension_loaded('gd')) {
            abort(503, 'Captcha unavailable.');
        }

        $this->ensureIssued($request);

        $code = $request->session()->get(self::SESSION_CODE_KEY);
        if (! is_string($code) || ! preg_match('/^\d{6}$/', $code)) {
            abort(404);
        }

        $width = 240;
        $height = 80;
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            abort(500);
        }

        $bg = imagecolorallocate($image, 248, 250, 252);
        $textColor = imagecolorallocate($image, 15, 118, 110);
        $noiseColor = imagecolorallocate($image, 148, 163, 184);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        for ($i = 0; $i < 8; $i++) {
            imageline(
                $image,
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $noiseColor
            );
        }

        for ($i = 0; $i < 120; $i++) {
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $noiseColor);
        }

        $chars = str_split($code);
        $x = 14;
        foreach ($chars as $char) {
            imagestring($image, 5, $x, 28, $char, $textColor);
            $x += 36;
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function ensureIssued(Request $request): void
    {
        $code = $request->session()->get(self::SESSION_CODE_KEY);
        if (is_string($code) && preg_match('/^\d{6}$/', $code)) {
            return;
        }

        $this->issueCode($request);
    }

    private function issueCode(Request $request): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $request->session()->put(self::SESSION_HASH_KEY, $this->hashAnswer($code));
        $request->session()->put(self::SESSION_CODE_KEY, $code);
    }

    private function hashAnswer(string $answer): string
    {
        return hash_hmac('sha256', $answer, (string) config('app.key'));
    }
}
