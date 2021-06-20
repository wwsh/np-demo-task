<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ItemControllerTest extends WebTestCase
{
    public function testCreate()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);
        $entityManager = static::$container->get(EntityManagerInterface::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);

        $data = 'very secure new item data';

        $newItemData = ['data' => $data];

        $client->request('POST', '/item', $newItemData);
        $client->request('GET', '/item');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('very secure new item data', $client->getResponse()->getContent());

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
    }

    public function testUpdate(): void
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);

        $data = 'super secure cool data';

        $newItemData = ['data' => $data];

        $client->request('POST', '/item', $newItemData);
        $this->assertResponseIsSuccessful();

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
        $id = $item->getId();

        $data = 'changed my mind with secure data';

        $updatedItemData = ['data' => $data];

        $client->request('PUT', '/item/' . $id, $updatedItemData);
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/item');
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString('changed my mind with secure data', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('super secure cool data', $client->getResponse()->getContent());

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
    }

    public function testDelete(): void
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);
        $itemRepository = static::$container->get(ItemRepository::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);

        $data = 'volatile data';

        $newItemData = ['data' => $data];

        $client->request('POST', '/item', $newItemData);
        $this->assertResponseIsSuccessful();

        $item = $itemRepository->findOneByData($data);
        $this->assertEquals($user->getUsername(), $item->getUser()->getUsername());
        $id = $item->getId();

        $client->request('DELETE', '/item/' . $id);
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/item');
        $this->assertResponseIsSuccessful();

        $this->assertStringNotContainsString('volatile data', $client->getResponse()->getContent());

        $item = $itemRepository->findOneByData($data);
        $this->assertNull($item);
    }
}
