<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Candidature;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CandidatureController extends AbstractController
{
    #[Route(
        '/api/annonces/{id}/candidatures',
        name: 'api_candidature_create',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function create(
        int $id,
        Request $request,
        #[CurrentUser] Candidat $candidat,
        AnnonceRepository $annonceRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        #[Autowire('%kernel.project_dir%/var/uploads/cv')] string $cvDirectory,
    ): JsonResponse {
        $annonce = $annonceRepository->find($id);
        if ($annonce === null) {
            return $this->json(['message' => 'Job offer not found.'], 404);
        }

        $uploadedCv = $request->files->get('cv');
        if ($uploadedCv !== null && !$uploadedCv instanceof UploadedFile) {
            return $this->json(['errors' => ['cv' => ['The CV must be an uploaded PDF file.']]], 422);
        }

        $motivationLetter = $request->request->get('lettreMotivation');
        if ($motivationLetter !== null && !is_string($motivationLetter)) {
            return $this->json(['errors' => ['lettreMotivation' => ['The motivation letter must be text.']]], 422);
        }

        $candidature = (new Candidature())
            ->setDate(new \DateTime())
            ->setStatut('pending')
            ->setLettreMotivation($motivationLetter)
            ->setIdCandidat($candidat)
            ->setIdAnnonce($annonce)
            ->setCvFile($uploadedCv);

        $violations = $validator->validate($candidature);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], 422);
        }

        $storedFilename = null;
        if ($uploadedCv !== null) {
            if (!is_dir($cvDirectory) && !mkdir($cvDirectory, 0770, true) && !is_dir($cvDirectory)) {
                throw new \RuntimeException(sprintf('Could not create CV storage directory "%s".', $cvDirectory));
            }

            $storedFilename = bin2hex(random_bytes(16)).'.pdf';
            $storedPath = $cvDirectory.DIRECTORY_SEPARATOR.$storedFilename;
            $uploadedCv->move($cvDirectory, $storedFilename);
            $candidature->setCv($storedFilename);
        }

        $entityManager->persist($candidature);

        try {
            $entityManager->flush();
        } catch (\Throwable $exception) {
            if ($storedFilename !== null && is_file($storedPath) && !unlink($storedPath)) {
                throw new \RuntimeException('Could not remove the uploaded CV after the application failed to save.', previous: $exception);
            }

            throw $exception;
        }

        return $this->json([
            'id' => $candidature->getId(),
            'statut' => $candidature->getStatut(),
            'cvUploaded' => $storedFilename !== null,
        ], 201);
    }
}
