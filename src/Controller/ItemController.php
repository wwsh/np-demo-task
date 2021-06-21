<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ItemController extends AbstractController
{
    /**
     * @Route("/item", name="item_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function list(SerializerInterface $serializer, ItemService $itemService): JsonResponse
    {
        $items = $itemService->getAll($this->getUser());

        $data = $serializer->normalize(
            $items,
            null,
            ['attributes' => Item::SERIALIZABLES]
        );

        return $this->json($data);
    }

    /**
     * @Route("/item", name="item_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, ItemService $itemService): JsonResponse
    {
        $data = $request->get('data');

        if (empty($data)) {
            return $this->json(['error' => 'No data parameter']);
        }

        $itemService->create($this->getUser(), $data);

        return $this->json([]);
    }

    /**
     * @Route("/item/{id}", name="items_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(Request $request, int $id, ItemService $itemService): JsonResponse
    {
        if (empty($id)) {
            return $this->json(['error' => 'No id parameter'], Response::HTTP_BAD_REQUEST);
        }

        $item = $itemService->get($id);

        if ($item === null) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        if ($item->getUser()->getId() !== $this->getUser()->getId()) {
            return $this->json(['error' => 'Not allowed'], Response::HTTP_FORBIDDEN);
        }

        $itemService->delete($item);

        return $this->json([]);
    }

    /**
     * @Route("/item/{id}", name="items_put", methods={"PUT"})
     * @IsGranted("ROLE_USER")
     */
    public function update(Request $request, int $id, ItemService $itemService): JsonResponse
    {
        if (empty($id)) {
            return $this->json(['error' => 'No id parameter'], Response::HTTP_BAD_REQUEST);
        }

        $item = $itemService->get($id);

        if ($item === null) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $data = $request->get('data');

        if (empty($data)) {
            return $this->json(['error' => 'No data parameter'], Response:: HTTP_BAD_REQUEST);
        }

        if ($item->getUser()->getId() !== $this->getUser()->getId()) {
            return $this->json(['error' => 'Not allowed'], Response::HTTP_FORBIDDEN);
        }

        $itemService->update($id, $data);

        return $this->json([]);
    }
}
