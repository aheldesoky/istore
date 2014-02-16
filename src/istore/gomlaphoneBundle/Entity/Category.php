<?php

namespace istore\gomlaphoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="category")
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    protected $category_name;
    
    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinTable(name="store" , 
     *                joinColumns={@ORM\JoinColumn(name="category_store_id", 
     *                                         referencedColumnName="id")})
     */
    protected $category_store_id;


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
     * Set category_name
     *
     * @param string $categoryName
     * @return Category
     */
    public function setCategoryName($categoryName)
    {
        $this->category_name = $categoryName;

        return $this;
    }

    /**
     * Get category_name
     *
     * @return string 
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }

    /**
     * Set category_user
     *
     * @param integer $categoryUser
     * @return Category
     */
    public function setCategoryUser($categoryUser)
    {
        $this->category_user = $categoryUser;

        return $this;
    }

    /**
     * Get category_user
     *
     * @return integer 
     */
    public function getCategoryUser()
    {
        return $this->category_user;
    }

    /**
     * Set category_store_id
     *
     * @param integer $categoryStoreId
     * @return Category
     */
    public function setCategoryStoreId($categoryStoreId)
    {
        $this->category_store_id = $categoryStoreId;

        return $this;
    }

    /**
     * Get category_store_id
     *
     * @return integer 
     */
    public function getCategoryStoreId()
    {
        return $this->category_store_id;
    }
}
