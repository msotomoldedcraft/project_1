<?php

namespace App\Controller\Admin;

use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/exchange/admin", name="admin_exchange_")
 */
class AdminExchangeController extends AbstractController
{
    /**
     * List all participants of a group
     * @Route("/participants/{groupId}", name="participants")
     */
    public function participants(int $groupId, GroupRepository $groupRepo, UserRepository $userRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $group = $groupRepo->find($groupId);
        if (!$group) {
            throw $this->createNotFoundException('Group not found.');
        }

        $users = $userRepo->findBy(['group' => $group]);

        return $this->render('admin/exchange/participants.html.twig', [
            'group' => $group,
            'users' => $users,
        ]);
    }

    /**
     * Download individual participant PDF
     * @Route("/download/{userId}", name="download")
     */
    public function download(int $userId, UserRepository $userRepo, Environment $twig): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepo->find($userId);
        if (!$user) throw $this->createNotFoundException('User not found.');

        $group = $user->getGroup();
        $wishlist = $user->getWishlist();

        $html = $twig->render('exchange/pdf_submission.html.twig', [
            'user' => $user,
            'group' => $group,
            'wishlist' => $wishlist,
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
                'Content-Disposition' => 'attachment; filename="participant_'.$user->getId().'.pdf"',
            ]
        );
    }
}
