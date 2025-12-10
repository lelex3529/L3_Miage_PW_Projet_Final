<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Entity\Visite;
use App\Form\VisiteCompteRenduType;
use App\Form\VisiteType;
use App\Repository\TuteurRepository;
use App\Repository\VisiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class VisiteController extends AbstractController
{
    #[Route('/etudiants/{id}/visites', name: 'etudiant_visites', methods: ['GET'])]
    public function index(Etudiant $etudiant, Request $request, SessionInterface $session, TuteurRepository $tuteurRepository, VisiteRepository $visiteRepository): Response
    {
        $tuteur = $this->requireTuteur($session, $tuteurRepository);
        if ($tuteur instanceof RedirectResponse) {
            return $tuteur;
        }

        if ($etudiant->getTuteur()?->getId() !== $tuteur->getId()) {
            $this->addFlash('error', 'Cet étudiant ne vous appartient pas.');
            return $this->redirectToRoute('etudiants_index');
        }

        $statut = $request->query->get('statut');
        $dir = strtoupper($request->query->get('dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $selectedStatut = in_array($statut, [Visite::STATUT_PREVUE, Visite::STATUT_REALISEE, Visite::STATUT_ANNULEE], true) ? $statut : null;
        $visites = $visiteRepository->findForEtudiantWithFilters($etudiant, $selectedStatut, $dir);

        return $this->render('visites/index.html.twig', [
            'etudiant' => $etudiant,
            'visites' => $visites,
            'statut' => $selectedStatut,
            'dir' => $dir,
        ]);
    }

    #[Route('/etudiants/{id}/visites/new', name: 'visites_new', methods: ['GET', 'POST'])]
    public function new(Etudiant $etudiant, Request $request, SessionInterface $session, TuteurRepository $tuteurRepository, EntityManagerInterface $em): Response
    {
        $tuteur = $this->requireTuteur($session, $tuteurRepository);
        if ($tuteur instanceof RedirectResponse) {
            return $tuteur;
        }

        if ($etudiant->getTuteur()?->getId() !== $tuteur->getId()) {
            $this->addFlash('error', 'Cet étudiant ne vous appartient pas.');
            return $this->redirectToRoute('etudiants_index');
        }

        $visite = new Visite();
        $visite->setEtudiant($etudiant);
        $visite->setTuteur($tuteur);
        $visite->setStatut(Visite::STATUT_PREVUE);
        $visite->setDate(new \DateTimeImmutable());

        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($visite);
            $em->flush();

            $this->addFlash('success', 'Visite créée.');
            return $this->redirectToRoute('etudiant_visites', ['id' => $etudiant->getId()]);
        }

        return $this->render('visites/new.html.twig', [
            'form' => $form->createView(),
            'etudiant' => $etudiant,
        ]);
    }

    #[Route('/visites/{id}/edit', name: 'visites_edit', methods: ['GET', 'POST'])]
    public function edit(Visite $visite, Request $request, SessionInterface $session, TuteurRepository $tuteurRepository, EntityManagerInterface $em): Response
    {
        $tuteur = $this->requireTuteur($session, $tuteurRepository);
        if ($tuteur instanceof RedirectResponse) {
            return $tuteur;
        }

        if ($visite->getTuteur()?->getId() !== $tuteur->getId()) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('etudiants_index');
        }

        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Visite mise à jour.');
            return $this->redirectToRoute('etudiant_visites', ['id' => $visite->getEtudiant()->getId()]);
        }

        return $this->render('visites/edit.html.twig', [
            'form' => $form->createView(),
            'visite' => $visite,
        ]);
    }

    #[Route('/visites/{id}/compte-rendu', name: 'visites_compte_rendu', methods: ['GET', 'POST'])]
    public function compteRendu(Visite $visite, Request $request, SessionInterface $session, TuteurRepository $tuteurRepository, EntityManagerInterface $em): Response
    {
        $tuteur = $this->requireTuteur($session, $tuteurRepository);
        if ($tuteur instanceof RedirectResponse) {
            return $tuteur;
        }

        if ($visite->getTuteur()?->getId() !== $tuteur->getId()) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('etudiants_index');
        }

        if ($request->query->get('export') === 'pdf') {
            $html = $this->renderView('visites/compte_rendu_pdf.html.twig', [
                'visite' => $visite,
                'etudiant' => $visite->getEtudiant(),
                'tuteur' => $visite->getTuteur(),
            ]);

            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="compte-rendu-visite-' . $visite->getId() . '.pdf"',
            ]);
        }

        $form = $this->createForm(VisiteCompteRenduType::class, $visite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Compte rendu enregistré.');
            return $this->redirectToRoute('etudiant_visites', ['id' => $visite->getEtudiant()->getId()]);
        }

        return $this->render('visites/compte_rendu.html.twig', [
            'form' => $form->createView(),
            'visite' => $visite,
            'etudiant' => $visite->getEtudiant(),
        ]);
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
