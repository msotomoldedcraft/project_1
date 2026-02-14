<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class VerificationController extends AbstractController
{
    #[Route('/verify/{token}', name: 'user_verify')]
    public function verify(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $user = $em->getRepository(User::class)->findOneBy(['token' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        // If user has no verification code yet, generate and send
        if (!$user->getVerificationCode()) {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // 6-digit code
            $user->setVerificationCode($code);
            $em->flush();

            // Send email
            $email = (new Email())
                ->from('noreply@example.com')
                ->to($user->getEmail())
                ->subject('Your Secret Santa Verification Code')
                ->text("Your verification code is: $code");

            $mailer->send($email);
        }

        // Build verification form
        $form = $this->createFormBuilder()
            ->add('code', TextType::class, [
                'label' => 'Enter verification code sent to your email'
            ])
            ->add('verify', SubmitType::class, ['label' => 'Verify'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $enteredCode = $form->get('code')->getData();

            if ($enteredCode === $user->getVerificationCode()) {
                $user->setVerified(true);
                $em->flush();

                $this->addFlash('success', 'Email verified successfully!');

                return $this->redirectToRoute('exchange_landing', [
                    'token' => $user->getToken()
                ]);
            } else {
                $this->addFlash('error', 'Incorrect verification code.');
            }
        }

        return $this->render('verification/verify.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
