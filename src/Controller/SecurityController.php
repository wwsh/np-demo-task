<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * @Route("/logout", name="logout", methods={"POST"})
     */
    public function logout()
    {
    }

    /**
     * @Route("/_fos_user_context_hash", name="fos_user_context_hash", methods={"GET"})
     */
    public function userContextHash()
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([], Response::HTTP_NOT_ACCEPTABLE);
        }

        $hash = md5($user->getId());

        return $this->json(
            [],
            Response::HTTP_OK,
            [
                'X-User-Context-Hash' => $hash,
                'Content-Type' => 'application/vnd.fos.user-context-hash',
            ]
        );
    }
}
