<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    /** 
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer") 
     */
    private $id;

    /** @ORM\Column(type="string", length=255) */
    private $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /** 
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="users") 
     * @ORM\JoinColumn(nullable=true)  
     */
    private $group;

    /** 
     * @ORM\OneToMany(
     *     targetEntity="Wishlist",
     *     mappedBy="user",
     *     cascade={"persist","remove"}
     * )
     */
    private $wishlist;

    /**
     * ✅ ASSIGNED RECEIVER (DRAW RESULT)
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $assignedTo;

    /** @ORM\Column(type="string", length=6, nullable=true) */
    private $verificationCode;

    /** @ORM\Column(type="boolean") */
    private $verified = false;

    /** @ORM\Column(type="string", length=32, unique=true) */
    private $token;

    /** @ORM\Column(type="string", nullable=true) */
    private $password;

    /**
     * @ORM\Column(type="text")
     */
    private $roles;

    /** @ORM\Column(type="boolean") */
    private $confirmed = false;

    public function __construct()
    {
        $this->wishlist = new ArrayCollection();
        $this->token = bin2hex(random_bytes(16));
        $this->roles = json_encode([]);
    }

    /* =====================
       BASIC GETTERS/SETTERS
       ===================== */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getWishlist(): Collection
    {
        return $this->wishlist;
    }

    public function addWishlist(Wishlist $wishlist): self
    {
        if (!$this->wishlist->contains($wishlist)) {
            $this->wishlist[] = $wishlist;
            $wishlist->setUser($this);
        }
        return $this;
    }

    public function removeWishlist(Wishlist $wishlist): self
    {
        if ($this->wishlist->removeElement($wishlist)) {
            if ($wishlist->getUser() === $this) {
                $wishlist->setUser(null);
            }
        }
        return $this;
    }

    /**
     * ✅ DRAW ASSIGNMENT METHODS
     */
    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $user): self
    {
        $this->assignedTo = $user;
        return $this;
    }

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(?string $code): self
    {
        $this->verificationCode = $code;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $status): self
    {
        $this->verified = $status;
        return $this;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $status): self
    {
        $this->confirmed = $status;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /* =====================
       SECURITY METHODS
       ===================== */

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles ? json_decode($this->roles, true) : [];
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = json_encode($roles);
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // no-op
    }

    public function __toString(): string
    {
        return $this->name ?? 'Unnamed User';
    }
}
