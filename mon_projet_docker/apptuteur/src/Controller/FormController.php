<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class FormController extends AbstractController
{
    public function hello(Environment $twig, $prenom = "Sophie")
    {
        $html = $twig->render('hello.html.twig', ['prenom' => strtoupper($prenom)]);

        return new Response("Hello $html");
    }

    public function liste(Environment $twig)
    {
        // On fournit ici des données simulées complètes (avec la clé 'etudiants')
        $tab = [
            'tuteurs' => [
                ['id' => 1, 'nom' => 'Jhonson', 'prenom' => 'Paul', 'etudiants' => []],
                ['id' => 2, 'nom' => 'Walberg', 'prenom' => 'Mark', 'etudiants' => []]
            ]
        ];

        $html = $twig->render('tuteurs.html.twig', $tab);

        return new Response($html);
    }

    // Affiche le formulaire
    public function search(Environment $twig): Response
    {
        $html = $twig->render('search_tuteur.html.twig');
        return new Response($html);
    }

    // Vérifie si le tuteur existe
    public function verify(Request $request, Environment $twig): Response
    {
        $nom = $request->request->get('nom');
        $tuteurs = ['Jhonson', 'Walberg'];

        $existe = in_array($nom, $tuteurs);

        $html = $twig->render('verify_tuteur.html.twig', [
            'nom' => $nom,
            'existe' => $existe
        ]);

        return new Response($html);
    }
}


?>