<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Group;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminDashboardController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/exchange/admin/dashboard", name="admin_dashboard")
     */
    public function index(UserRepository $userRepo, GroupRepository $groupRepo): Response
    {
        $groups = $groupRepo->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'groups' => $groups,
        ]);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/exchange/admin/group/create", name="admin_group_create")
     */
    public function createGroup(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $groupName = $request->request->get('group_name');

            if (!$groupName) {
                $this->addFlash('error', 'Group name is required.');
                return $this->redirectToRoute('admin_group_create');
            }

            $group = new Group();
            $group->setName($groupName);

            $em->persist($group);
            $em->flush();

            $this->addFlash('success', 'Group created successfully!');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/group_create.html.twig');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/exchange/admin/group/{groupId}/delete", name="admin_group_delete")
     */
    public function deleteGroup(int $groupId, GroupRepository $groupRepo, EntityManagerInterface $em): RedirectResponse
    {
        $group = $groupRepo->find($groupId);
        if (!$group) {
            $this->addFlash('error', 'Group not found.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $em->remove($group);
        $em->flush();

        $this->addFlash('success', "Group '{$group->getName()}' has been removed.");
        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/exchange/admin/group/{groupId}/reset-draw", name="admin_draw_reset")
     */
    public function resetDraw(int $groupId, GroupRepository $groupRepo, EntityManagerInterface $em): RedirectResponse
    {
        $group = $groupRepo->find($groupId);
        if (!$group) {
            $this->addFlash('error', 'Group not found.');
            return $this->redirectToRoute('admin_dashboard');
        }

        foreach ($group->getUsers() as $user) {
            $user->setAssignedTo(null);
            $em->persist($user);
        }
        $em->flush();

        $this->addFlash('success', "Draw for '{$group->getName()}' has been reset.");
        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/exchange/admin/group/{groupId}/results", name="admin_view_results")
     */
    public function viewResults(
        int $groupId,
        UserRepository $userRepo,
        GroupRepository $groupRepo
    ): Response {
        $group = $groupRepo->find($groupId);
        if (!$group) {
            $this->addFlash('error', 'Group not found.');
            return $this->redirectToRoute('admin_dashboard');
        }

        // Eager load assignedTo so Twig can display it
        $users = $userRepo->createQueryBuilder('u')
            ->leftJoin('u.assignedTo', 'r')
            ->addSelect('r')
            ->where('u.group = :group')
            ->setParameter('group', $group)
            ->getQuery()
            ->getResult();

        return $this->render('admin/group_results.html.twig', [
            'group' => $group,
            'users' => $users, // pass the users with assignedTo loaded
        ]);
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/exchange/admin/user/{userId}/download", name="admin_download_submission")
     */
    public function downloadSubmission(
        int $userId,
        UserRepository $userRepo,
        Environment $twig
    ): Response {
        $user = $userRepo->find($userId);
        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        $group = $user->getGroup();

        // Render PDF from Twig template
        $html = $twig->render('exchange/pdf_submission.html.twig', [
            'user' => $user,
            'group' => $group,
            'wishlist' => $user->getWishlist(),
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="submission_'.$user->getName().'.pdf"',
            ]
        );
    }
}
