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
     * @ORM\Column(type="string", nullable=false)
     */
    protected $model_brand;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $model_model;
    
    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
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
     * Set model_brand
     *
     * @param string $modelBrand
     * @return Model
     */
    public function setModelBrand($modelBrand)
    {
        $this->model_brand = $modelBrand;

        return $this;
    }

    /**
     * Get model_brand
     *
     * @return string 
     */
    public function getModelBrand()
    {
        return $this->model_brand;
    }

    /**
     * Set model_model
     *
     * @param string $modelModel
     * @return Model
     */
    public function setModelModel($modelModel)
    {
        $this->model_model = $modelModel;

        return $this;
    }

    /**
     * Get model_model
     *
     * @return string 
     */
    public function getModelModel()
    {
        return $this->model_model;
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
}
