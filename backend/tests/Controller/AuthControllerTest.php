<?php

namespace App\Tests\Controller;

use App\Entity\Annonce;
use App\Repository\CandidatRepository;
use App\Repository\CandidatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthControllerTest extends WebTestCase
{
    public function testCandidateCanRegisterAndAuthenticateWithJwt(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $client->jsonRequest('POST', '/api/register', [
            'nom' => 'Martin',
            'prenom' => 'Alex',
            'email' => 'alex.martin@example.test',
            'password' => 'A-long-password-123',
        ]);

        self::assertResponseStatusCodeSame(201);
        $candidat = static::getContainer()->get(CandidatRepository::class)->findOneBy([
            'email' => 'alex.martin@example.test',
        ]);
        self::assertNotNull($candidat);
        self::assertNotSame('A-long-password-123', $candidat->getPassword());

        $client->jsonRequest('POST', '/api/register', [
            'nom' => 'Martin',
            'prenom' => 'Alex',
            'email' => 'alex.martin@example.test',
            'password' => 'A-long-password-123',
        ]);

        self::assertResponseStatusCodeSame(409);

        $client->jsonRequest('POST', '/api/login', [
            'email' => 'alex.martin@example.test',
            'password' => 'wrong-password',
        ]);

        self::assertResponseStatusCodeSame(401);

        $client->jsonRequest('POST', '/api/login', [
            'email' => 'alex.martin@example.test',
            'password' => 'A-long-password-123',
        ]);

        self::assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsString($response['token'] ?? null);

        $annonce = (new Annonce())
            ->setTitre('Développeur Symfony')
            ->setDescription('Développer des applications web avec Symfony.');
        $entityManager->persist($annonce);
        $entityManager->flush();

        $temporaryPdf = tempnam(sys_get_temp_dir(), 'cv_');
        self::assertNotFalse($temporaryPdf);
        file_put_contents($temporaryPdf, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF");

        $storedCvPath = null;
        try {
            $client->request(
                'POST',
                '/api/annonces/'.$annonce->getId().'/candidatures',
                ['lettreMotivation' => 'Je souhaite rejoindre votre équipe.'],
                ['cv' => new UploadedFile($temporaryPdf, 'cv.pdf', 'application/pdf', null, true)],
                ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']],
            );

            self::assertResponseStatusCodeSame(201);
            $candidatureResponse = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
            self::assertTrue($candidatureResponse['cvUploaded']);

            $candidature = static::getContainer()->get(CandidatureRepository::class)->find($candidatureResponse['id']);
            self::assertNotNull($candidature);
            self::assertSame('pending', $candidature->getStatut());
            self::assertSame('alex.martin@example.test', $candidature->getIdCandidat()->getEmail());
            self::assertSame($annonce->getId(), $candidature->getIdAnnonce()->getId());

            $storedCvPath = dirname(__DIR__, 2).'/var/uploads/cv/'.$candidature->getCv();
            self::assertFileExists($storedCvPath);
            self::assertStringNotContainsString('/public/', str_replace('\\', '/', $storedCvPath));
        } finally {
            if ($storedCvPath !== null && is_file($storedCvPath) && !unlink($storedCvPath)) {
                throw new \RuntimeException('Could not remove the test CV.');
            }
            if (is_file($temporaryPdf) && !unlink($temporaryPdf)) {
                throw new \RuntimeException('Could not remove the temporary test upload.');
            }
        }

        $invalidCv = tempnam(sys_get_temp_dir(), 'cv_invalid_');
        self::assertNotFalse($invalidCv);
        file_put_contents($invalidCv, 'This is not a PDF.');

        try {
            $client->request(
                'POST',
                '/api/annonces/'.$annonce->getId().'/candidatures',
                [],
                ['cv' => new UploadedFile($invalidCv, 'cv.txt', 'text/plain', null, true)],
                ['HTTP_AUTHORIZATION' => 'Bearer '.$response['token']],
            );

            self::assertResponseStatusCodeSame(422);
        } finally {
            if (is_file($invalidCv) && !unlink($invalidCv)) {
                throw new \RuntimeException('Could not remove the invalid test upload.');
            }
        }
    }
}
