<?php

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ItemService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(User $user, string $data): void
    {
        $item = new Item();
        $item->setUser($user);
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function getAll(User $user): array
    {
        return $this->entityManager->getRepository(Item::class)
            ->findBy(['user' => $user]);
    }

    public function get(int $id): ?Item
    {
        return $this->entityManager->getRepository(Item::class)
            ->find($id);
    }

    public function delete(Item $item): void
    {
        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }
}
