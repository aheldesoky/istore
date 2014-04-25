<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="color")
 */
class Color
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $color_name;
    
    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinTable(name="store" , 
     *                joinColumns={@ORM\JoinColumn(name="color_store_id", 
     *                                         referencedColumnName="id")})
     */
    protected $color_store_id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set color_name
     *
     * @param string $colorName
     * @return Color
     */
    public function setColorName($colorName)
    {
        $this->color_name = $colorName;

        return $this;
    }

    /**
     * Get color_name
     *
     * @return string 
     */
    public function getColorName()
    {
        return $this->color_name;
    }

    /**
     * Set color_store_id
     *
     * @param integer $colorStoreId
     * @return Color
     */
    public function setColorStoreId($colorStoreId)
    {
        $this->color_store_id = $colorStoreId;

        return $this;
    }

    /**
     * Get color_store_id
     *
     * @return integer 
     */
    public function getColorStoreId()
    {
        return $this->color_store_id;
    }
}
