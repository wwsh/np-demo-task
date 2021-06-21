<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ItemControllerTest extends WebTestCase
{
    private $client;
    
    protected function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();

        // @todo Go: composer require --dev dama/doctrine-test-bundle
        // @todo and enable transaction isolation => requires upgrade to SF 5.2
        /** @var ItemRepository $itemRepository */
        $itemRepository = static::$container->get(ItemRepository::class);
        /** @var EntityManager $entityManager */
        $entityManager = static::$container->get(EntityManagerInterface::class);

        foreach($itemRepository->findAll() as $item) {
            $entityManager->remove($item);
        }
        $entityManager->flush();
    }

    public function testCreate()
    {
        $userRepository = static::$container->get(UserRepository::class);
        /** @var ItemRepository $itemRepository */
        $itemRepository = static::$container->get(ItemRepository::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');

        $this->client->loginUser($user);

        $data = 'very secure new item data';

        $newItemData = ['data' => $data];

        $this->client->request('POST', '/item', $newItemData);
        $this->client->request('GET', '/item');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('very secure new item data', $this->client->getResponse()->getContent());

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
    }

    public function testUpdate(): void
    {
        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');

        $this->client->loginUser($user);

        $data = 'super secure cool data';

        $newItemData = ['data' => $data];

        $this->client->request('POST', '/item', $newItemData);
        $this->assertResponseIsSuccessful();

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
        $id = $item->getId();

        $data = 'changed my mind with secure data';

        $updatedItemData = ['data' => $data];

        $this->client->request('PUT', '/item/' . $id, $updatedItemData);
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/item');
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString('changed my mind with secure data', $this->client->getResponse()->getContent());
        $this->assertStringNotContainsString('super secure cool data', $this->client->getResponse()->getContent());

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
    }

    public function testDelete(): void
    {
        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');

        $this->client->loginUser($user);

        $data = 'volatile data';

        $newItemData = ['data' => $data];

        $this->client->request('POST', '/item', $newItemData);
        $this->assertResponseIsSuccessful();

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
        $id = $item->getId();

        $this->client->request('DELETE', '/item/' . $id);
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/item');
        $this->assertResponseIsSuccessful();

        $this->assertStringNotContainsString('volatile data', $this->client->getResponse()->getContent());

        $item = $itemRepository->findOneByData($data);
        $this->assertNull($item);
    }

    public function testUpdateSecurity(): void
    {
        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');

        $this->client->loginUser($user);

        $data = 'super secure cool data';

        $newItemData = ['data' => $data];

        $this->client->request('POST', '/item', $newItemData);
        $this->assertResponseIsSuccessful();

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
        $id = $item->getId();

        /** @var User $user */
        $user = $userRepository->findOneByUsername('steve');

        $this->client->loginUser($user);

        $data = 'changed my mind with secure data';

        $updatedItemData = ['data' => $data];

        $this->client->request('PUT', '/item/' . $id, $updatedItemData);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteSecurity(): void
    {
        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);
        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');

        $this->client->loginUser($user);

        $data = 'super secure cool data';

        $newItemData = ['data' => $data];

        $this->client->request('POST', '/item', $newItemData);
        $this->assertResponseIsSuccessful();

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
        $id = $item->getId();

        /** @var User $user */
        $user = $userRepository->findOneByUsername('steve');

        $this->client->loginUser($user);

        $this->client->request('DELETE', '/item/' . $id);
        $this->assertResponseStatusCodeSame(403);
    }
}
