<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="bulk")
 */
class Bulk
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Model")
     * @ORM\JoinTable(name="model" , 
     *                joinColumns={@ORM\JoinColumn(name="bulk_model", 
     *                                         referencedColumnName="id")})
     */
    protected $bulk_model;
        
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $bulk_buy_price;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $bulk_sell_price;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $bulk_quantity;
    
    /**
     * @ORM\ManyToOne(targetEntity="Transaction")
     * @ORM\JoinTable(name="transaction" , 
     *                joinColumns={@ORM\JoinColumn(name="bulk_transaction", 
     *                                         referencedColumnName="id")})
     */
    protected $bulk_transaction;
    
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
     * Set bulk_buy_price
     *
     * @param string $bulkBuyPrice
     * @return Bulk
     */
    public function setBulkBuyPrice($bulkBuyPrice)
    {
        $this->bulk_buy_price = $bulkBuyPrice;

        return $this;
    }

    /**
     * Get bulk_buy_price
     *
     * @return string 
     */
    public function getBulkBuyPrice()
    {
        return $this->bulk_buy_price;
    }

    /**
     * Set bulk_quantity
     *
     * @param integer $bulkQuantity
     * @return Bulk
     */
    public function setBulkQuantity($bulkQuantity)
    {
        $this->bulk_quantity = $bulkQuantity;

        return $this;
    }

    /**
     * Get bulk_quantity
     *
     * @return integer 
     */
    public function getBulkQuantity()
    {
        return $this->bulk_quantity;
    }

    /**
     * Set bulk_model
     *
     * @param \istore\gomlaphoneBundle\Entity\Model $bulkModel
     * @return Bulk
     */
    public function setBulkModel(\istore\gomlaphoneBundle\Entity\Model $bulkModel = null)
    {
        $this->bulk_model = $bulkModel;

        return $this;
    }

    /**
     * Get bulk_model
     *
     * @return \istore\gomlaphoneBundle\Entity\Model 
     */
    public function getBulkModel()
    {
        return $this->bulk_model;
    }

    /**
     * Set bulk_supplier
     *
     * @param \istore\gomlaphoneBundle\Entity\Supplier $bulkSupplier
     * @return Bulk
     */
    public function setBulkSupplier(\istore\gomlaphoneBundle\Entity\Supplier $bulkSupplier = null)
    {
        $this->bulk_supplier = $bulkSupplier;

        return $this;
    }

    /**
     * Get bulk_supplier
     *
     * @return \istore\gomlaphoneBundle\Entity\Supplier 
     */
    public function getBulkSupplier()
    {
        return $this->bulk_supplier;
    }

    /**
     * Set bulk_item_has_serial
     *
     * @param boolean $bulkItemHasSerial
     * @return Bulk
     */
    public function setBulkItemHasSerial($bulkItemHasSerial)
    {
        $this->bulk_item_has_serial = $bulkItemHasSerial;

        return $this;
    }

    /**
     * Get bulk_item_has_serial
     *
     * @return boolean 
     */
    public function getBulkItemHasSerial()
    {
        return $this->bulk_item_has_serial;
    }

    /**
     * Set bulk_transaction
     *
     * @param \istore\gomlaphoneBundle\Entity\Transaction $bulkTransaction
     * @return Bulk
     */
    public function setBulkTransaction(\istore\gomlaphoneBundle\Entity\Transaction $bulkTransaction = null)
    {
        $this->bulk_transaction = $bulkTransaction;

        return $this;
    }

    /**
     * Get bulk_transaction
     *
     * @return \istore\gomlaphoneBundle\Entity\Transaction 
     */
    public function getBulkTransaction()
    {
        return $this->bulk_transaction;
    }

    /**
     * Set bulk_sell_price
     *
     * @param string $bulkSellPrice
     * @return Bulk
     */
    public function setBulkSellPrice($bulkSellPrice)
    {
        $this->bulk_sell_price = $bulkSellPrice;

        return $this;
    }

    /**
     * Get bulk_sell_price
     *
     * @return string 
     */
    public function getBulkSellPrice()
    {
        return $this->bulk_sell_price;
    }
}
