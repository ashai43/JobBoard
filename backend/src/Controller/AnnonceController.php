<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Annonce;
use App\Entity\Candidat;
use App\Repository\AnnonceRepository;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;


final class AnnonceController extends AbstractController
{
    #[Route('/annonce', name: 'app_annonce', methods: ['GET'])]
    public function index(AnnonceRepository $annonceRepository): JsonResponse
    {
        $annonces = $annonceRepository->findAll();

        return new JsonResponse($annonces, 200);
    }

    #[Route('/annonce/{id}', name: 'app_annonce_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, AnnonceRepository $annonceRepository): JsonResponse
    {
        $annonce = $annonceRepository->find($id);
        if (!$annonce) {
            return new JsonResponse(['message' => 'Annonce not found'], 404);
        }
        return new JsonResponse($annonce, 200);
    }


}
