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
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    protected $customer_phone;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customer_notes;
    
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
}
