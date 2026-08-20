<?php

namespace App\Controller;

use App\Enum\MatchStatus;
use App\Repository\GameMatchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MatchSelectionController extends AbstractController
{
    #[Route('/match/selection', name: 'app_match_selection', methods: ['GET'])]
    public function __invoke(GameMatchRepository $gameMatchRepository): Response
    {
        $today = new \DateTimeImmutable();
        $startOfDay = $today->setTime(0, 0, 0);
        $endOfDay = $today->setTime(23, 59, 59);
        $startOfWeek = $today->modify('monday this week')->setTime(0, 0, 0);
        $endOfWeek = $today->modify('sunday this week')->setTime(23, 59, 59);

        $todayMatches = $gameMatchRepository->createQueryBuilder('m')
            ->leftJoin('m.homeTeam', 'ht')
            ->addSelect('ht')
            ->leftJoin('m.awayTeam', 'at')
            ->addSelect('at')
            ->andWhere('m.date >= :startOfDay AND m.date <= :endOfDay')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setParameter('liveStatus', MatchStatus::LIVE)
            ->orderBy('CASE WHEN m.status = :liveStatus THEN 0 ELSE 1 END')
            ->addOrderBy('m.date', 'ASC')
            ->getQuery()
            ->getResult();

        $weekMatches = $gameMatchRepository->createQueryBuilder('m')
            ->leftJoin('m.homeTeam', 'ht')
            ->addSelect('ht')
            ->leftJoin('m.awayTeam', 'at')
            ->addSelect('at')
            ->andWhere('m.date >= :startOfWeek AND m.date <= :endOfWeek')
            ->andWhere('m.date < :startOfDay OR m.date > :endOfDay')
            ->setParameter('startOfWeek', $startOfWeek)
            ->setParameter('endOfWeek', $endOfWeek)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->orderBy('m.date', 'ASC')
            ->getQuery()
            ->getResult();

        $finishedMatches = $gameMatchRepository->createQueryBuilder('m')
            ->leftJoin('m.homeTeam', 'ht')
            ->addSelect('ht')
            ->leftJoin('m.awayTeam', 'at')
            ->addSelect('at')
            ->andWhere('m.status = :finishedStatus')
            ->setParameter('finishedStatus', MatchStatus::FINISHED)
            ->orderBy('m.date', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('match/selection.html.twig', [
            'todayMatches' => $todayMatches,
            'weekMatches' => $weekMatches,
            'finishedMatches' => $finishedMatches,
        ]);
    }
}