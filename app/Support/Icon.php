<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Support;

use Illuminate\Support\Facades\Image;

/**
 * Badge icons.
 *
 * A badge expects icon.png to be exactly 32 by 32 pixels, so anything else is
 * scaled to fit and padded out with transparency rather than being rejected.
 *
 * @author annejan@badge.team
 */
class Icon
{
    /** The file name a badge looks for. */
    public const NAME = 'icon.png';

    /** Badge icons are square, this many pixels a side. */
    public const SIZE = 32;

    /**
     * Fully transparent padding.
     *
     * contain() fills the leftover space with this. It has to be given as a
     * colour with an alpha channel: the driver rejects the word "transparent",
     * and leaving it out pads with opaque black.
     */
    private const TRANSPARENT = '#00000000';

    /**
     * Is this file the icon a badge looks for?
     *
     * @param string $name
     *
     * @return bool
     */
    public static function isIconName(string $name): bool
    {
        return strtolower($name) === self::NAME;
    }

    /**
     * Read the dimensions of image data, or null when it cannot be decoded.
     *
     * @param string $data
     *
     * @return array{0: int, 1: int}|null
     */
    public static function dimensions(string $data): ?array
    {
        $decoded = self::decode($data);
        if ($decoded === null) {
            return null;
        }

        try {
            return Image::fromBytes($decoded)->dimensions();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Scale image data to a 32 by 32 PNG, keeping its aspect ratio and any
     * transparency it had.
     *
     * Returns null when the data is not an image, or is already exactly what
     * a badge wants, so callers can leave the stored bytes alone.
     *
     * @param string $data
     *
     * @return string|null
     */
    public static function normalise(string $data): ?string
    {
        $decoded = self::decode($data);
        if ($decoded === null) {
            return null;
        }

        $dimensions = self::dimensions($decoded);
        if ($dimensions === null) {
            return null;
        }

        if ($dimensions === [self::SIZE, self::SIZE] && self::isPng($decoded)) {
            return null;
        }

        try {
            return Image::fromBytes($decoded)
                ->contain(self::SIZE, self::SIZE, self::TRANSPARENT)
                ->toPng()
                ->toBytes();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * File content is normally raw bytes, but may also be stored base64
     * encoded. Return the raw bytes either way, or null if it is not an image.
     *
     * @param string $data
     *
     * @return string|null
     */
    private static function decode(string $data): ?string
    {
        if ($data === '') {
            return null;
        }

        if (self::looksLikeImage($data)) {
            return $data;
        }

        $decoded = base64_decode($data, true);
        if ($decoded !== false && self::looksLikeImage($decoded)) {
            return $decoded;
        }

        return null;
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    private static function looksLikeImage(string $data): bool
    {
        return self::isPng($data)
            || str_starts_with($data, "\xFF\xD8\xFF")           // jpeg
            || str_starts_with($data, 'GIF8')                   // gif
            || str_starts_with($data, 'BM');                    // bmp
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    private static function isPng(string $data): bool
    {
        return str_starts_with($data, "\x89PNG\r\n\x1A\n");
    }
}
