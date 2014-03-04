<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="warranty_item")
 */
class WarrantyItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinTable(name="item" , 
     *                joinColumns={@ORM\JoinColumn(name="warrantyitem_item_id", 
     *                                         referencedColumnName="id")})
     */
    protected $warrantyitem_item_id;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $warrantyitem_date;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $warrantyitem_flag;
    
    function __construct() {
        $this->warrantyitem_date = new \DateTime();
        $this->warrantyitem_flag = 0;
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
     * Set warrantyitem_item_id
     *
     * @param string $warrantyitemItemId
     * @return WarrantyItem
     */
    public function setWarrantyitemItemId($warrantyitemItemId)
    {
        $this->warrantyitem_item_id = $warrantyitemItemId;

        return $this;
    }

    /**
     * Get warrantyitem_item_id
     *
     * @return string 
     */
    public function getWarrantyitemItemId()
    {
        return $this->warrantyitem_item_id;
    }

    /**
     * Set warrantyitem_date
     *
     * @param \DateTime $warrantyitemDate
     * @return WarrantyItem
     */
    public function setWarrantyitemDate($warrantyitemDate)
    {
        $this->warrantyitem_date = $warrantyitemDate;

        return $this;
    }

    /**
     * Get warrantyitem_date
     *
     * @return \DateTime 
     */
    public function getWarrantyitemDate()
    {
        return $this->warrantyitem_date;
    }

    /**
     * Set warrantyitem_flag
     *
     * @param \DateTime $warrantyitemFlag
     * @return WarrantyItem
     */
    public function setWarrantyitemFlag($warrantyitemFlag)
    {
        $this->warrantyitem_flag = $warrantyitemFlag;

        return $this;
    }

    /**
     * Get warrantyitem_flag
     *
     * @return \DateTime 
     */
    public function getWarrantyitemFlag()
    {
        return $this->warrantyitem_flag;
    }
}
