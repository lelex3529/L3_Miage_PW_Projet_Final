<?php

namespace App\Controller;

use App\Repository\TuteurRepository;
use App\Repository\VisiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function index(Request $request, SessionInterface $session, TuteurRepository $tuteurRepository, VisiteRepository $visiteRepository): Response
    {
        $tuteurId = $session->get('tuteur_id');
        if (!$tuteurId) {
            $this->addFlash('error', 'Merci de vous connecter.');
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($tuteurId);
        if (!$tuteur) {
            $session->remove('tuteur_id');
            $this->addFlash('error', 'Session invalide. Connectez-vous à nouveau.');
            return $this->redirectToRoute('login');
        }

        $upcoming = $visiteRepository->findUpcomingForTuteur($tuteur, 5);

        return $this->render('dashboard/index.html.twig', [
            'tuteur' => $tuteur,
            'etudiants' => $tuteur->getEtudiants(),
            'visites' => $upcoming,
        ]);
    }
}
