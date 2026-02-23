<?php

namespace App\Controller\Admin;

use App\Entity\Wishlist;
use App\Entity\User;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/exchange/admin/user")
 * @IsGranted("ROLE_ADMIN")
 */
class AdminUserController extends AbstractController
{
    /**
     * FORCE ADD USER
     * @Route("/add/{groupId}", name="admin_user_add")
     */
    public function add(
    int $groupId,
    Request $request,
    GroupRepository $groupRepo,
    EntityManagerInterface $em
    ): Response {
        $group = $groupRepo->find($groupId);
        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setName($request->request->get('name'));
            $user->setEmail($request->request->get('email'));
            $user->setGroup($group);
            $user->setRoles(['ROLE_USER']);
            $user->setVerified(true);
            $user->setConfirmed(true);
            $user->setPassword('');

            // 🔹 Handle wishlist items
            $wishlistItems = $request->request->all('wishlist');

            foreach ($wishlistItems as $item) {
                if (trim($item) === '') {
                    continue;
                }

                $wishlist = new Wishlist();
                $wishlist->setName($item);
                $wishlist->setUser($user);

                $em->persist($wishlist);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Participant added with wishlist');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/user/add.html.twig', [
            'group' => $group,
        ]);
    }

    /**
     * EDIT USER
     * @Route("/{id}/edit", name="admin_user_edit")
     */
    public function edit(
    User $user,
    Request $request,
    EntityManagerInterface $em
    ): Response {
        if ($request->isMethod('POST')) {
            // Update basic info
            $user->setName($request->request->get('name'));
            $user->setEmail($request->request->get('email'));

            // --- HANDLE EXISTING WISHLIST ITEMS ---
            $existingWishlist = $request->request->all('wishlist_existing');

            foreach ($user->getWishlist() as $wishlist) {
                $id = $wishlist->getId();

                // If removed
                if (!isset($existingWishlist[$id])) {
                    $em->remove($wishlist);
                    continue;
                }

                // Update value
                $wishlist->setName($existingWishlist[$id]);
            }

            // --- HANDLE NEW WISHLIST ITEMS ---
            $newWishlist = $request->request->all('wishlist_new');

            foreach ($newWishlist as $item) {
                if (trim($item) === '') {
                    continue;
                }

                $wishlist = new Wishlist();
                $wishlist->setName($item);
                $wishlist->setUser($user);
                $em->persist($wishlist);
            }

            $em->flush();

            $this->addFlash('success', 'Participant updated successfully');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * REMOVE USER
     * @Route("/{id}/delete", name="admin_user_delete")
     */
    public function delete(
        User $user,
        EntityManagerInterface $em
    ): Response {
        // Clean draw references
        foreach ($user->getGroup()->getUsers() as $u) {
            if ($u->getAssignedTo() === $user) {
                $u->setAssignedTo(null);
            }
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'User removed');
        return $this->redirectToRoute('admin_dashboard');
    }
}
