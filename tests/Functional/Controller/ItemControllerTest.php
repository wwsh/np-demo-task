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
}
