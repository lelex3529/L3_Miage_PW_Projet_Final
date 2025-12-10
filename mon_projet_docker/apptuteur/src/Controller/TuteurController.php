<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class TuteurController extends AbstractController
{
    static private $tuteurs = [
        [
            'id' => 1,
            'nom' => 'Johnson',
            'prenom' => 'Alex',
            'entreprise' => 'Acme',
            'email' => 'paul.johnson@acme.com',
            'telephone' => '06 00 00 00 01',
            'etudiants' => [
                [
                    'nom' => 'Martin',
                    'prenom' => 'Léa',
                    'sujet' => 'Détection d’anomalies sur flux bancaires'
                ],
                [
                    'nom' => 'Durand',
                    'prenom' => 'Noah',
                    'sujet' => 'Dashboard risques crédit'
                ]
            ]
        ],
        [
            'id' => 2,
            'nom' => 'Walberg',
            'prenom' => 'Mark',
            'entreprise' => 'Globex',
            'email' => 'mark.walberg@globex.com',
            'telephone' => '06 00 00 00 02',
            'etudiants' => []
        ]
    ];

    public function index(Environment $twig): Response
    {
        $tuteurs = self::$tuteurs;  //On utilise self pour accéder à la variable statique

        // ...
        $html = $twig->render('tuteurs.html.twig', [
            'tuteurs' => $tuteurs  // On envoie directement la variable 'tuteurs'
        ]);
        // ...

        return new Response($html);
    }

    public function show(Environment $twig, $id): Response
    {
        $tuteurs = self::$tuteurs;
        // Rechercher le tuteur correspondant à l'id fourni
        $tuteur = null;
        foreach ($tuteurs as $t) {
            if ((int) $t['id'] === (int) $id) {
                $tuteur = $t;
                break;
            }
        }

        if (!$tuteur) {
            throw $this->createNotFoundException('Tuteur non trouvé.');
        }

        $html = $twig->render('tuteur/show.html.twig', [
            'tuteur' => $tuteur,
        ]);

        return new Response($html);
    }

    public function sujets(Environment $twig, Request $request): Response
    {
        $tuteurs = self::$tuteurs;

        // Liste des entreprises disponibles pour le filtre
        $entreprises = [];
        foreach (self::$tuteurs as $t) {
            if (!empty($t['entreprise'])) {
                $entreprises[] = $t['entreprise'];
            }
        }
        $entreprises = array_values(array_unique($entreprises));

        // Filtre par entreprise si fourni en query string (?entreprise=...)
        $selectedEntreprise = $request->query->get('entreprise');
        if ($selectedEntreprise) {
            $tuteurs = array_values(array_filter($tuteurs, function ($t) use ($selectedEntreprise) {
                return isset($t['entreprise']) && $t['entreprise'] === $selectedEntreprise;
            }));
        }

        // Collecte des sujets avec informations tuteur/étudiant
        $sujets = [];
        foreach ($tuteurs as $t) {
            $etudiants = $t['etudiants'] ?? [];
            foreach ($etudiants as $e) {
                $sujet = $e['sujet'] ?? ($e['sujet_stage'] ?? ($e['sujetDeStage'] ?? 'Sujet non renseigné'));
                $sujets[] = [
                    'sujet' => $sujet,
                    'etudiant' => $e,
                    'tuteur' => ['nom' => $t['nom'] ?? '', 'prenom' => $t['prenom'] ?? '', 'entreprise' => $t['entreprise'] ?? '']
                ];
            }
        }

        $html = $twig->render('sujet/index.html.twig', [
            'sujets' => $sujets,
            'entreprises' => $entreprises,
            'selectedEntreprise' => $selectedEntreprise
        ]);

        return new Response($html);
    }
}
?>