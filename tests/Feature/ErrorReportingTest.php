<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace Tests\Feature;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Sentry\ClientBuilder;
use Sentry\Options;
use Sentry\SentrySdk;
use Tests\Support\RecordingTransport;
use Tests\TestCase;

/**
 * Class ErrorReportingTest.
 *
 * Exceptions have to keep reaching Sentry. Nothing else in the test suite
 * notices if that stops working, and a site whose errors go nowhere is a site
 * whose bugs cannot be diagnosed, so this pins the wiring in bootstrap/app.php.
 *
 * @author annejan@badge.team
 */
class ErrorReportingTest extends TestCase
{
    /**
     * Point the SDK at a transport that keeps what it is given.
     *
     * @return RecordingTransport
     */
    private function recordingTransport(): RecordingTransport
    {
        $transport = new RecordingTransport();

        $client = (new ClientBuilder(new Options(['dsn' => 'https://key@example.com/1'])))
            ->setTransport($transport)
            ->getClient();

        SentrySdk::getCurrentHub()->bindClient($client);

        return $transport;
    }

    public function testReportedExceptionsReachSentry(): void
    {
        $transport = $this->recordingTransport();

        app(ExceptionHandler::class)->report(new \RuntimeException('reporting probe'));

        $this->assertCount(1, $transport->events);
        $exceptions = $transport->events[0]->getExceptions();
        $this->assertNotEmpty($exceptions);
        $this->assertEquals('reporting probe', $exceptions[0]->getValue());
    }

    /**
     * The handler skips the noisy ones, so a 404 or a failed validation does
     * not drown out the reports that matter.
     */
    public function testExpectedExceptionsAreNotReported(): void
    {
        $transport = $this->recordingTransport();

        app(ExceptionHandler::class)->report(new \Illuminate\Auth\AuthenticationException());
        app(ExceptionHandler::class)->report(
            new \Illuminate\Database\Eloquent\ModelNotFoundException()
        );

        $this->assertCount(0, $transport->events);
    }
}
