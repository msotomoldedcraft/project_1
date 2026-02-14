<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="groups")
 */
class Group
{
    /** 
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer") 
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=255) 
     */
    private $name;

    /** 
     * @ORM\OneToMany(targetEntity="User", mappedBy="group", cascade={"persist","remove"}) 
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection { return $this->users; }
    public function addUser(User $user): self {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setGroup($this);
        }
        return $this;
    }
    public function removeUser(User $user): self {
        if ($this->users->removeElement($user)) {
            if ($user->getGroup() === $this) {
                $user->setGroup(null);
            }
        }
        return $this;
    }
}
