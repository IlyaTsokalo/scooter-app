<?php

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;


abstract class AbstractTest extends ApiTestCase
{
    protected string $apiKey;

    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->apiKey = static::getContainer()->getParameter('API_KEY');
    }

    protected function createClientWithAuthorization(): Client
    {
        return static::createClient([], ['headers' => ['Authorization' => sprintf('Bearer %s', $this->apiKey)]]);
    }

    protected function createUser(string $email, string $password): User
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setUuid(Uuid::v4());
        $user->setEmail($email);
        $user->setPassword(hash('sha256', $password));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
