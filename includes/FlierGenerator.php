<?php

declare(strict_types=1);

/**
 * Renders a print-ready A4 flier (PNG) per expo: logo, expo name/location,
 * the expo's own QR code, a call-to-action, and a branded footer. Built with
 * GD (already a dependency of QrGenerator/chillerlan) rather than adding a
 * PDF library — a raster PNG at 150 DPI prints cleanly for a booth handout
 * and keeps the footprint small, per CLAUDE.md's tech stack rule.
 */
class FlierGenerator
{
    // A4 @ 150 DPI
    private const WIDTH = 1240;
    private const HEIGHT = 1754;

    private const FONT_BOLD     = __DIR__ . '/fonts/Poppins-Bold.ttf';
    private const FONT_SEMIBOLD = __DIR__ . '/fonts/Poppins-SemiBold.ttf';
    private const FONT_REGULAR  = __DIR__ . '/fonts/Poppins-Regular.ttf';

    private const NAVY   = [0x20, 0x1b, 0x5c];
    private const BLUE   = [0x2f, 0xa5, 0xdf];
    private const GRAY   = [0x6c, 0x75, 0x7d];
    private const WHITE  = [0xff, 0xff, 0xff];

    public static function render(array $expo): string
    {
        $logoPath = __DIR__ . '/../public/assets/img/waterlift-logo.jpeg';
        $qrPath   = __DIR__ . '/../public/qr/' . $expo['slug'] . '.png';

        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $white  = self::allocate($canvas, self::WHITE);
        $navy   = self::allocate($canvas, self::NAVY);
        $blue   = self::allocate($canvas, self::BLUE);
        $gray   = self::allocate($canvas, self::GRAY);
        imagefill($canvas, 0, 0, $white);

        $centerX = (int) (self::WIDTH / 2);
        $contentWidth = self::WIDTH - 160;
        $y = 90;

        // Logo
        if (is_file($logoPath)) {
            $logo = imagecreatefromjpeg($logoPath);
            $logoW = imagesx($logo);
            $logoH = imagesy($logo);
            $targetW = 460;
            $targetH = (int) round($logoH * ($targetW / $logoW));
            imagecopyresampled($canvas, $logo, $centerX - (int) ($targetW / 2), $y, 0, 0, $targetW, $targetH, $logoW, $logoH);
            $y += $targetH + 60;
        }

        // Expo name (wraps to multiple lines if long)
        $nameLines = self::wrapText(strtoupper($expo['name']), self::FONT_BOLD, 40, $contentWidth);
        foreach ($nameLines as $line) {
            $y += 52;
            self::drawCentered($canvas, $line, self::FONT_BOLD, 40, $navy, $centerX, $y);
        }
        $y += 20;

        // Location / dates
        $subLine = $expo['location'] ?? '';
        if (!empty($expo['start_date']) || !empty($expo['end_date'])) {
            $dateRange = trim(($expo['start_date'] ?? '') . ' - ' . ($expo['end_date'] ?? ''), ' -');
            $subLine = $subLine !== '' ? $subLine . '   |   ' . $dateRange : $dateRange;
        }
        if ($subLine !== '') {
            $y += 44;
            self::drawCentered($canvas, $subLine, self::FONT_REGULAR, 24, $gray, $centerX, $y);
        }

        // QR code, boxed
        $y += 70;
        $qrBoxSize = 640;
        imagefilledrectangle(
            $canvas,
            $centerX - (int) ($qrBoxSize / 2) - 20,
            $y,
            $centerX + (int) ($qrBoxSize / 2) + 20,
            $y + $qrBoxSize + 40,
            self::allocate($canvas, [0xe5, 0xe3, 0xf0])
        );
        imagefilledrectangle(
            $canvas,
            $centerX - (int) ($qrBoxSize / 2) - 16,
            $y + 4,
            $centerX + (int) ($qrBoxSize / 2) + 16,
            $y + $qrBoxSize + 36,
            $white
        );
        if (is_file($qrPath)) {
            $qr = imagecreatefrompng($qrPath);
            imagecopyresampled($canvas, $qr, $centerX - (int) ($qrBoxSize / 2), $y + 20, 0, 0, $qrBoxSize, $qrBoxSize, imagesx($qr), imagesy($qr));
        }
        $y += $qrBoxSize + 90;

        // Call to action
        self::drawCentered($canvas, 'Scan the QR Code to Get in Touch', self::FONT_SEMIBOLD, 34, $blue, $centerX, $y);

        // Footer band
        $footerHeight = 160;
        $footerTop = self::HEIGHT - $footerHeight;
        imagefilledrectangle($canvas, 0, $footerTop, self::WIDTH, self::HEIGHT, $navy);
        self::drawCentered($canvas, 'Reliable Solar  |  Reliable Boreholes', self::FONT_SEMIBOLD, 28, $white, $centerX, $footerTop + 65);
        self::drawCentered($canvas, 'info@waterlift.co.ke   |   0768 117 070', self::FONT_REGULAR, 24, $white, $centerX, $footerTop + 110);

        ob_start();
        imagepng($canvas);
        $bytes = ob_get_clean();

        return $bytes;
    }

    private static function allocate($canvas, array $rgb): int
    {
        return imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);
    }

    /** imagettftext()'s $y is the text baseline, not the top. */
    private static function drawCentered($canvas, string $text, string $font, float $size, int $color, int $centerX, int $y): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $width = abs($box[4] - $box[0]);
        imagettftext($canvas, $size, 0, (int) ($centerX - $width / 2), $y, $color, $font, $text);
    }

    private static function wrapText(string $text, string $font, float $size, int $maxWidth): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $test = $current === '' ? $word : $current . ' ' . $word;
            $box = imagettfbbox($size, 0, $font, $test);
            $width = abs($box[4] - $box[0]);

            if ($width > $maxWidth && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $test;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }
}
