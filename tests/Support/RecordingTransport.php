<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace Tests\Support;

use Sentry\Event;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;

/**
 * A Sentry transport that keeps events instead of sending them.
 *
 * @author annejan@badge.team
 */
class RecordingTransport implements TransportInterface
{
    /** @var array<int, Event> */
    public array $events = [];

    /**
     * @param Event $event
     *
     * @return Result
     */
    public function send(Event $event): Result
    {
        $this->events[] = $event;

        return new Result(ResultStatus::success(), $event);
    }

    /**
     * @param int|null $timeout
     *
     * @return Result
     */
    public function close(?int $timeout = null): Result
    {
        return new Result(ResultStatus::success());
    }
}
