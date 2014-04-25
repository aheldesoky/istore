<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="brand")
 */
class Brand
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
    protected $brand_name;
    
    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinTable(name="store" , 
     *                joinColumns={@ORM\JoinColumn(name="brand_store_id", 
     *                                         referencedColumnName="id")})
     */
    protected $brand_store_id;


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
     * Set brand_name
     *
     * @param string $brandName
     * @return Brand
     */
    public function setBrandName($brandName)
    {
        $this->brand_name = $brandName;

        return $this;
    }

    /**
     * Get brand_name
     *
     * @return string 
     */
    public function getBrandName()
    {
        return $this->brand_name;
    }

    /**
     * Set brand_store_id
     *
     * @param integer $brandStoreId
     * @return Brand
     */
    public function setBrandStoreId($brandStoreId)
    {
        $this->brand_store_id = $brandStoreId;

        return $this;
    }

    /**
     * Get brand_store_id
     *
     * @return integer 
     */
    public function getBrandStoreId()
    {
        return $this->brand_store_id;
    }
}
