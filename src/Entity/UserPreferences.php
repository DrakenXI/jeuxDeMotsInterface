<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserPreferencesRepository")
 */
class UserPreferences
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="preferences", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $max_display;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $relations_not_display = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $display_order;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getMaxDisplay(): ?int
    {
        return $this->max_display;
    }

    public function setMaxDisplay(int $max_display): self
    {
        $this->max_display = $max_display;

        return $this;
    }

    public function getRelationsNotDisplay(): ?array
    {
        return $this->relations_not_display;
    }

    public function setRelationsNotDisplay(?array $relations_not_display): self
    {
        $this->relations_not_display = $relations_not_display;

        return $this;
    }

    public function getDisplayOrder(): ?string
    {
        return $this->display_order;
    }

    public function setDisplayOrder(string $display_order): self
    {
        $this->display_order = $display_order;

        return $this;
    }
}
