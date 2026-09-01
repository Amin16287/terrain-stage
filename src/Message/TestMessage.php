<?php

declare(strict_types=1);

namespace App\Message;

final readonly class TestMessage
{
    public function __construct(
        public string $content,
        public \DateTimeImmutable $dispatchedAt = new \DateTimeImmutable(),
    ) {
    }
}
