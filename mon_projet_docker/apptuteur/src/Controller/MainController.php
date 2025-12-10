<?php
// src/Controller/MainController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class MainController extends AbstractController
{
    #[Route('index')]
    public function index(Environment $twig): Response
    {
        $html = $twig->render('base.html.twig');
        return new Response($html);
    }

    #[Route('bonjour/{nom}', defaults: ['nom' => 'Inconnue'], requirements: ['nom' => '[A-Za-z]+'], methods: ['GET'])]
    public function indexbis(string $nom): Response
    {
        return new Response('<html><body>Bonjour ' . htmlspecialchars($nom) . '</body></html>');
    }

    //#[Route('calcul/{value}', name: 'calcul', defaults: ['value' => 0], requirements: ['value' => '^(100|[1-9]?[0-9])$'], methods: ['GET'])] 
    //Aller voir au niveau du fichier routes.yaml
    public function indexter(string $value): Response
    {
        return new Response('<html><body>Calcul ' . $value . '</body></html>');
    }
}