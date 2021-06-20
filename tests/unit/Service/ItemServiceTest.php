<?php

namespace App\Tests\Unit\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Service\ItemService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ItemServiceTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;

    /**
     * @var ItemService
     */
    private $itemService;

    public function setUp(): void
    {
        /** @var EntityManagerInterface */
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->itemService = new ItemService($this->entityManager);
    }

    public function testCreate(): void
    {
        /** @var User */
        $user = $this->createMock(User::class);
        $data = 'secret data';

        $expectedObject = new Item();
        $expectedObject->setUser($user);
        $expectedObject->setData($data);

        $this->entityManager->expects($this->once())->method('persist')->with($expectedObject);

        $this->itemService->create($user, $data);
    }

    public function testUpdate(): void
    {
        $data = 'update data';
        $id = 1;
        $entity = new Item();

        $entity->setData($data);

        $repositoryMock = $this->createMock(ServiceEntityRepository::class);
        $repositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($entity);

        $this->entityManager->expects($this->at(0))
            ->method('getRepository')
            ->with(Item::class)
            ->willReturn($repositoryMock);

        $this->entityManager->expects($this->at(1))
            ->method('persist')
            ->with($entity);

        $this->itemService->update($id, $data);
    }

    public function testDelete(): void
    {
        $item = new Item();

        $this->entityManager->expects($this->once())->method('remove')->with($item);

        $this->itemService->delete($item);
    }

    public function testGetAll(): void
    {
        $user = $this->createMock(User::class);

        $repositoryMock = $this->createMock(ServiceEntityRepository::class);
        $repositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with(['user' => $user])
            ->willReturn([]);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Item::class)
            ->willReturn($repositoryMock);

        $this->itemService->getAll($user);
    }
}
