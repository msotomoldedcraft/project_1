<?php

namespace App\Controller;

use App\Service\DrawService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DrawController extends AbstractController
{
    /**
     * @Route("/draw/assign", name="draw_assign")
    */
    public function assign(DrawService $drawService, UserRepository $repo, EntityManagerInterface $em): Response
    {
        $users = $repo->findAll();
        $assigned = $drawService->assign($users);

        // Save the assignments in DB
        foreach ($assigned as $userId => $assignedUser) {
            $user = $repo->find($userId);
            $user->setAssignedTo($assignedUser->getName());
            $em->persist($user);
        }
        $em->flush();

        return $this->render('draw/assign.html.twig', [
            'assigned' => $assigned,
        ]);
    }
}