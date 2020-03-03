<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class FileTest.
 *
 * @author annejan@badge.team
 */
class FileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Assert the File has a relation with a single Project Version.
     */
    public function testFileVersionProjectRelationship(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create();
        $this->assertInstanceOf(Version::class, $file->version);
        $this->assertInstanceOf(Project::class, $file->version->project);
    }

    /**
     * Assert File extension helper work in a basic case.
     */
    public function testFileExtensionAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.txt']);
        $this->assertEquals('txt', $file->extension);
    }

    /**
     * Assert txt Files are flagged editable.
     */
    public function testFileEditableAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.txt']);
        $this->assertTrue($file->editable);
    }

    /**
     * Check the size of content helper.
     */
    public function testFileSizeOfContentAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['content' => 0]);
        $this->assertNull($file->size_of_content);
        $file = factory(File::class)->create(['content' => '123']);
        $this->assertEquals(3, $file->size_of_content);
    }

    /**
     * Assert py file mime type.
     */
    public function testFilePythonMimeAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.py']);
        $this->assertEquals('application/x-python-code', $file->mime);
    }

    /**
     * Assert png file mime type.
     */
    public function testFilePngMimeAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.png']);
        $this->assertEquals('image/png', $file->mime);
    }

    /**
     * Assert unknown (octet-stream) file mime type.
     */
    public function testFileUnknownMimeAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.unknown']);
        $this->assertEquals('application/octet-stream', $file->mime);
    }

    /**
     * Assert wav file mime type.
     */
    public function testFileWavMimeAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.wav']);
        $this->assertEquals('audio/wave', $file->mime);
    }

    /**
     * Assert bin (octet-stream) file mime type.
     */
    public function testFileBinMimeAttribute(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.bin']);
        $this->assertEquals('application/octet-stream', $file->mime);
    }

    /**
     * Assert png icon.
     */
    public function testFileIsInvalidIcon(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $file = factory(File::class)->create(['name' => 'test.bin']);
        $this->assertFalse($file->isValidIcon());
    }

    /**
     * Assert png icon.
     */
    public function testFileIsValidIcon(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var File $file */
        $file = factory(File::class)->create(['name' => 'icon.png']);
        $this->assertFalse($file->isValidIcon());
        $file->content = base64_decode( // 32x32 pixel PNG
            'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAFsUlEQVRYw9VXa1CUZRgFpd2lcjTR'.
            'H82oOSYTJt7FC3kjzTQVxzHSadLQH5lUTk2ijqKZloNmk4IXWC/gZUGEJPHCqqAkxM1Vue2yy010'.
            'Y1G5CCrYBO7pvN/3zZI1mabL5M6c2d3ve5/nnOd5zvvuty4uz+LrslYzt3CPJoPv2QlrVUG89BLh'.
            '1m4CSJ57I30mBNK3qq/xkp8iwvmvvF2aeRVH+uL+zb2w1x6A+Lz0PbdY3urv9C5khqs7GaM0tnvl'.
            '64F7RqC5AL9VfIvcne7NXj1dF3BJd8LVaQIKdms2VZ0eCzSedgjArWOoPjcZUctUBi4ZSbg7hTx/'.
            't8bTfKDz763VkW3kTZeAu7lovb4T5oMedn/fjt9w6StEB2dUr6+7EAjcySB5voMcd7KAhhOoNyzE'.
            'yVC1jUsnE52e9rbzF2az1x4ieR7qq3OQmpoKvV6Pq5YU4PZZ2Gv2yIac4xbDkH5PzZDF0RpV0V5N'.
            'RXPZV6w4h5UbkJN1FsnJyUhKSsLxpER24BS9cBTNZWuRvcO9qUd31w8Y2u2pGJKtXyUZ7xaJ7l4g'.
            'spF2Vi+RJyQkIDY2lgKO8/5PQM1uyZDblqgyGDqcUD9p63tYdB5NLbZtcvVi3nfSkXn+BOLj4xET'.
            'E4PjR3WyuPp4oFaHVtsWFO/3uO83uMMKpujxRF1g9XH1hgWs8JhMfjudOEcBidDpdIiOjkZK8j4H'.
            'OWr4+cYOGjIQietUFcoJ+eJ/PfHGScar2UvyTBKfl8wmzoAiwxFERUVBq9UiTc/7tQdJHg3c3AVY'.
            'l8J+9UvJkIv93bRM5fnYhqTxOhbt0RQ0lYSQ8AyJf5bJhdkaTuJKUTwiIyMRHh6O3LTINvJfVwHm'.
            'iUDxaDRbliEjzL2x8wuus5my6+MeOp8K49lFZYK8MUUhPyG5vdEah7CwMGzevBmX07eTXCuTl0wB'.
            'jENlVMyXDLlp0XNnmHIIoXokckOExoPGu9ViC5eJRQca9G1Or0/AvWqdRL5hwwZUGSnAGkzyyYBp'.
            'WJsA4zC0Wr9G0b6uLT5eHZYw9cuPZEhWr5WMVxcnn/kNydJ5L5PTbHUxnPl+7AgPRXBwMG6bPmLb'.
            'uU1Nw2UBfxZRMh0iV9walYWpxxLP/9u2G0rz3LeLeUrkJ0mcROIjD5CDp976kED4DHudhCMIHwXD'.
            '/ybEbl0uGTJoplsEKfo+1JA0XmaThcar/1EhPyqTi27UcZvdjACqVnK+7yNgSjdMGt2FhntDwai/'.
            'CFE6YRqLJssKGlL9cENy2wXaUvwgVS/mLSoWW/D6dzTYCqCSYymdKrfbPA6Ht3jj8FZvtvlN6TvM'.
            'Y2SYRspdMSoCCgYB5YEPN6TDeFXfsxJfh4mkSopHyNWZWaVlAsFtVk63l79DTFdAYSWTuGa84gfG'.
            'FDG+cAiQR5EX+9OQ6/7ZkA7jces4yAUk8tFyZaJS4fSKGQhdNgAMkxAwrRevvUsRM9iht7l2giy4'.
            'kPF5AwCDF5D1Kj+PlwwZE6IyPWBIGm9a1bmp1+2VQVQ9RMFQmdzMRBZB7sfkrPrKTI5iDvr06uQQ'.
            'IIBrC3lvLkX4M4ZCjRSdzxwXWX22J/BLbyC9J3MG4Ip+UgtPyG2M6yMZktUnNpeuYRBbXDiQGCQL'.
            'MPrI4xBtL2V7y1hhJSu9+iEiN45zkC//hOusQdJ1lM3i+rc4d8ZdGgzk9gMyWX0Gu3SeArI8Ibhi'.
            'Q1SljJ1CdHbhQ+ZdcdFe+THJR8ltyx8oz9A0Sm5pKZOWsbrKAIJE1kXcDZ8DtqV8/4LfF3MM80jO'.
            'NSZ24DI7YGAhOf0kUmT2oQh2wTxbEsCut5D8M+nxLXu7OtmWOhEtVWH84YlyKsTPuuDiA2w1yVcT'.
            'r7kcXKmaf2qj2sQfILQHDq1W1c4a0zGN5EHKAyzn4OLiq7QklPjByQhVuHwVbulo7EL0JryJwU6G'.
            't8LVpV3/T/5vX38A+s3uXtUCwqEAAAAASUVORK5CYII=');
        $this->assertTrue($file->isValidIcon());
        $file->content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGP6zwAAAgcBApocMXEA'.
            'AAAASUVORK5CYII=');    // file too low resolution :)
        $this->assertFalse($file->isValidIcon());
    }

    /**
     * Check if file can be viewed in page.
     */
    public function testFileIsViewable(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        /** @var File $file */
        $file = factory(File::class)->create(['name' => 'test.bin']);
        $this->assertNull($file->viewable);
        /** @var File $file */
        $file = factory(File::class)->create(['name' => 'test.mp3']);
        $this->assertEquals('audio', $file->viewable);
        /** @var File $file */
        $file = factory(File::class)->create(['name' => 'test.png']);
        $this->assertEquals('image', $file->viewable);
    }
}
