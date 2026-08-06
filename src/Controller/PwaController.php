<?php

namespace App\Controller;

use App\Repository\GameMatchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
