<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Form\EtudiantType;
use App\Repository\EtudiantRepository;
use App\Repository\TuteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class EtudiantController extends AbstractController
{
    #[Route('/etudiants', name: 'etudiants_index', methods: ['GET'])]
    public function index(SessionInterface $session, TuteurRepository $tuteurRepository, EtudiantRepository $etudiantRepository): Response
    {
        $tuteur = $this->requireTuteur($session, $tuteurRepository);
        if ($tuteur instanceof RedirectResponse) {
            return $tuteur;
        }

        return $this->render('etudiants/index.html.twig', [
            'etudiants' => $etudiantRepository->findBy(['tuteur' => $tuteur]),
            'tuteur' => $tuteur,
        ]);
    }

    #[Route('/etudiants/new', name: 'etudiants_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SessionInterface $session, TuteurRepository $tuteurRepository, EntityManagerInterface $em): Response
    {
        $tuteur = $this->requireTuteur($session, $tuteurRepository);
        if ($tuteur instanceof RedirectResponse) {
            return $tuteur;
        }

        $etudiant = new Etudiant();
        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $etudiant->setTuteur($tuteur);
            $em->persist($etudiant);
            $em->flush();

            $this->addFlash('success', 'Étudiant ajouté.');
            return $this->redirectToRoute('etudiants_index');
        }

        return $this->render('etudiants/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/etudiants/{id}/edit', name: 'etudiants_edit', methods: ['GET', 'POST'])]
    public function edit(Etudiant $etudiant, Request $request, SessionInterface $session, TuteurRepository $tuteurRepository, EntityManagerInterface $em): Response
    {
        $tuteur = $this->requireTuteur($session, $tuteurRepository);
        if ($tuteur instanceof RedirectResponse) {
            return $tuteur;
        }

        if ($etudiant->getTuteur()?->getId() !== $tuteur->getId()) {
            $this->addFlash('error', 'Vous ne pouvez modifier que vos étudiants.');
            return $this->redirectToRoute('etudiants_index');
        }

        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Étudiant mis à jour.');
            return $this->redirectToRoute('etudiants_index');
        }

        return $this->render('etudiants/edit.html.twig', [
            'form' => $form->createView(),
            'etudiant' => $etudiant,
        ]);
    }

    #[Route('/etudiants/{id}/delete', name: 'etudiants_delete', methods: ['POST'])]
    public function delete(Etudiant $etudiant, Request $request, SessionInterface $session, TuteurRepository $tuteurRepository, EntityManagerInterface $em): Response
    {
        $tuteur = $this->requireTuteur($session, $tuteurRepository);
        if ($tuteur instanceof RedirectResponse) {
            return $tuteur;
        }

        if ($etudiant->getTuteur()?->getId() !== $tuteur->getId()) {
            $this->addFlash('error', 'Suppression non autorisée.');
            return $this->redirectToRoute('etudiants_index');
        }

        if ($this->isCsrfTokenValid('delete_etudiant_' . $etudiant->getId(), $request->request->get('_token'))) {
            $em->remove($etudiant);
            $em->flush();
            $this->addFlash('success', 'Étudiant supprimé.');
        }

        return $this->redirectToRoute('etudiants_index');
    }

    private function requireTuteur(SessionInterface $session, TuteurRepository $tuteurRepository)
    {
        $tuteurId = $session->get('tuteur_id');
        if (!$tuteurId) {
            $this->addFlash('error', 'Merci de vous connecter.');
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($tuteurId);
        if (!$tuteur) {
            $session->remove('tuteur_id');
            $this->addFlash('error', 'Session invalide.');
            return $this->redirectToRoute('login');
        }

        return $tuteur;
    }
}
