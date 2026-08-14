<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace Tests\Unit;

use App\Support\Icon;
use Tests\TestCase;

/**
 * Class IconTest.
 *
 * @author annejan@badge.team
 */
class IconTest extends TestCase
{
    /**
     * A PNG with an opaque red left half and a fully transparent right half.
     */
    private function png(int $width, int $height): string
    {
        $this->assertGreaterThan(0, $width);
        $this->assertGreaterThan(0, $height);
        $gd = imagecreatetruecolor(max(1, $width), max(1, $height));
        imagealphablending($gd, false);
        imagesavealpha($gd, true);
        imagefill($gd, 0, 0, (int) imagecolorallocatealpha($gd, 0, 0, 0, 127));
        imagefilledrectangle($gd, 0, 0, (int) ($width / 2) - 1, $height - 1, (int) imagecolorallocate($gd, 255, 0, 0));
        ob_start();
        imagepng($gd);

        return (string) ob_get_clean();
    }

    /**
     * @return array{0: int, 1: int, 2: int} width, height, alpha of a corner
     */
    private function inspect(string $png, int $x, int $y): array
    {
        $im = imagecreatefromstring($png);
        $this->assertNotFalse($im);

        return [imagesx($im), imagesy($im), (imagecolorat($im, $x, $y) >> 24) & 0x7F];
    }

    public function testNormaliseScalesToBadgeSize(): void
    {
        $resized = Icon::normalise($this->png(64, 24));

        $this->assertNotNull($resized);
        [$width, $height] = $this->inspect($resized, 0, 0);
        $this->assertEquals(Icon::SIZE, $width);
        $this->assertEquals(Icon::SIZE, $height);
    }

    public function testNormaliseKeepsTransparency(): void
    {
        $resized = Icon::normalise($this->png(64, 24));

        $this->assertNotNull($resized);
        // Top right was transparent in the source, and the padding added to
        // make the image square has to be transparent too.
        [, , $alpha] = $this->inspect($resized, Icon::SIZE - 1, 0);
        $this->assertEquals(127, $alpha);
    }

    public function testNormaliseDoesNotCropTheImage(): void
    {
        $resized = Icon::normalise($this->png(64, 24));

        $this->assertNotNull($resized);
        $im = imagecreatefromstring($resized);
        $this->assertNotFalse($im);
        // The red half survives rather than being cropped away.
        $this->assertGreaterThan(200, (imagecolorat($im, 2, (int) (Icon::SIZE / 2)) >> 16) & 0xFF);
    }

    public function testNormaliseLeavesACorrectIconAlone(): void
    {
        $this->assertNull(Icon::normalise($this->png(Icon::SIZE, Icon::SIZE)));
    }

    public function testNormaliseIgnoresThingsThatAreNotImages(): void
    {
        $this->assertNull(Icon::normalise('import time'));
        $this->assertNull(Icon::normalise(''));
    }

    public function testNormaliseHandlesBase64Content(): void
    {
        $resized = Icon::normalise(base64_encode($this->png(64, 24)));

        $this->assertNotNull($resized);
        [$width, $height] = $this->inspect($resized, 0, 0);
        $this->assertEquals(Icon::SIZE, $width);
        $this->assertEquals(Icon::SIZE, $height);
    }

    public function testDimensionsReadsRawAndBase64(): void
    {
        $png = $this->png(64, 24);

        $this->assertEquals([64, 24], Icon::dimensions($png));
        $this->assertEquals([64, 24], Icon::dimensions(base64_encode($png)));
        $this->assertNull(Icon::dimensions('not an image'));
    }

    public function testIsIconNameIsCaseInsensitive(): void
    {
        $this->assertTrue(Icon::isIconName('icon.png'));
        $this->assertTrue(Icon::isIconName('Icon.PNG'));
        $this->assertFalse(Icon::isIconName('icon.py'));
        $this->assertFalse(Icon::isIconName('splash.png'));
    }
}
