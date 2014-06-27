<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="customer")
 */
class Customer
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
    protected $customer_name;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $customer_phone;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $customer_type;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customer_notes;
    
    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinTable(name="store" , 
     *                joinColumns={@ORM\JoinColumn(name="customer_store_id", 
     *                                         referencedColumnName="id")})
     */
    protected $customer_store;
    
    /**
     * Get id
     *
     * @return integer 
     */
    
    function __construct() {
        $this->customer_type = 'prepaid';
    }
    
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set customer_name
     *
     * @param string $customerName
     * @return Customer
     */
    public function setCustomerName($customerName)
    {
        $this->customer_name = $customerName;

        return $this;
    }

    /**
     * Get customer_name
     *
     * @return string 
     */
    public function getCustomerName()
    {
        return $this->customer_name;
    }

    /**
     * Set customer_phone
     *
     * @param string $customerPhone
     * @return Customer
     */
    public function setCustomerPhone($customerPhone)
    {
        $this->customer_phone = $customerPhone;

        return $this;
    }

    /**
     * Get customer_phone
     *
     * @return string 
     */
    public function getCustomerPhone()
    {
        return $this->customer_phone;
    }

    /**
     * Set customer_notes
     *
     * @param string $customerNotes
     * @return Customer
     */
    public function setCustomerNotes($customerNotes)
    {
        $this->customer_notes = $customerNotes;

        return $this;
    }

    /**
     * Get customer_notes
     *
     * @return string 
     */
    public function getCustomerNotes()
    {
        return $this->customer_notes;
    }

    /**
     * Set customer_store
     *
     * @param string $customerStore
     * @return Customer
     */
    public function setCustomerStore($customerStore)
    {
        $this->customer_store = $customerStore;

        return $this;
    }

    /**
     * Get customer_store
     *
     * @return string 
     */
    public function getCustomerStore()
    {
        return $this->customer_store;
    }

    /**
     * Set customer_type
     *
     * @param string $customerType
     * @return Customer
     */
    public function setCustomerType($customerType)
    {
        $this->customer_type = $customerType;

        return $this;
    }

    /**
     * Get customer_type
     *
     * @return string 
     */
    public function getCustomerType()
    {
        return $this->customer_type;
    }
}
