<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class UserDashboardController extends AbstractController
{
    /**
     * @Route("/user/dashboard", name="user_dashboard")
     */
    public function index(): Response
    {
        return $this->render('user/dashboard.html.twig');
    }
}
