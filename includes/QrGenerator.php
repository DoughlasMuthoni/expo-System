<?php

declare(strict_types=1);

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Generates and removes the per-expo QR code PNG under public/qr/.
 * One expo = one slug = one QR code (CLAUDE.md Core Business Rules).
 */
class QrGenerator
{
    private const OUTPUT_DIR = __DIR__ . '/../public/qr';

    public static function generate(string $slug, string $targetUrl): string
    {
        if (!is_dir(self::OUTPUT_DIR) && !mkdir(self::OUTPUT_DIR, 0755, true) && !is_dir(self::OUTPUT_DIR)) {
            throw new RuntimeException('Could not create QR output directory.');
        }

        $options = new QROptions([
            'outputInterface'  => QRGdImagePNG::class,
            'eccLevel'         => EccLevel::M,
            'scale'            => 8,
            'imageTransparent' => false,
        ]);

        (new QRCode($options))->render($targetUrl, self::filePath($slug));

        return self::publicPath($slug);
    }

    public static function delete(string $slug): void
    {
        $file = self::filePath($slug);

        if (is_file($file)) {
            unlink($file);
        }
    }

    public static function publicPath(string $slug): string
    {
        return '/qr/' . rawurlencode($slug) . '.png';
    }

    public static function exists(string $slug): bool
    {
        return is_file(self::filePath($slug));
    }

    private static function filePath(string $slug): string
    {
        return self::OUTPUT_DIR . '/' . $slug . '.png';
    }
}
