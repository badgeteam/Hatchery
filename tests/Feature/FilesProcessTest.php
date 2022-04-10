<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\ProjectUpdated;
use App\Models\Badge;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class FilesProcessTest.
 *
 * @author annejan@badge.team
 */
class FilesProcessTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdateLintWarning(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $data = 'import time';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertRedirect('/files/' . $file->id . '/edit')->assertSessionHas('warnings');
    }

    /**
     * Check the files can be stored.
     */
    public function testFilesUpdateLintError(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create();
        $data = 'imprt time';
        $response = $this
            ->actingAs($user)
            ->call('put', '/files/' . $file->id, ['file_content' => $data]);
        $response->assertRedirect('/files/' . $file->id . '/edit')->assertSessionHasErrors();
    }

    /**
     * Check the files can be linted.
     *
     * @throws \JsonException
     */
    public function testFilesLintSuccess(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.json']);
        $data = json_encode(['tests' => ['test1', 'test2']], JSON_THROW_ON_ERROR);
        Event::fake();
        $response = $this
            ->actingAs($user)
            ->json('post', '/lint-content/' . $file->id, ['file_content' => $data]);
        $response->assertStatus(200)->assertExactJson(['linting' => 'started']);
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertNotEquals($data, $file->content);
        Event::assertDispatched(ProjectUpdated::class, function ($e) {
            $this->assertEquals('success', $e->type);

            return true;
        });
    }

    /**
     * Check the files can be linted.
     */
    public function testFilesLintWarning(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.py']);
        $data = 'import neopixel';
        Event::fake();
        $response = $this
            ->actingAs($user)
            ->json('post', '/lint-content/' . $file->id, ['file_content' => $data]);
        $response->assertStatus(200)->assertExactJson(['linting' => 'started']);
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertNotEquals($data, $file->content);
        Event::assertDispatched(ProjectUpdated::class, function ($e) {
            $this->assertEquals('warning', $e->type);

            return true;
        });
    }

    /**
     * Check the files can be linted.
     */
    public function testFilesLintDanger(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.py']);
        $data = 'improt system';
        Event::fake();
        $response = $this
            ->actingAs($user)
            ->json('post', '/lint-content/' . $file->id, ['file_content' => $data]);
        $response->assertStatus(200)->assertExactJson(['linting' => 'started']);
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertNotEquals($data, $file->content);
        Event::assertDispatched(ProjectUpdated::class, function ($e) {
            $this->assertEquals('danger', $e->type);

            return true;
        });
    }

    /**
     * Check the files can't be linted.
     */
    public function testFilesLintInfo(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.txt']);
        $data = 'This is a file';
        Event::fake();
        $response = $this
            ->actingAs($user)
            ->json('post', '/lint-content/' . $file->id, ['file_content' => $data]);
        $response->assertStatus(200)->assertExactJson(['linting' => 'started']);
        /** @var File $file */
        $file = File::find($file->id);
        $this->assertNotEquals($data, $file->content);
        Event::assertDispatched(ProjectUpdated::class, function ($e) use ($file) {
            $this->assertEquals('info', $e->type);
            $this->assertEquals('File ' . $file->name . ' currently not lintable.', $e->message);

            return true;
        });
    }

    /**
     * Check the files can't be processed.
     */
    public function testFilesProcessInfo(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.txt']);
        Event::fake();
        $response = $this
            ->actingAs($user)
            ->json('post', '/process-file/' . $file->id);
        $response->assertStatus(200)->assertExactJson(['processing' => 'started']);
        /** @var File $file */
        $file = File::find($file->id);
        Event::assertDispatched(ProjectUpdated::class, function ($e) use ($file) {
            $this->assertEquals('info', $e->type);
            $this->assertEquals('File ' . $file->name . ' currently not processable.', $e->message);

            return true;
        });
    }

    /**
     * Check the files can't be processed.
     */
    public function testFilesProcessNoCommands(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.v', 'content' => '`default_nettype none
module chip (
  output  O_LED_R
  );
  wire  w_led_r;
  assign w_led_r = 1\'b0;
  assign O_LED_R = w_led_r;
endmodule']);
        $files = File::count();
        Event::fake();
        $response = $this
            ->actingAs($user)
            ->json('post', '/process-file/' . $file->id);
        $response->assertStatus(200)->assertExactJson(['processing' => 'started']);
        /** @var File $file */
        $file = File::find($file->id);
        Event::assertDispatched(ProjectUpdated::class, function ($e) use ($file) {
            $this->assertEquals('danger', $e->type);
            $this->assertEquals('No badges with workable commands for project: ' . $file->version->project->name, $e->message);

            return true;
        });
        $this->assertCount($files, File::all());
    }

    /**
     * Check the files can't be processed.
     */
    public function testFilesProcessNoConstraints(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.v', 'content' => '`default_nettype none
module chip (
  output  O_LED_R
  );
  wire  w_led_r;
  assign w_led_r = 1\'b0;
  assign O_LED_R = w_led_r;
endmodule']);
        $files = File::count();
        /** @var Badge $badge */
        $badge = Badge::factory()->create([
            'commands' => 'echo VDL > OUT',
        ]);
        $file->version->project->badges()->attach($badge);
        Event::fake();
        $response = $this
            ->actingAs($user)
            ->json('post', '/process-file/' . $file->id);
        $response->assertStatus(200)->assertExactJson(['processing' => 'started']);
        $i = 0;
        Event::assertDispatched(ProjectUpdated::class, function ($e) use ($badge, &$i) {
            if ($i === 0) {
                $this->assertEquals('warning', $e->type);
                $this->assertEquals('No constraints for badge: ' . $badge->name, $e->message);
            }
            $i++;

            return true;
        });
        $this->assertCount($files, File::all());
    }

    /**
     * Check the files can be processed.
     */
    public function testFilesProcessSuccess(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.v', 'content' => '`default_nettype none
module chip (
  output  O_LED_R
  );
  wire  w_led_r;
  assign w_led_r = 1\'b0;
  assign O_LED_R = w_led_r;
endmodule']);
        /** @var Badge $badge */
        $badge = Badge::factory()->create([
            'constraints' => 'set_io O_LED_R	39',
            'commands'    => 'yosys -q -p "read_verilog -noautowire VDL ; check ; clean ; synth_ice40 -blif VDL.blif"
# arachne-pnr -d 5k -P sg48 -p PCF VDL.blif -o VDL.txt
arachne-pnr -p PCF VDL.blif -o VDL.txt
icepack VDL.txt OUT',
        ]);
        $file->version->project->badges()->attach($badge);
        $files = File::count();
        Event::fake();
        $response = $this
            ->actingAs($user)
            ->json('post', '/process-file/' . $file->id);
        $response->assertStatus(200)->assertExactJson(['processing' => 'started']);
        /** @var File $generated */
        $generated = File::get()->last();
        Event::assertDispatched(ProjectUpdated::class, function ($e) use ($generated) {
            $this->assertEquals('success', $e->type);
            $this->assertEquals('File ' . $generated->name . ' generated.', $e->message);

            return true;
        });
        $this->assertCount($files + 1, File::all());
        $this->assertEquals($file->baseName . '_' . $badge->slug . '.bin', $generated->name);
        $this->assertGreaterThan(32200, strlen($generated->content));
        $this->assertLessThan(32300, strlen($generated->content));
    }

    /**
     * Check the files can't be processed.
     */
    public function testFilesProcessError(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->be($user);
        /** @var File $file */
        $file = File::factory()->create(['name' => 'test.v', 'content' => '`default_nettype none
module chip (
  output  O_LED_R
  );
  wire  w_led_r;
  assign w_led_r = 1\'b0;
  assign O_LED_R = w_led_r;
endmodule']);
        /** @var Badge $badge */
        $badge = Badge::factory()->create([
            'constraints' => 'set_io O_LED_R	39',
            'commands'    => 'echo lol && some typo',
        ]);
        $file->version->project->badges()->attach($badge);
        $files = File::count();
        Event::fake();
        $response = $this
            ->actingAs($user)
            ->json('post', '/process-file/' . $file->id);
        $response->assertStatus(200)->assertExactJson(['processing' => 'started']);
        $i = 0;
        Event::assertDispatched(ProjectUpdated::class, function ($e) use (&$i) {
            if ($i === 0) {
                $this->assertEquals('danger', $e->type);
            }
            if ($i === 1) {
                $this->assertEquals('warning', $e->type);
                $this->assertEquals("lol\n", $e->message);
            }
            $i++;

            return true;
        });
        $this->assertCount($files, File::all());
    }
}
