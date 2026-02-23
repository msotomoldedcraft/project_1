<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Group;
use App\Entity\Wishlist;
use App\Repository\UserRepository;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExchangeController extends AbstractController
{
    /**
     * @Route("/exchange", name="exchange_intro")
     */
    public function intro(UserRepository $userRepo): Response
    {
        $participantCount = $userRepo->count(['verified' => true]);

        return $this->render('exchange/intro.html.twig', [
            'participantCount' => $participantCount,
        ]);
    }

    /**
     * STEP 1 - User info
     * @Route("/exchange/step-1", name="exchange_step_1")
     */
    public function step1(Request $request, SessionInterface $session): Response
    {
        $data = $session->get('exchange', []);

        $form = $this->createFormBuilder($data)
            ->add('name', TextType::class, ['label' => 'Your Name'])
            ->add('email', EmailType::class, ['label' => 'Your Email'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('exchange', array_merge($data, $form->getData()));
            return $this->redirectToRoute('exchange_step_2');
        }

        return $this->render('exchange/step1.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * STEP 2 - Event info
     * @Route("/exchange/step-2", name="exchange_step_2")
     */
    public function step2(Request $request, SessionInterface $session): Response
    {
        $data = $session->get('exchange', []);

        $form = $this->createFormBuilder($data)
            ->add('event', TextType::class, [
                'label' => 'What are you drawing names for?',
                'help' => 'e.g. Christmas Gifts, Valentine’s Day, Birthday Exchange',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('exchange', array_merge($data, $form->getData()));
            return $this->redirectToRoute('exchange_step_3');
        }

        return $this->render('exchange/step2.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * STEP 3 - User wishlist
     * @Route("/exchange/step-3", name="exchange_step_3")
     */
    public function step3(Request $request, SessionInterface $session): Response
    {
        $data = $session->get('exchange', []);
        $data['wishlist'] = $data['wishlist'] ?? [];

        $form = $this->createFormBuilder($data)
            ->add('wishlist', CollectionType::class, [
                'entry_type' => TextType::class,
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
     * STEP 4 GET - Review submission
     * @Route("/exchange/step-4", name="exchange_step_4", methods={"GET"})
     */
    public function step4(SessionInterface $session): Response
    {
        $data = $session->get('exchange');
        if (!$data) return $this->redirectToRoute('exchange_step_1');

        return $this->render('exchange/step4.html.twig', ['data' => $data]);
    }

    /**
     * STEP 4 POST - Save user and wishlist
     * @Route("/exchange/step-4/submit", name="exchange_step_4_submit", methods={"POST"})
     */
    public function step4Submit(
        SessionInterface $session,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        UserRepository $userRepo,
        GroupRepository $groupRepo,
        Environment $twig
    ): Response {
        $data = $session->get('exchange');
        if (!$data) return $this->redirectToRoute('exchange_step_1');

        $name = $data['name'];
        $email = $data['email'];
        $event = $data['event'];
        $wishlistItems = $data['wishlist'] ?? [];

        $user = $userRepo->findOneBy(['email' => $email]) ?? new User();
        $user->setName($name)->setEmail($email);

        $group = $groupRepo->findOneBy(['name' => $event]) ?? new Group();
        if (!$group->getId()) {
            $group->setName($event);
            $em->persist($group);
        }
        $user->setGroup($group);

        $em->persist($user);
        $em->flush();

        // Clear old wishlist and save new
        foreach ($user->getWishlist() as $old) $em->remove($old);
        $em->flush();

        foreach ($wishlistItems as $itemName) {
            if ($itemName !== '') {
                $wishlist = new Wishlist();
                $wishlist->setName($itemName)->setUser($user);
                $em->persist($wishlist);
            }
        }
        $em->flush();

        // Send confirmation email
        $emailMessage = (new TemplatedEmail())
            ->from('yourgmail@gmail.com')
            ->to($user->getEmail())
            ->subject("Your submission for '$event' is confirmed")
            ->htmlTemplate('emails/exchange_confirmation.html.twig')
            ->context([
                'user' => $user,
                'event' => $event,
                'confirmUrl' => $this->generateUrl('exchange_confirm', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL),
                'downloadUrl' => $this->generateUrl('exchange_download', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $mailer->send($emailMessage);

        return $this->redirectToRoute('exchange_step_5_notice', ['token' => $user->getToken()]);
    }

    /**
     * STEP 5 - Notice page
     * @Route("/exchange/step-5/notice/{token}", name="exchange_step_5_notice")
     */
    public function step5Notice(string $token, UserRepository $userRepo): Response
    {
        $user = $userRepo->findOneBy(['token' => $token]);
        if (!$user) throw $this->createNotFoundException('Invalid token.');

        $event = $user->getGroup() ? $user->getGroup()->getName() : 'N/A';
        return $this->render('exchange/step5.html.twig', ['user' => $user, 'event' => $event]);
    }

    /**
     * Resend confirmation email
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
        $emailMessage = (new TemplatedEmail())
            ->from('yourgmail@gmail.com')
            ->to($user->getEmail())
            ->subject("Confirmation: You're in '$event'")
            ->htmlTemplate('emails/exchange_confirmation.html.twig')
            ->context(['user' => $user, 'event' => $event]);

        $mailer->send($emailMessage);
        $this->addFlash('success', 'Confirmation email resent!');

        return $this->redirectToRoute('exchange_step_5_notice', ['token' => $user->getToken()]);
    }

    /**
     * STEP 6 - Home page & wishlist edit/download PDF
     * @Route("/exchange/step-6/{token}", name="exchange_step_6")
     */
    public function step6(string $token, UserRepository $userRepo): Response
    {
        $user = $userRepo->findOneBy(['token' => $token]);
        if (!$user) throw $this->createNotFoundException('Invalid token.');

        $group = $user->getGroup();
        return $this->render('exchange/step6.html.twig', ['user' => $user, 'group' => $group]);
    }

    /**
     * Edit wishlist
     * @Route("/exchange/wishlist/{token}", name="wishlist_edit")
     */
    public function wishlistEdit(string $token, UserRepository $userRepo, EntityManagerInterface $em, Request $request): Response
    {
        $user = $userRepo->findOneBy(['token' => $token]);
        if (!$user) throw $this->createNotFoundException('Invalid token.');

        $wishlistItems = array_map(function($item) {
        return $item->getName();
        }, $user->getWishlist()->toArray());


        $form = $this->createFormBuilder(['wishlist' => $wishlistItems])
            ->add('wishlist', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($user->getWishlist() as $old) $em->remove($old);
            $em->flush();

            foreach ($form->getData()['wishlist'] as $itemName) {
                if ($itemName !== '') {
                    $wishlist = new Wishlist();
                    $wishlist->setName($itemName)->setUser($user);
                    $em->persist($wishlist);
                }
            }
            $em->flush();

            $this->addFlash('success', 'Wishlist updated successfully!');
            return $this->redirectToRoute('exchange_step_6', ['token' => $user->getToken()]);
        }

        return $this->render('exchange/wishlist_edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * Download User Submission as PDF
     * @Route("/exchange/download/{token}", name="exchange_download")
     */
    public function download(string $token, UserRepository $userRepo, Environment $twig): Response
    {
        $user = $userRepo->findOneBy(['token' => $token]);
        if (!$user) throw $this->createNotFoundException('Invalid token.');

        $group = $user->getGroup();
        $wishlist = $user->getWishlist();

        // Render Twig template as HTML
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
                'Content-Disposition' => 'attachment; filename="exchange_submission.pdf"',
            ]
        );
    }

    /**
     * Confirm submission
     * @Route("/exchange/confirm/{token}", name="exchange_confirm")
     */
    public function confirm(string $token, UserRepository $userRepo, EntityManagerInterface $em): Response
    {
        $user = $userRepo->findOneBy(['token' => $token]);
        if (!$user) throw $this->createNotFoundException('Invalid token.');

        if (method_exists($user, 'isConfirmed') && !$user->isConfirmed()) {
            $user->setConfirmed(true);
            $em->flush();
        }

        return $this->redirectToRoute('exchange_step_6', ['token' => $user->getToken()]);
    }
}