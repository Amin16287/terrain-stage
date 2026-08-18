<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Club;
use App\Entity\Player;
use App\Repository\TeamRepository;
use App\Repository\ClubRepository;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminTeamController extends AbstractController
{
    #[Route('/teams', name: 'app_admin_teams_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository, ClubRepository $clubRepository): Response
    {
        $teams = $teamRepository->createQueryBuilder('t')
            ->leftJoin('t.club', 'c')->addSelect('c')
            ->leftJoin('t.players', 'p')->addSelect('p')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();

        $clubs = $clubRepository->findBy([], ['name' => 'ASC']);

        return $this->render('admin/teams/index.html.twig', [
            'teams' => $teams,
            'clubs' => $clubs,
        ]);
    }

    #[Route('/teams/new', name: 'app_admin_teams_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ClubRepository $clubRepository): Response
    {
        $clubs = $clubRepository->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $team = new Team();

            $name = $request->request->get('name', '');
            $category = $request->request->get('category', '');
            $season = $request->request->get('season', date('Y') . '-' . (date('Y') + 1));
            $clubId = $request->request->get('club');

            $team->setName($name);
            $team->setCategory($category);
            $team->setSeason($season);

            if ($clubId) {
                $club = $clubRepository->find((int) $clubId);
                if ($club) {
                    $team->setClub($club);
                }
            }

            $em->persist($team);
            $em->flush();

            $this->addFlash('success', 'Équipe créée avec succès !');
            return $this->redirectToRoute('app_admin_teams_show', ['id' => $team->getId()]);
        }

        return $this->render('admin/teams/form.html.twig', [
            'clubs' => $clubs,
            'team' => null,
            'isNew' => true,
        ]);
    }

    #[Route('/teams/{id}/edit', name: 'app_admin_teams_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, TeamRepository $teamRepository, ClubRepository $clubRepository): Response
    {
        $team = $teamRepository->find($id);
        if (!$team) {
            throw $this->createNotFoundException('Équipe non trouvée');
        }

        $clubs = $clubRepository->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name', '');
            $category = $request->request->get('category', '');
            $season = $request->request->get('season', '');
            $clubId = $request->request->get('club');

            $team->setName($name);
            $team->setCategory($category);
            $team->setSeason($season);

            if ($clubId) {
                $club = $clubRepository->find((int) $clubId);
                if ($club) {
                    $team->setClub($club);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Équipe modifiée avec succès !');
            return $this->redirectToRoute('app_admin_teams_show', ['id' => $team->getId()]);
        }

        return $this->render('admin/teams/form.html.twig', [
            'clubs' => $clubs,
            'team' => $team,
            'isNew' => false,
        ]);
    }

    #[Route('/teams/{id}', name: 'app_admin_teams_show', methods: ['GET'])]
    public function show(int $id, TeamRepository $teamRepository, PlayerRepository $playerRepository): Response
    {
        $team = $teamRepository->createQueryBuilder('t')
            ->andWhere('t.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('t.club', 'c')->addSelect('c')
            ->getQuery()
            ->getOneOrNullResult();

        if (!$team) {
            throw $this->createNotFoundException('Équipe non trouvée');
        }

        $players = $playerRepository->findBy(
            ['team' => $team],
            ['lastName' => 'ASC', 'firstName' => 'ASC']
        );

        return $this->render('admin/teams/show.html.twig', [
            'team' => $team,
            'players' => $players,
        ]);
    }

    #[Route('/teams/{id}/delete', name: 'app_admin_teams_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em, TeamRepository $teamRepository): Response
    {
        $team = $teamRepository->find($id);
        if (!$team) {
            throw $this->createNotFoundException('Équipe non trouvée');
        }

        if (count($team->getHomeMatches()) > 0 || count($team->getAwayMatches()) > 0) {
            $this->addFlash('error', 'Impossible de supprimer : cette équipe est liée à des matchs.');
            return $this->redirectToRoute('app_admin_teams_index');
        }

        $em->remove($team);
        $em->flush();

        $this->addFlash('success', 'Équipe supprimée avec succès !');
        return $this->redirectToRoute('app_admin_teams_index');
    }

    #[Route('/players/new', name: 'app_admin_players_new', methods: ['GET', 'POST'])]
    public function newPlayer(Request $request, EntityManagerInterface $em, TeamRepository $teamRepository): Response
    {
        $teams = $teamRepository->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $player = new Player();

            $firstName = $request->request->get('firstName', '');
            $lastName = $request->request->get('lastName', '');
            $position = $request->request->get('position', '');
            $licenseNumber = $request->request->get('licenseNumber', '');
            $birthDateRaw = $request->request->get('birthDate');
            $teamId = $request->request->get('team');

            $player->setFirstName($firstName);
            $player->setLastName($lastName);
            $player->setPosition($position);
            $player->setLicenseNumber($licenseNumber);

            if ($birthDateRaw) {
                try {
                    $birthDate = new \DateTime($birthDateRaw);
                    $player->setBirthDate($birthDate);
                } catch (\Exception $e) {}
            }

            if ($teamId) {
                $team = $teamRepository->find((int) $teamId);
                if ($team) {
                    $player->setTeam($team);
                }
            }

            $em->persist($player);
            $em->flush();

            $this->addFlash('success', 'Joueur créé avec succès !');
            return $this->redirectToRoute('app_admin_teams_show', ['id' => $player->getTeam()?->getId() ?? 0]);
        }

        return $this->render('admin/players/form.html.twig', [
            'teams' => $teams,
            'player' => null,
            'isNew' => true,
        ]);
    }

    #[Route('/players/{id}/edit', name: 'app_admin_players_edit', methods: ['GET', 'POST'])]
    public function editPlayer(int $id, Request $request, EntityManagerInterface $em, PlayerRepository $playerRepository, TeamRepository $teamRepository): Response
    {
        $player = $playerRepository->find($id);
        if (!$player) {
            throw $this->createNotFoundException('Joueur non trouvé');
        }

        $teams = $teamRepository->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('firstName', '');
            $lastName = $request->request->get('lastName', '');
            $position = $request->request->get('position', '');
            $licenseNumber = $request->request->get('licenseNumber', '');
            $birthDateRaw = $request->request->get('birthDate');
            $teamId = $request->request->get('team');

            $player->setFirstName($firstName);
            $player->setLastName($lastName);
            $player->setPosition($position);
            $player->setLicenseNumber($licenseNumber);

            if ($birthDateRaw) {
                try {
                    $birthDate = new \DateTime($birthDateRaw);
                    $player->setBirthDate($birthDate);
                } catch (\Exception $e) {}
            }

            if ($teamId) {
                $team = $teamRepository->find((int) $teamId);
                if ($team) {
                    $player->setTeam($team);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Joueur modifié avec succès !');
            return $this->redirectToRoute('app_admin_teams_show', ['id' => $player->getTeam()?->getId() ?? 0]);
        }

        return $this->render('admin/players/form.html.twig', [
            'teams' => $teams,
            'player' => $player,
            'isNew' => false,
        ]);
    }

    #[Route('/players/{id}/delete', name: 'app_admin_players_delete', methods: ['POST'])]
    public function deletePlayer(int $id, EntityManagerInterface $em, PlayerRepository $playerRepository): Response
    {
        $player = $playerRepository->find($id);
        if (!$player) {
            throw $this->createNotFoundException('Joueur non trouvé');
        }

        $teamId = $player->getTeam()?->getId();

        if (count($player->getMatchEvents()) > 0) {
            $this->addFlash('error', 'Impossible de supprimer : ce joueur est lié à des événements de match.');
            return $this->redirectToRoute('app_admin_teams_show', ['id' => $teamId ?? 0]);
        }

        $em->remove($player);
        $em->flush();

        $this->addFlash('success', 'Joueur supprimé avec succès !');
        return $this->redirectToRoute('app_admin_teams_show', ['id' => $teamId ?? 0]);
    }
}
