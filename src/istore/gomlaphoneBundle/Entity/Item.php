<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="item")
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Bulk")
     * @ORM\JoinTable(name="bulk" , 
     *                joinColumns={@ORM\JoinColumn(name="item_bulk", 
     *                                         referencedColumnName="id")})
     */
    protected $item_bulk;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $item_has_warranty = 0;
    
    /**
     * @ORM\Column(nullable=true)
     * @ORM\ManyToOne(targetEntity="Warranty")
     * @ORM\JoinTable(name="warranty" , 
     *                joinColumns={@ORM\JoinColumn(name="item_warranty_id", 
     *                                         referencedColumnName="id")})
     */
    protected $item_warranty_id;
    
    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    protected $item_serial = null;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $item_status;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $item_color = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $item_notes = null;

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
     * Set item_serial
     *
     * @param string $itemSerial
     * @return Item
     */
    public function setItemSerial($itemSerial)
    {
        $this->item_serial = $itemSerial;

        return $this;
    }

    /**
     * Get item_serial
     *
     * @return string 
     */
    public function getItemSerial()
    {
        return $this->item_serial;
    }

    /**
     * Set item_status
     *
     * @param string $itemStatus
     * @return Item
     */
    public function setItemStatus($itemStatus)
    {
        $this->item_status = $itemStatus;

        return $this;
    }

    /**
     * Get item_status
     *
     * @return string 
     */
    public function getItemStatus()
    {
        return $this->item_status;
    }

    /**
     * Set item_notes
     *
     * @param string $itemNotes
     * @return Item
     */
    public function setItemNotes($itemNotes)
    {
        $this->item_notes = $itemNotes;

        return $this;
    }

    /**
     * Get item_notes
     *
     * @return string 
     */
    public function getItemNotes()
    {
        return $this->item_notes;
    }

    /**
     * Set item_bulk
     *
     * @param \istore\gomlaphoneBundle\Entity\Bulk $itemBulk
     * @return Item
     */
    public function setItemBulk(\istore\gomlaphoneBundle\Entity\Bulk $itemBulk = null)
    {
        $this->item_bulk = $itemBulk;

        return $this;
    }

    /**
     * Get item_bulk
     *
     * @return \istore\gomlaphoneBundle\Entity\Bulk 
     */
    public function getItemBulk()
    {
        return $this->item_bulk;
    }

    /**
     * Set item_color
     *
     * @param string $itemColor
     * @return Item
     */
    public function setItemColor($itemColor)
    {
        $this->item_color = $itemColor;

        return $this;
    }

    /**
     * Get item_color
     *
     * @return string 
     */
    public function getItemColor()
    {
        return $this->item_color;
    }

    /**
     * Set item_has_warranty
     *
     * @param string $itemHasWarranty
     * @return Item
     */
    public function setItemHasWarranty($itemHasWarranty)
    {
        $this->item_has_warranty = $itemHasWarranty;

        return $this;
    }

    /**
     * Get item_has_warranty
     *
     * @return string 
     */
    public function getItemHasWarranty()
    {
        return $this->item_has_warranty;
    }

    /**
     * Set item_warranty_id
     *
     * @param string $itemWarrantyId
     * @return Item
     */
    public function setItemWarrantyId($itemWarrantyId)
    {
        $this->item_warranty_id = $itemWarrantyId;

        return $this;
    }

    /**
     * Get item_warranty_id
     *
     * @return string 
     */
    public function getItemWarrantyId()
    {
        return $this->item_warranty_id;
    }
}
