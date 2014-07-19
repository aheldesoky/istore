<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model")
 */
class Model
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Brand")
     * @ORM\JoinTable(name="brand" , 
     *                joinColumns={@ORM\JoinColumn(name="model_brand", 
     *                                         referencedColumnName="id")})
     */
    protected $model_brand;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $model_name;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $model_number;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $model_serial;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinTable(name="category" , 
     *                joinColumns={@ORM\JoinColumn(name="model_category", 
     *                                         referencedColumnName="id")})
     */
    protected $model_category;
    
    /**
     * @ORM\ManyToOne(targetEntity="Color")
     * @ORM\JoinTable(name="color" , 
     *                joinColumns={@ORM\JoinColumn(name="model_color", 
     *                                         referencedColumnName="id")})
     */
    protected $model_color;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $model_specs;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $model_item_has_serial = true;
    
    
    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinTable(name="store" , 
     *                joinColumns={@ORM\JoinColumn(name="model_store_id", 
     *                                         referencedColumnName="id")})
     */
    protected $model_store_id;
    
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
     * Set model_serial
     *
     * @param string $modelSerial
     * @return Model
     */
    public function setModelSerial($modelSerial)
    {
        $this->model_serial = $modelSerial;

        return $this;
    }

    /**
     * Get model_serial
     *
     * @return string 
     */
    public function getModelSerial()
    {
        return $this->model_serial;
    }

    /**
     * Set model_specs
     *
     * @param string $modelSpecs
     * @return Model
     */
    public function setModelSpecs($modelSpecs)
    {
        $this->model_specs = $modelSpecs;

        return $this;
    }

    /**
     * Get model_specs
     *
     * @return string 
     */
    public function getModelSpecs()
    {
        return $this->model_specs;
    }

    /**
     * Set model_category
     *
     * @param \istore\gomlaphoneBundle\Entity\Category $modelCategory
     * @return Model
     */
    public function setModelCategory(\istore\gomlaphoneBundle\Entity\Category $modelCategory = null)
    {
        $this->model_category = $modelCategory;

        return $this;
    }

    /**
     * Get model_category
     *
     * @return \istore\gomlaphoneBundle\Entity\Category 
     */
    public function getModelCategory()
    {
        return $this->model_category;
    }

    /**
     * Set model_store_id
     *
     * @param integer $modelStoreId
     * @return Model
     */
    public function setModelStoreId($modelStoreId)
    {
        $this->model_store_id = $modelStoreId;

        return $this;
    }

    /**
     * Get model_store_id
     *
     * @return integer 
     */
    public function getModelStoreId()
    {
        return $this->model_store_id;
    }

    /**
     * Set model_item_has_serial
     *
     * @param boolean $modelItemHasSerial
     * @return Model
     */
    public function setModelItemHasSerial($modelItemHasSerial)
    {
        $this->model_item_has_serial = $modelItemHasSerial;

        return $this;
    }

    /**
     * Get model_item_has_serial
     *
     * @return boolean 
     */
    public function getModelItemHasSerial()
    {
        return $this->model_item_has_serial;
    }

    /**
     * Set model_color
     *
     * @param \istore\gomlaphoneBundle\Entity\Color $modelColor
     * @return Model
     */
    public function setModelColor(\istore\gomlaphoneBundle\Entity\Color $modelColor = null)
    {
        $this->model_color = $modelColor;

        return $this;
    }

    /**
     * Get model_color
     *
     * @return \istore\gomlaphoneBundle\Entity\Color 
     */
    public function getModelColor()
    {
        return $this->model_color;
    }

    /**
     * Set model_name
     *
     * @param string $modelName
     * @return Model
     */
    public function setModelName($modelName)
    {
        $this->model_name = $modelName;

        return $this;
    }

    /**
     * Get model_name
     *
     * @return string 
     */
    public function getModelName()
    {
        return $this->model_name;
    }

    /**
     * Set model_number
     *
     * @param string $modelNumber
     * @return Model
     */
    public function setModelNumber($modelNumber)
    {
        $this->model_number = $modelNumber;

        return $this;
    }

    /**
     * Get model_number
     *
     * @return string 
     */
    public function getModelNumber()
    {
        return $this->model_number;
    }

    /**
     * Set model_brand
     *
     * @param \istore\gomlaphoneBundle\Entity\Brand $modelBrand
     * @return Model
     */
    public function setModelBrand(\istore\gomlaphoneBundle\Entity\Brand $modelBrand = null)
    {
        $this->model_brand = $modelBrand;

        return $this;
    }

    /**
     * Get model_brand
     *
     * @return \istore\gomlaphoneBundle\Entity\Brand 
     */
    public function getModelBrand()
    {
        return $this->model_brand;
    }
}
