<?php

namespace App\Controller;

use App\Entity\Kartenn;
use App\Repository\KartennRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class KartennController extends AbstractController
{

    public function __construct(
        private KartennRepository $kartennRepository
    )
    {
    }

    #[Route('/', name: 'app_kartenn')]
    public function index(): Response
    {
        $kartennou = $this->kartennRepository->findAll();

        foreach($kartennou as $key=>$item) {
            if($item->getPdf() === null) {
                unset($kartennou[$key]);
            }
        }

        usort($kartennou, function(Kartenn $a,Kartenn  $b) {
            return $a->getNiverenn() > $b->getNiverenn();
        });

        return $this->render('index.html.twig', [
            'kartennou' => $kartennou,
        ]);

    }
}
