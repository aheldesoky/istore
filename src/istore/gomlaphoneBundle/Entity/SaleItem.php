<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sale_item")
 */
class SaleItem
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
     *                joinColumns={@ORM\JoinColumn(name="saleitem_sale_id", 
     *                                         referencedColumnName="id")})
     */
    protected $saleitem_sale_id;
    
    /**
     * @ORM\Column(nullable=false)
     * @ORM\OneToOne(targetEntity="Item")
     * @ORM\JoinTable(name="item" , 
     *                joinColumns={@ORM\JoinColumn(name="saleitem_item_id", 
     *                                         referencedColumnName="id")})
     */
    protected $saleitem_item_id;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $saleitem_discount;
    

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
     * Set saleitem_sale_id
     *
     * @param string $saleitemSaleId
     * @return SaleItem
     */
    public function setSaleitemSaleId($saleitemSaleId)
    {
        $this->saleitem_sale_id = $saleitemSaleId;

        return $this;
    }

    /**
     * Get saleitem_sale_id
     *
     * @return string 
     */
    public function getSaleitemSaleId()
    {
        return $this->saleitem_sale_id;
    }

    /**
     * Set saleitem_item_id
     *
     * @param string $saleitemItemId
     * @return SaleItem
     */
    public function setSaleitemItemId($saleitemItemId)
    {
        $this->saleitem_item_id = $saleitemItemId;

        return $this;
    }

    /**
     * Get saleitem_item_id
     *
     * @return string 
     */
    public function getSaleitemItemId()
    {
        return $this->saleitem_item_id;
    }

    /**
     * Set saleitem_discount
     *
     * @param integer $saleitemDiscount
     * @return SaleItem
     */
    public function setSaleitemDiscount($saleitemDiscount)
    {
        $this->saleitem_discount = $saleitemDiscount;

        return $this;
    }

    /**
     * Get saleitem_discount
     *
     * @return integer 
     */
    public function getSaleitemDiscount()
    {
        return $this->saleitem_discount;
    }
}
