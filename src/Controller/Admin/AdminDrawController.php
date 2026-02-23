<?php

namespace App\Controller\Admin;

use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/exchange/admin")
 * @IsGranted("ROLE_ADMIN")
 */
class AdminDrawController extends AbstractController
{
    /**
     * Show the draw roulette page
     * 
     * @Route("/group/{groupId}/draw-names", name="admin_draw_names")
     */
    public function drawNames(
        int $groupId,
        GroupRepository $groupRepo,
        UserRepository $userRepo,
        EntityManagerInterface $em
    ): Response {
        $group = $groupRepo->find($groupId);
        if (!$group) {
            throw $this->createNotFoundException("Group not found.");
        }

        $users = $userRepo->findBy(['group' => $group]);

        if (count($users) < 2) {
            $this->addFlash('error', 'Not enough participants to perform draw.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/draw_names.html.twig', [
            'group' => $group,
            'users' => $users,
        ]);
    }

    /**
     * Assign a receiver to a giver (AJAX)
     * 
     * @Route("/group/{groupId}/draw-assign", name="admin_draw_assign", methods={"POST"})
     */
    public function assignReceiver(
        int $groupId,
        GroupRepository $groupRepo,
        UserRepository $userRepo,
        EntityManagerInterface $em
    ): Response {
        $group = $groupRepo->find($groupId);
        if (!$group) {
            return $this->json(['error' => 'Group not found'], 404);
        }

        $giverId = $_POST['giverId'] ?? null;
        $receiverId = $_POST['receiverId'] ?? null;

        if (!$giverId || !$receiverId) {
            return $this->json(['error' => 'Giver or receiver not provided'], 400);
        }

        $giver = $userRepo->find($giverId);
        $receiver = $userRepo->find($receiverId);

        if (!$giver || $giver->getGroup()->getId() !== $groupId) {
            return $this->json(['error' => 'Invalid giver'], 400);
        }

        if (!$receiver || $receiver->getGroup()->getId() !== $groupId) {
            return $this->json(['error' => 'Invalid receiver'], 400);
        }

        // Assign receiver to giver
        $giver->setAssignedTo($receiver);
        $em->persist($giver);
        $em->flush();

        return $this->json([
            'success' => true,
            'receiverName' => $receiver->getName(),
        ]);
    }

}
