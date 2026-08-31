<?php

namespace App\Controller;

use App\Repository\GameMatchRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
}
