<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="postpaid")
 */
class Postpaid
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Sale")
     * @ORM\JoinTable(name="sale" , 
     *                joinColumns={@ORM\JoinColumn(name="postpaid_sale_id", 
     *                                         referencedColumnName="id")})
     */
    protected $postpaid_sale_id;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $postpaid_amount;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $postpaid_date;

    function __construct() {
        $this->postpaid_date = new \DateTime();
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
     * Set postpaid_sale_id
     *
     * @param string $postpaidSaleId
     * @return Postpaid
     */
    public function setPostpaidSaleId($postpaidSaleId)
    {
        $this->postpaid_sale_id = $postpaidSaleId;

        return $this;
    }

    /**
     * Get postpaid_sale_id
     *
     * @return string 
     */
    public function getPostpaidSaleId()
    {
        return $this->postpaid_sale_id;
    }

    /**
     * Set postpaid_amount
     *
     * @param integer $postpaidAmount
     * @return Postpaid
     */
    public function setPostpaidAmount($postpaidAmount)
    {
        $this->postpaid_amount = $postpaidAmount;

        return $this;
    }

    /**
     * Get postpaid_amount
     *
     * @return integer 
     */
    public function getPostpaidAmount()
    {
        return $this->postpaid_amount;
    }

    /**
     * Set postpaid_date
     *
     * @param \DateTime $postpaidDate
     * @return Postpaid
     */
    public function setPostpaidDate($postpaidDate)
    {
        $this->postpaid_date = $postpaidDate;

        return $this;
    }

    /**
     * Get postpaid_date
     *
     * @return \DateTime 
     */
    public function getPostpaidDate()
    {
        return $this->postpaid_date;
    }
}
