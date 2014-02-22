<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store")
 */
class Store
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    protected $store_name;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $store_phone;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $store_address;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $store_logo;
    
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
     * Set store_name
     *
     * @param string $storeName
     * @return Store
     */
    public function setStoreName($storeName)
    {
        $this->store_name = $storeName;

        return $this;
    }

    /**
     * Get store_name
     *
     * @return string 
     */
    public function getStoreName()
    {
        return $this->store_name;
    }

    /**
     * Set store_address
     *
     * @param string $storeAddress
     * @return Store
     */
    public function setStoreAddress($storeAddress)
    {
        $this->store_address = $storeAddress;

        return $this;
    }

    /**
     * Get store_address
     *
     * @return string 
     */
    public function getStoreAddress()
    {
        return $this->store_address;
    }

    /**
     * Set store_phone
     *
     * @param string $storePhone
     * @return Store
     */
    public function setStorePhone($storePhone)
    {
        $this->store_phone = $storePhone;

        return $this;
    }

    /**
     * Get store_phone
     *
     * @return string 
     */
    public function getStorePhone()
    {
        return $this->store_phone;
    }

    /**
     * Set store_logo
     *
     * @param string $storeLogo
     * @return Store
     */
    public function setStoreLogo($storeLogo)
    {
        $this->store_logo = $storeLogo;

        return $this;
    }

    /**
     * Get store_logo
     *
     * @return string 
     */
    public function getStoreLogo()
    {
        return $this->store_logo;
    }
}
