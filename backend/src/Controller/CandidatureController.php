<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Candidature;
use App\Repository\AnnonceRepository;
use App\Repository\CandidatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
final class CandidatureController extends AbstractController
{
    #[Route(
        '/api/annonces/{id}/candidatures',
        name: 'api_candidatures_create',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_CANDIDAT')]
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
            return $this->json(['message' => 'Job offer not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $uploadedCv = $request->files->get('cv');
        if ($uploadedCv !== null && !$uploadedCv instanceof UploadedFile) {
            return $this->json(['errors' => ['cv' => ['The CV must be an uploaded PDF file.']]], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $motivationLetter = $request->request->get('lettreMotivation');
        if ($motivationLetter !== null && !is_string($motivationLetter)) {
            return $this->json(['errors' => ['lettreMotivation' => ['The motivation letter must be text.']]], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
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

            return $this->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $storedFilename = null;
        $storedPath = null;
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
            if ($storedFilename !== null && $storedPath !== null && is_file($storedPath) && !unlink($storedPath)) {
                throw new \RuntimeException('Could not remove the uploaded CV after the application failed to save.', previous: $exception);
            }

            throw $exception;
        }

        return $this->json([
            'id' => $candidature->getId(),
            'statut' => $candidature->getStatut(),
            'cvUploaded' => $storedFilename !== null,
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/annonces/{id}/candidatures', name: 'api_annonces_candidatures_index', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_ENTREPRISE')]
    public function showAnnonceCandidatures(
        int $id,
        AnnonceRepository $annonceRepository,
        CandidatureRepository $candidatureRepository,
        #[CurrentUser] Candidat $candidat,
    ): JsonResponse {
        $annonce = $annonceRepository->find($id);
        if (!$annonce) {
            return new JsonResponse(['message' => 'Applications not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // vérification si l'utilisateur connecté est le propriétaire de l'annonce
        if ($annonce->getIdEntreprise()?->getCompteCandidat()?->getId() !== $candidat->getId()) {
            return new JsonResponse(['message' => 'You are not allowed to view these applications.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $candidatures = $candidatureRepository->findBy(['idAnnonce' => $annonce], ['id' => 'ASC']);

        return $this->json(array_map(
            fn (Candidature $candidature): array => $this->formatCandidature($candidature),
            $candidatures,
        ));
    }

    #[Route('/api/annonces/{id}/candidatures/{candidatureId}', name: 'api_annonces_candidature_show', requirements: ['id' => '\d+', 'candidatureId' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_ENTREPRISE')]
    public function showAnnonceCandidature(
        int $id,
        int $candidatureId,
        AnnonceRepository $annonceRepository,
        CandidatureRepository $candidatureRepository,
        #[CurrentUser] Candidat $candidat,
    ): JsonResponse {
        $annonce = $annonceRepository->find($id);
        if (!$annonce) {
            return new JsonResponse(['message' => 'Application not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        // vérification si l'utilisateur connecté est le propriétaire de l'annonce
        if ($annonce->getIdEntreprise()?->getCompteCandidat()?->getId() !== $candidat->getId()) {
            return new JsonResponse(['message' => 'You are not allowed to view this application.'], JsonResponse::HTTP_FORBIDDEN);
        }
        $candidature = $candidatureRepository->findOneBy([
            'id' => $candidatureId,
            'idAnnonce' => $annonce,
        ]);
        if (!$candidature) {
            return new JsonResponse(['message' => 'Application not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($this->formatCandidature($candidature), JsonResponse::HTTP_OK);
    }

    #[Route('/api/annonces/{id}/candidatures/{candidatureId}', name: 'api_annonces_candidature_delete', requirements: ['id' => '\d+', 'candidatureId' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_CANDIDAT')]
    public function deleteCandidature(
        int $id,
        int $candidatureId,
        AnnonceRepository $annonceRepository,
        CandidatureRepository $candidatureRepository,
        #[CurrentUser] Candidat $candidat,
        EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/var/uploads/cv')] string $cvDirectory,
    ): JsonResponse {
        $annonce = $annonceRepository->find($id);
        if (!$annonce) {
            return new JsonResponse(['message' => 'Application not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $candidature = $candidatureRepository->findOneBy([
            'id' => $candidatureId,
            'idAnnonce' => $annonce,
        ]);
        if (!$candidature) {
            return new JsonResponse(['message' => 'Application not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        // vérification si l'utilisateur connecté est le propriétaire de la candidature
        if ($candidature->getIdCandidat()?->getId() !== $candidat->getId()) {
            return new JsonResponse(['message' => 'You are not allowed to delete this application.'], JsonResponse::HTTP_FORBIDDEN);
        }
        $cvFilename = $candidature->getCv();
        $cvPath = null;
        if ($cvFilename !== null) {
            $cvPath = $cvDirectory.DIRECTORY_SEPARATOR.$cvFilename;
            if (basename($cvFilename) !== $cvFilename || is_link($cvPath)) {
                throw new \RuntimeException('The stored CV filename is invalid.');
            }
        }

        $entityManager->remove($candidature);
        $entityManager->flush();

        if ($cvPath !== null && is_file($cvPath) && !unlink($cvPath)) {
            throw new \RuntimeException(sprintf('Could not delete the stored CV "%s".', $cvPath));
        }

        return $this->json(['message' => 'Application deleted successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/candidatures/{id}', name: 'api_candidatures_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_CANDIDAT')]
    public function showCandidature(
        int $id,
        CandidatureRepository $candidatureRepository,
        #[CurrentUser] Candidat $candidat,
    ): JsonResponse {
        $candidature = $candidatureRepository->find($id);
        if (!$candidature) {
            return new JsonResponse(['message' => 'Application not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        // vérification si l'utilisateur connecté est le propriétaire de la candidature
        if ($candidature->getIdCandidat()?->getId() !== $candidat->getId()) {
            return new JsonResponse(['message' => 'You are not allowed to view this application.'], JsonResponse::HTTP_FORBIDDEN);
        }
        return $this->json($this->formatCandidature($candidature), JsonResponse::HTTP_OK);
    }

    #[Route('/api/candidatures', name: 'api_candidatures_index', methods: ['GET'])]
    #[IsGranted('ROLE_CANDIDAT')]
    public function showCandidatures(
        CandidatureRepository $candidatureRepository,
        #[CurrentUser] Candidat $candidat,
    ): JsonResponse {
        $candidatures = $candidatureRepository->findBy(['idCandidat' => $candidat], ['id' => 'ASC']);

        return $this->json(array_map(
            fn (Candidature $candidature): array => $this->formatCandidature($candidature),
            $candidatures,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCandidature(Candidature $candidature): array
    {
        $candidat = $candidature->getIdCandidat();
        $annonce = $candidature->getIdAnnonce();

        return [
            'id' => $candidature->getId(),
            'date' => $candidature->getDate()?->format(\DateTimeInterface::ATOM),
            'statut' => $candidature->getStatut(),
            'lettreMotivation' => $candidature->getLettreMotivation(),
            'cvUploaded' => $candidature->getCv() !== null,
            'candidat' => $candidat === null ? null : [
                'id' => $candidat->getId(),
                'nom' => $candidat->getNom(),
                'prenom' => $candidat->getPrenom(),
                'email' => $candidat->getEmail(),
            ],
            'annonce' => $annonce === null ? null : [
                'id' => $annonce->getId(),
                'titre' => $annonce->getTitre(),
            ],
        ];
    }
}
