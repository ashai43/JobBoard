<?php

namespace App\Tests\Controller;

use App\Entity\Annonce;
use App\Entity\Candidat;
use App\Entity\Candidature;
use App\Entity\Entreprise;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class AnnonceControllerTest extends WebTestCase
{
    public function testEntrepriseCanViewOnlyItsOwnAnnonceCandidatures(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $suffix = bin2hex(random_bytes(8));
        $compte = (new Candidat())
            ->setNom('Entreprise')
            ->setPrenom('Proprietaire')
            ->setEmail('owner-'.$suffix.'@example.test')
            ->setPassword('test-password')
            ->setRole('ROLE_ENTREPRISE');
        $autreCompte = (new Candidat())
            ->setNom('Entreprise')
            ->setPrenom('Autre')
            ->setEmail('other-'.$suffix.'@example.test')
            ->setPassword('test-password')
            ->setRole('ROLE_ENTREPRISE');
        $entreprise = (new Entreprise())
            ->setNom('Entreprise A')
            ->setAdresse('Adresse A')
            ->setCompteCandidat($compte);
        $autreEntreprise = (new Entreprise())
            ->setNom('Entreprise B')
            ->setAdresse('Adresse B')
            ->setCompteCandidat($autreCompte);
        $annoncePossedee = (new Annonce())
            ->setTitre('Annonce de A')
            ->setIdEntreprise($entreprise);
        $annonceAutre = (new Annonce())
            ->setTitre('Annonce de B')
            ->setIdEntreprise($autreEntreprise);
        $candidature = (new Candidature())
            ->setDate(new \DateTime())
            ->setStatut('pending')
            ->setLettreMotivation('Je souhaite rejoindre votre équipe.')
            ->setIdCandidat($autreCompte)
            ->setIdAnnonce($annoncePossedee);

        foreach ([$compte, $autreCompte, $entreprise, $autreEntreprise, $annoncePossedee, $annonceAutre, $candidature] as $entity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();

        $token = static::getContainer()->get(JWTTokenManagerInterface::class)->create($compte);
        $authorization = ['HTTP_AUTHORIZATION' => 'Bearer '.$token];
        $client->request('GET', '/annonce/'.$annoncePossedee->getId().'/candidatures', server: $authorization);
        self::assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(1, $response);
        self::assertSame($candidature->getId(), $response[0]['id']);
        self::assertSame('other-'.$suffix.'@example.test', $response[0]['candidat']['email']);

        $client->request(
            'GET',
            '/annonce/'.$annoncePossedee->getId().'/candidatures/'.$candidature->getId(),
            server: $authorization,
        );
        self::assertResponseIsSuccessful();
        $detailResponse = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($candidature->getId(), $detailResponse['id']);

        $client->request('GET', '/annonce/'.$annonceAutre->getId().'/candidatures', server: $authorization);
        self::assertResponseStatusCodeSame(403);
    }
}
