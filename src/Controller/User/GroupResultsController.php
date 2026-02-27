<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class GroupResultsController extends AbstractController
{
    /**
     * @Route("/my-result", name="group_results", methods={"GET"})
     */
    public function results(): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser) {
            throw $this->createAccessDeniedException();
        }

        // Block admins
        if ($this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $group = $currentUser->getGroup();

        return $this->render('exchange/user_results.html.twig', [
            'group' => $group,
            'user'  => $currentUser,
        ]);
    }
}