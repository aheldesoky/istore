<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="supplier")
 */
class Supplier
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
    protected $supplier_name;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $supplier_address;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $supplier_phone;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $supplier_email;
    
    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Governorate")
     * @ORM\JoinTable(name="governorate" , 
     *                joinColumns={@ORM\JoinColumn(name="supplier_governorate_id", 
     *                                         referencedColumnName="id")})
     */
    protected $supplier_governorate_id;

    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinTable(name="store" , 
     *                joinColumns={@ORM\JoinColumn(name="supplier_store_id", 
     *                                         referencedColumnName="id")})
     */
    protected $supplier_store_id;
    
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
     * Set supplier_name
     *
     * @param string $supplierName
     * @return Supplier
     */
    public function setSupplierName($supplierName)
    {
        $this->supplier_name = $supplierName;

        return $this;
    }

    /**
     * Get supplier_name
     *
     * @return string 
     */
    public function getSupplierName()
    {
        return $this->supplier_name;
    }

    /**
     * Set supplier_address
     *
     * @param string $supplierAddress
     * @return Supplier
     */
    public function setSupplierAddress($supplierAddress)
    {
        $this->supplier_address = $supplierAddress;

        return $this;
    }

    /**
     * Get supplier_address
     *
     * @return string 
     */
    public function getSupplierAddress()
    {
        return $this->supplier_address;
    }

    /**
     * Set supplier_phone
     *
     * @param string $supplierPhone
     * @return Supplier
     */
    public function setSupplierPhone($supplierPhone)
    {
        $this->supplier_phone = $supplierPhone;

        return $this;
    }

    /**
     * Get supplier_phone
     *
     * @return string 
     */
    public function getSupplierPhone()
    {
        return $this->supplier_phone;
    }

    /**
     * Set supplier_email
     *
     * @param string $supplierEmail
     * @return Supplier
     */
    public function setSupplierEmail($supplierEmail)
    {
        $this->supplier_email = $supplierEmail;

        return $this;
    }

    /**
     * Get supplier_email
     *
     * @return string 
     */
    public function getSupplierEmail()
    {
        return $this->supplier_email;
    }

    /**
     * Set supplier_governorate_id
     *
     * @param string $supplierGovernorateId
     * @return Supplier
     */
    public function setSupplierGovernorateId($supplierGovernorateId)
    {
        $this->supplier_governorate_id = $supplierGovernorateId;

        return $this;
    }

    /**
     * Get supplier_governorate_id
     *
     * @return string 
     */
    public function getSupplierGovernorateId()
    {
        return $this->supplier_governorate_id;
    }

    /**
     * Set supplier_store_id
     *
     * @param integer $supplierStoreId
     * @return Supplier
     */
    public function setSupplierStoreId($supplierStoreId)
    {
        $this->supplier_store_id = $supplierStoreId;

        return $this;
    }

    /**
     * Get supplier_store_id
     *
     * @return integer 
     */
    public function getSupplierStoreId()
    {
        return $this->supplier_store_id;
    }
}
