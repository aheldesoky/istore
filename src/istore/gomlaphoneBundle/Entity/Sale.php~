<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sale")
 */
class Sale
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Bulk")
     * @ORM\JoinTable(name="bulk" , 
     *                joinColumns={@ORM\JoinColumn(name="item_bulk", 
     *                                         referencedColumnName="id")})
     */
    protected $sale_customer_id;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $sale_date;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $sale_discount;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $sale_discount_confirmed;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sale_total_price;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sale_total_paid;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sale_total_count;
    
    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinTable(name="store" , 
     *                joinColumns={@ORM\JoinColumn(name="sale_store_id", 
     *                                         referencedColumnName="id")})
     */
    protected $sale_store_id;
    
    function __construct() {
        $this->sale_date = new \DateTime();
        $this->sale_discount_confirmed = false;
        $this->sale_total_price = 0;
    }

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
     * Set sale_customer_id
     *
     * @param integer $saleCustomerId
     * @return Sale
     */
    public function setSaleCustomerId($saleCustomerId)
    {
        $this->sale_customer_id = $saleCustomerId;

        return $this;
    }

    /**
     * Get sale_customer_id
     *
     * @return integer 
     */
    public function getSaleCustomerId()
    {
        return $this->sale_customer_id;
    }
    
    /**
     * Set sale_date
     *
     * @param \DateTime $saleDate
     * @return Sale
     */
    public function setSaleDate($saleDate)
    {
        $this->sale_date = $saleDate;

        return $this;
    }

    /**
     * Get sale_date
     *
     * @return \DateTime 
     */
    public function getSaleDate()
    {
        return $this->sale_date;
    }

    /**
     * Set sale_store_id
     *
     * @param string $saleStoreId
     * @return Sale
     */
    public function setSaleStoreId($saleStoreId)
    {
        $this->sale_store_id = $saleStoreId;

        return $this;
    }

    /**
     * Get sale_store_id
     *
     * @return string 
     */
    public function getSaleStoreId()
    {
        return $this->sale_store_id;
    }

    /**
     * Set sale_discount
     *
     * @param integer $saleDiscount
     * @return Sale
     */
    public function setSaleDiscount($saleDiscount)
    {
        $this->sale_discount = $saleDiscount;

        return $this;
    }

    /**
     * Get sale_discount
     *
     * @return integer 
     */
    public function getSaleDiscount()
    {
        return $this->sale_discount;
    }

    /**
     * Set sale_discount_confirmed
     *
     * @param boolean $saleDiscountConfirmed
     * @return Sale
     */
    public function setSaleDiscountConfirmed($saleDiscountConfirmed)
    {
        $this->sale_discount_confirmed = $saleDiscountConfirmed;

        return $this;
    }

    /**
     * Get sale_discount_confirmed
     *
     * @return boolean 
     */
    public function getSaleDiscountConfirmed()
    {
        return $this->sale_discount_confirmed;
    }

    /**
     * Set sale_total_price
     *
     * @param integer $saleTotalPrice
     * @return Sale
     */
    public function setSaleTotalPrice($saleTotalPrice)
    {
        $this->sale_total_price = $saleTotalPrice;

        return $this;
    }

    /**
     * Get sale_total_price
     *
     * @return integer 
     */
    public function getSaleTotalPrice()
    {
        return $this->sale_total_price;
    }

    /**
     * Set sale_total_count
     *
     * @param integer $saleTotalCount
     * @return Sale
     */
    public function setSaleTotalCount($saleTotalCount)
    {
        $this->sale_total_count = $saleTotalCount;

        return $this;
    }

    /**
     * Get sale_total_count
     *
     * @return integer 
     */
    public function getSaleTotalCount()
    {
        return $this->sale_total_count;
    }

    /**
     * Set sale_total_paid
     *
     * @param integer $saleTotalPaid
     * @return Sale
     */
    public function setSaleTotalPaid($saleTotalPaid)
    {
        $this->sale_total_paid = $saleTotalPaid;

        return $this;
    }

    /**
     * Get sale_total_paid
     *
     * @return integer 
     */
    public function getSaleTotalPaid()
    {
        return $this->sale_total_paid;
    }
}
