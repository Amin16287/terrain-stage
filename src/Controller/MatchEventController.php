<?php

namespace App\Controller;

use App\Entity\GameMatch;
use App\Entity\MatchEvent;
use App\Enum\EventType;
use App\Enum\SyncStatus;
use App\Repository\GameMatchRepository;
use App\Repository\MatchEventRepository;
use App\Repository\PlayerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class MatchEventController extends AbstractController
{
    #[Route('/match/{id}/saisie', name: 'app_match_saisie', methods: ['GET'])]
    public function saisie(
        int $id,
        GameMatchRepository $gameMatchRepository,
        PlayerRepository $playerRepository,
        MatchEventRepository $matchEventRepository
    ): Response {
        $match = $gameMatchRepository->createQueryBuilder('m')
            ->andWhere('m.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('m.homeTeam', 'ht')
            ->addSelect('ht')
            ->leftJoin('m.awayTeam', 'at')
            ->addSelect('at')
            ->getQuery()
            ->getOneOrNullResult();

        if (!$match) {
            throw $this->createNotFoundException('Match non trouvé');
        }

        $players = [];
        if ($match->getHomeTeam()) {
            $players = $playerRepository->findBy(
                ['team' => $match->getHomeTeam()],
                ['lastName' => 'ASC', 'firstName' => 'ASC']
            );
        }

        $events = $matchEventRepository->createQueryBuilder('e')
            ->andWhere('e.relatedMatch = :match')
            ->setParameter('match', $match)
            ->leftJoin('e.player', 'p')
            ->addSelect('p')
            ->orderBy('e.minute', 'DESC')
            ->addOrderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $goalsHome = 0;
        $goalsAway = 0;
        foreach ($events as $event) {
            if ($event->getType() === EventType::GOAL) {
                $goalsHome++;
            }
        }

        return $this->render('match/saisie.html.twig', [
            'match' => $match,
            'players' => $players,
            'events' => $events,
            'eventTypes' => EventType::cases(),
            'computedScoreHome' => $goalsHome,
            'computedScoreAway' => $match->getScoreAway() ?? 0,
        ]);
    }

    #[Route('/match/{id}/event/create', name: 'app_match_event_create', methods: ['POST'])]
    public function createEvent(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        GameMatchRepository $gameMatchRepository,
        PlayerRepository $playerRepository,
        UserRepository $userRepository
    ): JsonResponse {
        $match = $gameMatchRepository->find($id);
        if (!$match) {
            return $this->json(['error' => 'Match non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $type = $data['type'] ?? null;
        $minute = $data['minute'] ?? null;
        $playerId = $data['playerId'] ?? null;
        $zoneX = $data['zoneX'] ?? null;
        $zoneY = $data['zoneY'] ?? null;
        $comment = $data['comment'] ?? null;

        if (!$type || !$minute || !$playerId) {
            return $this->json(['error' => 'Champs requis manquants'], 400);
        }

        $enumType = EventType::tryFrom($type);
        if (!$enumType) {
            return $this->json(['error' => 'Type d\'événement invalide'], 400);
        }

        $player = $playerRepository->find($playerId);
        if (!$player) {
            return $this->json(['error' => 'Joueur non trouvé'], 404);
        }

        $user = $userRepository->findOneBy([]);
        if (!$user) {
            return $this->json(['error' => 'Aucun utilisateur trouvé. Veuillez créer un utilisateur test.'], 400);
        }

        $event = new MatchEvent();
        $event->setType($enumType);
        $event->setMinute((int) $minute);
        $event->setPlayer($player);
        $event->setRelatedMatch($match);
        $event->setTaggedBy($user);
        $event->setClientUuid(Uuid::v4()->toRfc4122());
        $event->setSyncStatus(SyncStatus::PENDING);

        if ($zoneX !== null) {
            $event->setZoneX((float) $zoneX);
        }
        if ($zoneY !== null) {
            $event->setZoneY((float) $zoneY);
        }
        if ($comment !== null) {
            $event->setComment($comment);
        }

        if ($enumType === EventType::GOAL) {
            $match->setScoreHome($match->getScoreHome() + 1);
        }

        $em->persist($event);
        $em->flush();

        return $this->json([
            'success' => true,
            'event' => [
                'id' => $event->getId(),
                'type' => $event->getType()->value,
                'minute' => $event->getMinute(),
                'playerName' => $player->getFirstName() . ' ' . strtoupper($player->getLastName()),
                'playerPosition' => $player->getPosition(),
                'scoreHome' => $match->getScoreHome(),
                'scoreAway' => $match->getScoreAway() ?? 0,
            ],
        ]);
    }

    #[Route('/match/{matchId}/event/{eventId}/delete', name: 'app_match_event_delete', methods: ['POST'])]
    public function deleteEvent(
        int $matchId,
        int $eventId,
        EntityManagerInterface $em,
        MatchEventRepository $matchEventRepository,
        GameMatchRepository $gameMatchRepository
    ): JsonResponse {
        $event = $matchEventRepository->find($eventId);
        if (!$event) {
            return $this->json(['error' => 'Événement non trouvé'], 404);
        }

        $match = $gameMatchRepository->find($matchId);
        if (!$match) {
            return $this->json(['error' => 'Match non trouvé'], 404);
        }

        if ($event->getType() === EventType::GOAL && $match->getScoreHome() > 0) {
            $match->setScoreHome($match->getScoreHome() - 1);
        }

        $em->remove($event);
        $em->flush();

        return $this->json([
            'success' => true,
            'scoreHome' => $match->getScoreHome(),
            'scoreAway' => $match->getScoreAway() ?? 0,
        ]);
    }

    #[Route('/match/{id}/status', name: 'app_match_update_status', methods: ['POST'])]
    public function updateStatus(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        GameMatchRepository $gameMatchRepository
    ): JsonResponse {
        $match = $gameMatchRepository->find($id);
        if (!$match) {
            return $this->json(['error' => 'Match non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? null;

        $enumStatus = null;
        if ($status) {
            $enumStatus = \App\Enum\MatchStatus::tryFrom($status);
        }

        if (!$enumStatus) {
            return $this->json(['error' => 'Statut invalide'], 400);
        }

        $match->setStatus($enumStatus);
        $em->flush();

        return $this->json([
            'success' => true,
            'status' => $match->getStatus()->value,
        ]);
    }
}
