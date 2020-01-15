<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


class UserPreferences
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy("id"))
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $max_display;

    /**
     * @ORM\Column(type="json")
     */
    private $relations_not_display = [];

    /**
     * @ORM\Column(type="string")
     */
    private $display_order;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getMaxDisplay()
    {
        return $this->max_display;
    }

    /**
     * @param mixed $max_display
     */
    public function setMaxDisplay($max_display): void
    {
        $this->max_display = $max_display;
    }

    /**
     * @return array
     */
    public function getRelationsNotDisplay(): array
    {
        return $this->relations_not_display;
    }

    /**
     * @param array $relations_not_display
     */
    public function setRelationsNotDisplay(array $relations_not_display): void
    {
        $this->relations_not_display = $relations_not_display;
    }

    /**
     * @return mixed
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param mixed $display_order
     */
    public function setDisplayOrder($display_order): void
    {
        $this->display_order = $display_order;
    }

}