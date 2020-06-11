<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Natue\Bundle\ZedBundle\Entity\Contracts\HasMultiplierQuantity;

/**
 * ZedSupplierBarcode
 *
 * @ORM\Table(
 *  name="zed_supplier_barcode",
 *  indexes={@ORM\Index(name="zed_supplier_shipping_unit_barcode_fk_zed_product", columns={"zed_product"}),
 * @ORM\Index(name="zed_supplier_barcode_index_barcode", columns={"barcode"})}
 * )
 * @ORM\Entity(repositoryClass="Natue\Bundle\ZedBundle\Repository\ZedSupplierBarcodeRepository")
 */
class ZedSupplierBarcode implements HasMultiplierQuantity
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
     * @ORM\Column(name="barcode", type="string", length=255)
     */
    private $barcode;

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
     * Set barcode
     *
     * @param string $barcode
     * @return ZedSupplierShippingUnitBarcode
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * Get barcode
     *
     * @return string 
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * Set multiplier
     *
     * @param string $multiplier
     * @return ZedSupplierShippingUnitBarcode
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
     * @return ZedSupplierShippingUnitBarcode
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
     * @return ZedSupplierShippingUnitBarcode
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
     * @return ZedSupplierShippingUnitBarcode
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
