<?php

namespace App\Controller;

use App\Entity\GameMatch;
use App\Entity\Team;
use App\Enum\MatchStatus;
use App\Repository\GameMatchRepository;
use App\Repository\TeamRepository;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminMatchController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard', methods: ['GET'])]
    public function dashboard(GameMatchRepository $gameMatchRepository, TeamRepository $teamRepository, ClubRepository $clubRepository): Response
    {
        $totalMatches = $gameMatchRepository->count([]);
        $liveMatches = $gameMatchRepository->count(['status' => MatchStatus::LIVE]);
        $scheduledMatches = $gameMatchRepository->count(['status' => MatchStatus::SCHEDULED]);
        $finishedMatches = $gameMatchRepository->count(['status' => MatchStatus::FINISHED]);
        $totalTeams = $teamRepository->count([]);
        $totalClubs = $clubRepository->count([]);

        $recentMatches = $gameMatchRepository->createQueryBuilder('m')
            ->leftJoin('m.homeTeam', 'ht')->addSelect('ht')
            ->leftJoin('m.awayTeam', 'at')->addSelect('at')
            ->orderBy('m.date', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $liveMatchesList = $gameMatchRepository->createQueryBuilder('m')
            ->leftJoin('m.homeTeam', 'ht')->addSelect('ht')
            ->leftJoin('m.awayTeam', 'at')->addSelect('at')
            ->andWhere('m.status = :status')
            ->setParameter('status', MatchStatus::LIVE)
            ->orderBy('m.date', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/dashboard.html.twig', [
            'totalMatches' => $totalMatches,
            'liveMatches' => $liveMatches,
            'scheduledMatches' => $scheduledMatches,
            'finishedMatches' => $finishedMatches,
            'totalTeams' => $totalTeams,
            'totalClubs' => $totalClubs,
            'recentMatches' => $recentMatches,
            'liveMatchesList' => $liveMatchesList,
        ]);
    }

    #[Route('/matches', name: 'app_admin_matches_index', methods: ['GET'])]
    public function index(Request $request, GameMatchRepository $gameMatchRepository, TeamRepository $teamRepository): Response
    {
        $status = $request->query->get('status', 'all');
        $teamId = $request->query->get('team', null);
        $dateFrom = $request->query->get('date_from', null);
        $dateTo = $request->query->get('date_to', null);

        $qb = $gameMatchRepository->createQueryBuilder('m')
            ->leftJoin('m.homeTeam', 'ht')->addSelect('ht')
            ->leftJoin('m.awayTeam', 'at')->addSelect('at');

        if ($status !== 'all') {
            $enumStatus = MatchStatus::tryFrom($status);
            if ($enumStatus) {
                $qb->andWhere('m.status = :status')->setParameter('status', $enumStatus);
            }
        }

        if ($teamId) {
            $qb->andWhere('ht.id = :teamId OR at.id = :teamId')->setParameter('teamId', (int) $teamId);
        }

        if ($dateFrom) {
            try {
                $dateFromObj = new \DateTimeImmutable($dateFrom);
                $qb->andWhere('m.date >= :dateFrom')->setParameter('dateFrom', $dateFromObj);
            } catch (\Exception $e) {}
        }

        if ($dateTo) {
            try {
                $dateToObj = new \DateTimeImmutable($dateTo . ' 23:59:59');
                $qb->andWhere('m.date <= :dateTo')->setParameter('dateTo', $dateToObj);
            } catch (\Exception $e) {}
        }

        $matches = $qb
            ->orderBy('m.date', 'DESC')
            ->getQuery()
            ->getResult();

        $teams = $teamRepository->findBy([], ['name' => 'ASC']);

        return $this->render('admin/matches/index.html.twig', [
            'matches' => $matches,
            'teams' => $teams,
            'filters' => [
                'status' => $status,
                'team' => $teamId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    #[Route('/matches/new', name: 'app_admin_matches_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, TeamRepository $teamRepository): Response
    {
        $teams = $teamRepository->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $match = new GameMatch();

            $homeTeamId = $request->request->get('homeTeam');
            $awayTeamId = $request->request->get('awayTeam');
            $dateRaw = $request->request->get('date');
            $time = $request->request->get('time', '15:00');
            $venue = $request->request->get('venue', '');
            $status = $request->request->get('status', 'scheduled');
            $scoreHome = (int) $request->request->get('scoreHome', 0);
            $scoreAwayRaw = $request->request->get('scoreAway');
            $scoreAway = $scoreAwayRaw !== '' && $scoreAwayRaw !== null ? (int) $scoreAwayRaw : null;
            $opponentName = $request->request->get('opponentName');

            if ($homeTeamId) {
                $homeTeam = $teamRepository->find((int) $homeTeamId);
                if ($homeTeam) {
                    $match->setHomeTeam($homeTeam);
                }
            }

            if ($awayTeamId) {
                $awayTeam = $teamRepository->find((int) $awayTeamId);
                if ($awayTeam) {
                    $match->setAwayTeam($awayTeam);
                }
            }

            if ($dateRaw) {
                try {
                    $date = new \DateTime($dateRaw . ' ' . $time);
                    $match->setDate($date);
                } catch (\Exception $e) {
                    $match->setDate(new \DateTime());
                }
            } else {
                $match->setDate(new \DateTime());
            }

            $match->setVenue($venue);

            $enumStatus = MatchStatus::tryFrom($status);
            if ($enumStatus) {
                $match->setStatus($enumStatus);
            } else {
                $match->setStatus(MatchStatus::SCHEDULED);
            }

            $match->setScoreHome($scoreHome);
            $match->setScoreAway($scoreAway);
            if ($opponentName !== '' && $opponentName !== null) {
                $match->setOpponentName($opponentName);
            }

            $em->persist($match);
            $em->flush();

            $this->addFlash('success', 'Match créé avec succès !');
            return $this->redirectToRoute('app_admin_matches_show', ['id' => $match->getId()]);
        }

        return $this->render('admin/matches/form.html.twig', [
            'teams' => $teams,
            'match' => null,
            'isNew' => true,
            'statuses' => MatchStatus::cases(),
        ]);
    }

    #[Route('/matches/{id}/edit', name: 'app_admin_matches_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, GameMatchRepository $gameMatchRepository, TeamRepository $teamRepository): Response
    {
        $match = $gameMatchRepository->find($id);
        if (!$match) {
            throw $this->createNotFoundException('Match non trouvé');
        }

        $teams = $teamRepository->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $homeTeamId = $request->request->get('homeTeam');
            $awayTeamId = $request->request->get('awayTeam');
            $dateRaw = $request->request->get('date');
            $time = $request->request->get('time', '15:00');
            $venue = $request->request->get('venue', '');
            $status = $request->request->get('status', 'scheduled');
            $scoreHome = (int) $request->request->get('scoreHome', 0);
            $scoreAwayRaw = $request->request->get('scoreAway');
            $scoreAway = $scoreAwayRaw !== '' && $scoreAwayRaw !== null ? (int) $scoreAwayRaw : null;
            $opponentName = $request->request->get('opponentName');

            if ($homeTeamId) {
                $homeTeam = $teamRepository->find((int) $homeTeamId);
                if ($homeTeam) {
                    $match->setHomeTeam($homeTeam);
                }
            }

            if ($awayTeamId) {
                $awayTeam = $teamRepository->find((int) $awayTeamId);
                if ($awayTeam) {
                    $match->setAwayTeam($awayTeam);
                }
            } else {
                $match->setAwayTeam(null);
            }

            if ($dateRaw) {
                try {
                    $date = new \DateTime($dateRaw . ' ' . $time);
                    $match->setDate($date);
                } catch (\Exception $e) {}
            }

            $match->setVenue($venue);

            $enumStatus = MatchStatus::tryFrom($status);
            if ($enumStatus) {
                $match->setStatus($enumStatus);
            }

            $match->setScoreHome($scoreHome);
            $match->setScoreAway($scoreAway);
            $match->setOpponentName($opponentName !== '' && $opponentName !== null ? $opponentName : null);

            $em->flush();

            $this->addFlash('success', 'Match modifié avec succès !');
            return $this->redirectToRoute('app_admin_matches_show', ['id' => $match->getId()]);
        }

        return $this->render('admin/matches/form.html.twig', [
            'teams' => $teams,
            'match' => $match,
            'isNew' => false,
            'statuses' => MatchStatus::cases(),
        ]);
    }

    #[Route('/matches/{id}', name: 'app_admin_matches_show', methods: ['GET'])]
    public function show(int $id, GameMatchRepository $gameMatchRepository): Response
    {
        $match = $gameMatchRepository->createQueryBuilder('m')
            ->andWhere('m.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('m.homeTeam', 'ht')->addSelect('ht')
            ->leftJoin('m.awayTeam', 'at')->addSelect('at')
            ->leftJoin('m.matchEvents', 'me')->addSelect('me')
            ->leftJoin('me.player', 'p')->addSelect('p')
            ->orderBy('me.minute', 'ASC')
            ->addOrderBy('me.createdAt', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();

        if (!$match) {
            throw $this->createNotFoundException('Match non trouvé');
        }

        return $this->render('admin/matches/show.html.twig', [
            'match' => $match,
        ]);
    }

    #[Route('/matches/{id}/delete', name: 'app_admin_matches_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em, GameMatchRepository $gameMatchRepository): Response
    {
        $match = $gameMatchRepository->find($id);
        if (!$match) {
            $this->addFlash('warning', 'Ce match a déjà été supprimé.');
            return $this->redirectToRoute('app_admin_matches_index');
        }

        $em->remove($match);
        $em->flush();

        $this->addFlash('success', 'Match supprimé avec succès !');
        return $this->redirectToRoute('app_admin_matches_index');
    }

    #[Route('/matches/{id}/status', name: 'app_admin_matches_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request, EntityManagerInterface $em, GameMatchRepository $gameMatchRepository): Response
    {
        $match = $gameMatchRepository->find($id);
        if (!$match) {
            return $this->json(['error' => 'Match non trouvé'], 404);
        }

        $status = $request->request->get('status') ?? ($request->toArray()['status'] ?? null);
        if ($status === null && $request->getContent()) {
            $data = json_decode($request->getContent(), true);
            $status = $data['status'] ?? null;
        }

        $enumStatus = MatchStatus::tryFrom($status);
        if (!$enumStatus) {
            return $this->json(['error' => 'Statut invalide'], 400);
        }

        $match->setStatus($enumStatus);
        $em->flush();

        if ($request->headers->get('Accept') === 'application/json') {
            return $this->json([
                'success' => true,
                'status' => $match->getStatus()->value,
            ]);
        }

        $this->addFlash('success', 'Statut mis à jour !');
        return $this->redirectToRoute('app_admin_matches_show', ['id' => $match->getId()]);
    }

    #[Route('/matches/{id}/score', name: 'app_admin_matches_update_score', methods: ['POST'])]
    public function updateScore(int $id, Request $request, EntityManagerInterface $em, GameMatchRepository $gameMatchRepository): Response
    {
        $match = $gameMatchRepository->find($id);
        if (!$match) {
            return $this->json(['error' => 'Match non trouvé'], 404);
        }

        $scoreHome = $request->request->get('scoreHome');
        $scoreAway = $request->request->get('scoreAway');

        if ($scoreHome === null && $request->getContent()) {
            $data = json_decode($request->getContent(), true);
            $scoreHome = $data['scoreHome'] ?? null;
            $scoreAway = $data['scoreAway'] ?? null;
        }

        if ($scoreHome !== null) {
            $match->setScoreHome((int) $scoreHome);
        }
        if ($scoreAway !== null && $scoreAway !== '') {
            $match->setScoreAway((int) $scoreAway);
        } else {
            $match->setScoreAway(null);
        }

        $em->flush();

        if ($request->headers->get('Accept') === 'application/json') {
            return $this->json([
                'success' => true,
                'scoreHome' => $match->getScoreHome(),
                'scoreAway' => $match->getScoreAway(),
            ]);
        }

        $this->addFlash('success', 'Score mis à jour !');
        return $this->redirectToRoute('app_admin_matches_show', ['id' => $match->getId()]);
    }
}