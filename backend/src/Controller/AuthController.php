<?php

namespace App\Controller;

use App\Dto\RegisterCandidatRequest;
use App\Entity\Candidat;
use App\Repository\CandidatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterCandidatRequest $request,
        CandidatRepository $candidatRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $email = trim($request->email);
        if ($candidatRepository->findOneBy(['email' => $email]) !== null) {
            return $this->json(['message' => 'An account with this email already exists.'], 409);
        }

        $candidat = (new Candidat())
            ->setNom(trim($request->nom))
            ->setPrenom(trim($request->prenom))
            ->setEmail($email);
        $candidat->setPassword($passwordHasher->hashPassword($candidat, $request->password));

        $entityManager->persist($candidat);
        $entityManager->flush();

        return $this->json([
            'id' => $candidat->getId(),
            'email' => $candidat->getEmail(),
        ], 201);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function loginCheck(): JsonResponse
    {
        return $this->json(['message' => 'Invalid credentials.'], 401);
    }
}
