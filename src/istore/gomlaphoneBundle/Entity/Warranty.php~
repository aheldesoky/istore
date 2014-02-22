<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="warranty")
 */
class Warranty
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
    protected $warranty_name;
    
    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinTable(name="store" , 
     *                joinColumns={@ORM\JoinColumn(name="warranty_store_id", 
     *                                         referencedColumnName="id")})
     */
    protected $warranty_store_id;

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
     * Set warranty_name
     *
     * @param string $warrantyName
     * @return Warranty
     */
    public function setWarrantyName($warrantyName)
    {
        $this->warranty_name = $warrantyName;

        return $this;
    }

    /**
     * Get warranty_name
     *
     * @return string 
     */
    public function getWarrantyName()
    {
        return $this->warranty_name;
    }

    /**
     * Set warranty_store_id
     *
     * @param string $warrantyStoreId
     * @return Warranty
     */
    public function setWarrantyStoreId($warrantyStoreId)
    {
        $this->warranty_store_id = $warrantyStoreId;

        return $this;
    }

    /**
     * Get warranty_store_id
     *
     * @return string 
     */
    public function getWarrantyStoreId()
    {
        return $this->warranty_store_id;
    }
    
    public function __toString()
    {
        return strval($this->id);
    }
}
