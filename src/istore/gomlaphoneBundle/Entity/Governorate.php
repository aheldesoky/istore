<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="governorate")
 */
class Governorate
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
    protected $governorate_name;
    

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
     * Set governorate_name
     *
     * @param string $governorateName
     * @return Governorate
     */
    public function setGovernorateName($governorateName)
    {
        $this->governorate_name = $governorateName;

        return $this;
    }

    /**
     * Get governorate_name
     *
     * @return string 
     */
    public function getGovernorateName()
    {
        return $this->governorate_name;
    }
}
