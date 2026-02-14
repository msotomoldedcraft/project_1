<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
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
     * @ORM\Column(type="string", length=191, unique=true) 
     */
    private $email;

    /** 
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="users") 
     * @ORM\JoinColumn(nullable=true)  <!-- FIXED: allow null temporarily -->
     */
    private $group;

    /** 
     * @ORM\OneToMany(targetEntity="Wishlist", mappedBy="user", cascade={"persist","remove"}) 
     */
    private $wishlist;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true) 
     */
    private $assignedTo;

    /** 
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $verificationCode;

    /** 
     * @ORM\Column(type="boolean") 
     */
    private $verified = false;

    /** 
     * @ORM\Column(type="string", length=32, unique=true) 
     */
    private $token;

    public function __construct()
    {
        $this->wishlist = new ArrayCollection();
        $this->token = bin2hex(random_bytes(16)); // auto-generate token
    }

    // --- ID ---
    public function getId(): ?int { return $this->id; }

    // --- Name ---
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    // --- Email ---
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    // --- Group ---
    public function getGroup(): ?Group { return $this->group; }
    public function setGroup(?Group $group): self { $this->group = $group; return $this; }

    // --- Wishlist ---
    /**
     * @return Collection|Wishlist[]
     */
    public function getWishlist(): Collection { return $this->wishlist; }
    public function addWishlist(Wishlist $wishlist): self {
        if (!$this->wishlist->contains($wishlist)) {
            $this->wishlist[] = $wishlist;
            $wishlist->setUser($this);
        }
        return $this;
    }
    public function removeWishlist(Wishlist $wishlist): self {
        if ($this->wishlist->removeElement($wishlist)) {
            if ($wishlist->getUser() === $this) {
                $wishlist->setUser(null);
            }
        }
        return $this;
    }

    // --- Assigned To ---
    public function getAssignedTo(): ?string { return $this->assignedTo; }
    public function setAssignedTo(?string $assignedTo): self { $this->assignedTo = $assignedTo; return $this; }

    // --- Verification Code ---
    public function getVerificationCode(): ?string { return $this->verificationCode; }
    public function setVerificationCode(?string $code): self { $this->verificationCode = $code; return $this; }

    // --- Verified ---
    public function isVerified(): bool { return $this->verified; }
    public function setVerified(bool $status): self { $this->verified = $status; return $this; }

    // --- Token ---
    public function getToken(): string { return $this->token; }
    public function setToken(string $token): self { $this->token = $token; return $this; }

    // --- ToString for easier display in Twig or ChoiceType ---
    public function __toString(): string
    {
        return $this->name ?? 'Unnamed User';
    }
}
