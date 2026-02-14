<?php

namespace App\Service;

use App\Entity\User;

class DrawService
{
    /**
     * Assign users randomly for Secret Santa
     *
     * @param User[] $users
     * @return array [userId => assignedUser]
     */
    public function assign(array $users): array
    {
        $assigned = [];
        $available = $users;

        foreach ($users as $user) {
            // Remove self from options
            $options = array_filter($available, function($u) use ($user) {
                return $u->getId() !== $user->getId();
            });

            // If no options left, reshuffle recursively
            if (count($options) === 0) {
                return $this->assign($users);
            }

            // Pick random user
            $randomKeys = array_keys($options);
            $randomKey = $randomKeys[array_rand($randomKeys)];
            $assignedUser = $options[$randomKey];

            $assigned[$user->getId()] = $assignedUser;

            // Remove assigned user from available
            $available = array_filter($available, function($u) use ($assignedUser) {
                return $u->getId() !== $assignedUser->getId();
            });
        }

        return $assigned;
    }
}
