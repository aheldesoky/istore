<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="payment")
 */
class Payment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Transaction")
     * @ORM\JoinTable(name="transaction" , 
     *                joinColumns={@ORM\JoinColumn(name="payment_transaction", 
     *                                         referencedColumnName="id")})
     */
    protected $payment_transaction;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $payment_date;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $payment_amount;
    
    function __construct() {
        $this->payment_date = new \DateTime();
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
     * Set payment_date
     *
     * @param \DateTime $paymentDate
     * @return Payment
     */
    public function setPaymentDate($paymentDate)
    {
        $this->payment_date = $paymentDate;

        return $this;
    }

    /**
     * Get payment_date
     *
     * @return \DateTime 
     */
    public function getPaymentDate()
    {
        return $this->payment_date;
    }

    /**
     * Set paymant_amount
     *
     * @param integer $paymantAmount
     * @return Payment
     */
    public function setPaymantAmount($paymantAmount)
    {
        $this->paymant_amount = $paymantAmount;

        return $this;
    }

    /**
     * Get paymant_amount
     *
     * @return integer 
     */
    public function getPaymantAmount()
    {
        return $this->paymant_amount;
    }

    /**
     * Set payment_transaction
     *
     * @param \istore\gomlaphoneBundle\Entity\Transaction $paymentTransaction
     * @return Payment
     */
    public function setPaymentTransaction(\istore\gomlaphoneBundle\Entity\Transaction $paymentTransaction = null)
    {
        $this->payment_transaction = $paymentTransaction;

        return $this;
    }

    /**
     * Get payment_transaction
     *
     * @return \istore\gomlaphoneBundle\Entity\Transaction 
     */
    public function getPaymentTransaction()
    {
        return $this->payment_transaction;
    }

    /**
     * Set payment_amount
     *
     * @param integer $paymentAmount
     * @return Payment
     */
    public function setPaymentAmount($paymentAmount)
    {
        $this->payment_amount = $paymentAmount;

        return $this;
    }

    /**
     * Get payment_amount
     *
     * @return integer 
     */
    public function getPaymentAmount()
    {
        return $this->payment_amount;
    }
}
