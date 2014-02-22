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
    protected $customer_fname;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $customer_lname;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $customer_address;
    
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
     * Set customer_fname
     *
     * @param string $customerFname
     * @return Customer
     */
    public function setCustomerFname($customerFname)
    {
        $this->customer_fname = $customerFname;

        return $this;
    }

    /**
     * Get customer_fname
     *
     * @return string 
     */
    public function getCustomerFname()
    {
        return $this->customer_fname;
    }

    /**
     * Set customer_lname
     *
     * @param string $customerLname
     * @return Customer
     */
    public function setCustomerLname($customerLname)
    {
        $this->customer_lname = $customerLname;

        return $this;
    }

    /**
     * Get customer_lname
     *
     * @return string 
     */
    public function getCustomerLname()
    {
        return $this->customer_lname;
    }

    /**
     * Set customer_address
     *
     * @param string $customerAddress
     * @return Customer
     */
    public function setCustomerAddress($customerAddress)
    {
        $this->customer_address = $customerAddress;

        return $this;
    }

    /**
     * Get customer_address
     *
     * @return string 
     */
    public function getCustomerAddress()
    {
        return $this->customer_address;
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
