<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\TestMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class TestMessageHandler
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(TestMessage $message): void
    {
        $this->logger->info('[Messenger] TestMessage traité', [
            'content' => $message->content,
            'dispatched_at' => $message->dispatchedAt->format(\DateTimeInterface::ATOM),
            'handled_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'pid' => getmypid(),
        ]);
    }
}
