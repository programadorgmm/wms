<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Natue\Bundle\ZedBundle\Entity\Contracts\HasMultiplierQuantity;

/**
 * ZedProductBarcode
 *
 * @ORM\Table(
 *  name="zed_product_barcode",
 *  indexes={@ORM\Index(name="zed_product_barcode_fk_zed_product", columns={"zed_product"}),
 * @ORM\Index(name="zed_product_barcode_index_barcode", columns={"barcode"})}
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\ZedBundle\Repository\ZedProductBarcodeRepository")
 */
class ZedProductBarcode implements HasMultiplierQuantity
{
    /**
     * @var string
     * @ORM\Column(name="barcode", type="string", length=255, nullable=false)
     * @ORM\Id
     */
    private $barcode;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
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
     *
     * @return ZedProductBarcode
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ZedProductBarcode
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
     *
     * @return ZedProductBarcode
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
     * @param ZedProduct $zedProduct
     *
     * @return ZedProductBarcode
     */
    public function setZedProduct(ZedProduct $zedProduct = null)
    {
        $this->zedProduct = $zedProduct;

        return $this;
    }

    /**
     * Get zedProduct
     *
     * @return ZedProduct
     */
    public function getZedProduct()
    {
        return $this->zedProduct;
    }

    /**
     * @return int
     */
    public function getMultiplier()
    {
        return 1;
    }
}
