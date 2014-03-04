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
    protected $bulk_price;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $bulk_quantity;
    
    /**
     * @ORM\ManyToOne(targetEntity="Supplier")
     * @ORM\JoinTable(name="supplier" , 
     *                joinColumns={@ORM\JoinColumn(name="bulk_supplier", 
     *                                         referencedColumnName="id")})
     */
    protected $bulk_supplier;
    
    /**
     * @ORM\Column(type="date", nullable=false)
     */
    protected $bulk_date;
    
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
     * Set bulk_price
     *
     * @param string $bulkPrice
     * @return Bulk
     */
    public function setBulkPrice($bulkPrice)
    {
        $this->bulk_price = $bulkPrice;

        return $this;
    }

    /**
     * Get bulk_price
     *
     * @return string 
     */
    public function getBulkPrice()
    {
        return $this->bulk_price;
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
     * Set bulk_date
     *
     * @param \DateTime $bulkDate
     * @return Bulk
     */
    public function setBulkDate($bulkDate)
    {
        $this->bulk_date = $bulkDate;

        return $this;
    }

    /**
     * Get bulk_date
     *
     * @return \DateTime 
     */
    public function getBulkDate()
    {
        return $this->bulk_date;
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
}
