<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Natue\Bundle\ZedBundle\Entity\Contracts\HasMultiplierQuantity;

/**
 * ZedSupplierSku
 *
 * @ORM\Table(
 *  name="zed_supplier_sku",
 *  indexes={@ORM\Index(name="zed_supplier_sku_fk_zed_product", columns={"zed_product"}),
 * @ORM\Index(name="zed_supplier_sku_index_sku", columns={"sku"})}
 * )
 * @ORM\Entity(repositoryClass="Natue\Bundle\ZedBundle\Repository\ZedSupplierSkuRepository")
 */
class ZedSupplierSku implements HasMultiplierQuantity
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    
    protected $id;
    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255)
     */
    private $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="multiplier", type="string", length=255, nullable=true)
     */
    private $multiplier;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var ZedProduct
     * @ORM\ManyToOne(targetEntity="ZedProduct")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_product", referencedColumnName="id")
     * })
     */
    private $zedProduct;

    /**
     * Set sku
     *
     * @param string $sku
     * @return ZedSupplierSku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get sku
     *
     * @return string 
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set multiplier
     *
     * @param string $multiplier
     * @return ZedSupplierSku
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    /**
     * Get multiplier
     *
     * @return string 
     */
    public function getMultiplier()
    {
        return $this->multiplier;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ZedSupplierSku
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return ZedSupplierSku
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set zedProduct
     *
     * @param \Natue\Bundle\ZedBundle\Entity\ZedProduct $zedProduct
     * @return ZedSupplierSku
     */
    public function setZedProduct(\Natue\Bundle\ZedBundle\Entity\ZedProduct $zedProduct = null)
    {
        $this->zedProduct = $zedProduct;

        return $this;
    }

    /**
     * Get zedProduct
     *
     * @return \Natue\Bundle\ZedBundle\Entity\ZedProduct 
     */
    public function getZedProduct()
    {
        return $this->zedProduct;
    }
}
