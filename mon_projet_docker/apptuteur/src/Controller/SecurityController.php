<?php

namespace App\Controller;

use App\Form\LoginType;
use App\Repository\TuteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(Request $request, SessionInterface $session, TuteurRepository $tuteurRepository): Response
    {
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $tuteur = $tuteurRepository->findOneBy(['email' => $email]);

            if ($tuteur) {
                $session->set('tuteur_id', $tuteur->getId());
                $this->addFlash('success', 'Connexion réussie.');

                return $this->redirectToRoute('dashboard');
            }

            $this->addFlash('error', 'Aucun tuteur trouvé avec cet email.');
        }

        return $this->render('security/login.html.twig', [
            'loginForm' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(SessionInterface $session): Response
    {
        $session->remove('tuteur_id');
        $this->addFlash('success', 'Déconnexion effectuée.');

        return $this->redirectToRoute('login');
    }
}
