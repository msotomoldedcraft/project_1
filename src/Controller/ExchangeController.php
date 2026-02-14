<?php

namespace App\Controller;


use App\Entity\User;
use App\Entity;
use App\Entity\Wishlist;
use App\Repository\UserRepository;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class ExchangeController extends AbstractController
{
    /**
     * STEP 1
     * User info: name + email
     *
     * @Route("/exchange/step-1", name="exchange_step_1")
     */
    public function step1(Request $request, SessionInterface $session): Response
    {
        // Load existing session data if user goes back
        $data = $session->get('exchange', []);

        $form = $this->createFormBuilder($data)
            ->add('name', TextType::class, [
                'label' => 'Your Name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Your Email',
            ])
            ->add('continue', SubmitType::class, [
                'label' => 'Continue',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save step 1 data to session
            $session->set('exchange', array_merge(
                $data,
                $form->getData()
            ));

            return $this->redirectToRoute('exchange_step_2');
        }

        return $this->render('exchange/step1.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * STEP 2
     * Event setup
     *
     * @Route("/exchange/step-2", name="exchange_step_2")
     */
    public function step2(Request $request, SessionInterface $session): Response
    {
        $data = $session->get('exchange', []);

        $form = $this->createFormBuilder($data)
            ->add('event', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'What are you drawing names for?',
                'help' => 'e.g. Christmas Gifts, Valentine’s Day, Birthday Exchange',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('exchange', array_merge(
                $data,
                $form->getData()
            ));

            return $this->redirectToRoute('exchange_step_3');
        }

        return $this->render('exchange/step2.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * STEP 3
     * Organizer wishlist
     *
     * @Route("/exchange/step-3", name="exchange_step_3")
     */
    public function step3(Request $request, SessionInterface $session): Response
    {
        $data = $session->get('exchange', []);

        if (!isset($data['wishlist'])) {
            $data['wishlist'] = [];
        }

        $form = $this->createFormBuilder($data)
            ->add('wishlist', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                'entry_type' => \Symfony\Component\Form\Extension\Core\Type\TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('exchange', $form->getData());
            return $this->redirectToRoute('exchange_step_4');
        }

        return $this->render('exchange/step3.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * STEP 4
     * Confirmation page
     *
     * @Route("/exchange/step-4", name="exchange_step_4")
     */
    public function step4(SessionInterface $session): Response
    {
        $data = $session->get('exchange');

        if (!$data) {
            return $this->redirectToRoute('exchange_step_1');
        }

        return $this->render('exchange/step4.html.twig', [
            'data' => $data,
        ]);
    }
    /**
     * STEP 5
     * Send confirmation email
     *
     * @Route("/exchange/step-5", name="exchange_step_5", methods={"POST"})
     */
    public function step5(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        GroupRepository $groupRepo,
        UserRepository $userRepo
    ): Response {
        $sessionData = $request->getSession()->get('exchange', []);

        $name = $sessionData['name'] ?? null;
        $email = $sessionData['email'] ?? null;
        $event = $sessionData['event'] ?? null;

        if (!$name || !$email || !$event) {
            $this->addFlash('error', 'Missing data. Please go back and complete all fields.');
            return $this->redirectToRoute('exchange_step_1');
        }

        // Check if user already exists
        $user = $userRepo->findOneBy(['email' => $email]);
        if (!$user) {
            $user = new User();
            $user->setName($name)->setEmail($email);
        } else {
            $user->setName($name); // update name in case it changed
        }

        // Assign group
        $group = $groupRepo->findOneBy(['name' => $event]);
        if (!$group) {
            $group = new \App\Entity\Group();
            $group->setName($event);
            $em->persist($group);
            $em->flush();
        }
        $user->setGroup($group);

        $em->persist($user);
        $em->flush();

        // FIXED: Generate absolute landing URL with proper host
        $requestContext = $this->container->get('router')->getContext();
        if (!$requestContext->getHost()) {
            // Set a default host for local dev
            $requestContext->setHost('127.0.0.1:8000');
            $requestContext->setScheme('http');
        }
        $landingUrl = $this->generateUrl(
            'exchange_landing',
            ['token' => $user->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Send confirmation email
        $emailMessage = (new Email())
            ->from('yourgmail@gmail.com')
            ->to($user->getEmail())
            ->subject("You're now a member of the '$event' group!")
            ->html("
                <p>Hi {$user->getName()},</p>
                <p>You are now a member of the group <strong>$event</strong>.</p>
                <p>Go to the group page to make your wish list and draw a name.</p>
                <p><a href='{$landingUrl}' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>Go to Group Page</a></p>
            ");

        $mailer->send($emailMessage);

        // Render notice page
        return $this->render('exchange/step5_notice.html.twig', [
            'user' => $user,
            'event' => $event,
        ]);
    }

    /**
     * STEP 5
     * Resend confirmation email
     *
     * @Route("/exchange/step-5/resend", name="exchange_step_5_resend", methods={"POST"})
     */
    public function resendEmail(Request $request, UserRepository $userRepo, MailerInterface $mailer): Response
    {
        $email = $request->request->get('email');
        $user = $userRepo->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('exchange_step_4');
        }

        $event = $user->getGroup() ? $user->getGroup()->getName() : 'your group';

        // FIXED: ensure host exists for absolute URL
        $requestContext = $this->container->get('router')->getContext();
        if (!$requestContext->getHost()) {
            $requestContext->setHost('127.0.0.1:8000');
            $requestContext->setScheme('http');
        }
        $landingUrl = $this->generateUrl(
            'exchange_landing',
            ['token' => $user->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailMessage = (new Email())
            ->from('yourgmail@gmail.com')
            ->to($user->getEmail())
            ->subject("Reminder: You're now a member of '$event'")
            ->html("
                <p>Hi {$user->getName()},</p>
                <p>This is a reminder that you are now a member of the group <strong>$event</strong>.</p>
                <p><a href='{$landingUrl}' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>Go to Group Page</a></p>
            ");

        $mailer->send($emailMessage);

        $this->addFlash('success', 'Confirmation email resent!');
        return $this->redirectToRoute('exchange_step_5');
    }

    /**
     * GROUP LANDING PAGE
     *
     * @Route("/exchange/landing/{token}", name="exchange_landing")
     */
    public function landing(string $token, UserRepository $userRepo): Response
    {
        $user = $userRepo->findOneBy(['token' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Invalid token.');
        }

        $group = $user->getGroup();

        return $this->render('exchange/landing.html.twig', [
            'user' => $user,
            'group' => $group,
        ]);
    }
    /**
     * @Route("/exchange/wishlist/{token}", name="wishlist_edit")
     */
    public function wishlistEdit(string $token)
    {
        return new Response("Wishlist Edit page for token: $token");
    }

    /**
     * @Route("/exchange/draw-names/{token}", name="draw_names")
     */
    public function drawNames(string $token)
    {
        return new Response("Draw Names page for token: $token");
    }
}