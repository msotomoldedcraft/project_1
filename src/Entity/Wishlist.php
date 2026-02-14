<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="wishlist")
 */
class Wishlist
{
    /** 
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer") 
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true) 
     */
    private $name;

    /** 
     * @ORM\ManyToOne(targetEntity="User", inversedBy="wishlist") 
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    // --- ID ---
    public function getId(): ?int
    {
        return $this->id;
    }

    // --- Name ---
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    // --- User ---
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
