<?php

declare(strict_types=1);

namespace App\Controller;

use App\Message\TestMessage;
use App\Repository\GameMatchRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PwaController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function __invoke(GameMatchRepository $gameMatchRepository): Response
    {
        $totalMatches = $gameMatchRepository->count([]);

        return $this->render('pwa/home.html.twig', [
            'totalMatches' => $totalMatches,
        ]);
    }

    #[Route('/healthz', name: 'app_healthz', methods: ['GET', 'HEAD'])]
    public function healthz(Connection $connection): JsonResponse
    {
        $dbOk = true;
        $dbMessage = 'ok';
        try {
            $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL());
        } catch (Exception | \Throwable $e) {
            $dbOk = false;
            $dbMessage = $e->getMessage();
        }

        $payload = [
            'status' => $dbOk ? 'ok' : 'degraded',
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'checks' => [
                'app' => 'ok',
                'db' => $dbOk ? 'ok' : 'error',
                'db_message' => $dbMessage,
            ],
        ];

        $code = $dbOk ? 200 : 503;
        $response = new JsonResponse($payload, $code);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('X-Health-Check', '1');
        return $response;
    }

    #[Route('/test/messenger/dispatch', name: 'app_test_messenger_dispatch', methods: ['GET'])]
    public function testMessengerDispatch(MessageBusInterface $bus, Request $request): JsonResponse
    {
        $content = $request->query->get('message', 'Hello Messenger at ' . (new \DateTimeImmutable())->format('H:i:s'));
        $bus->dispatch(new TestMessage($content));

        return new JsonResponse([
            'status' => 'dispatched',
            'content' => $content,
            'transport' => 'async (doctrine)',
            'hint' => 'Lance `php bin/console messenger:consume async` pour traiter ce message.',
        ]);
    }

    #[Route('/test/mercure/publish', name: 'app_test_mercure_publish', methods: ['GET'])]
    public function testMercurePublish(HubInterface $hub, Request $request): JsonResponse
    {
        $topic = $request->query->get('topic', 'https://example.com/test-topic');
        $data = $request->query->get('data', 'Hello Mercure at ' . (new \DateTimeImmutable())->format('H:i:s'));

        $update = new Update(
            topics: $topic,
            data: json_encode(['message' => $data, 'serverTime' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)], JSON_THROW_ON_ERROR),
        );

        $hub->publish($update);

        return new JsonResponse([
            'status' => 'published',
            'topic' => $topic,
            'data' => $data,
            'hint' => 'Ouvre /test/mercure/subscribe dans un onglet pour voir les messages arriver en temps réel.',
        ]);
    }

    #[Route('/test/mercure/subscribe', name: 'app_test_mercure_subscribe', methods: ['GET'])]
    public function testMercureSubscribe(): Response
    {
        return $this->render('pwa/test_mercure_subscribe.html.twig');
    }
}
