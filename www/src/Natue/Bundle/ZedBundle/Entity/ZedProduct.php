<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZedProduct
 *
 * @ORM\Table(
 *  name="zed_product",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="sku_UNIQUE", columns={"sku"})},
 *  indexes={@ORM\Index(name="zed_product_fk_zed_supplier", columns={"zed_supplier"})}
 * )
 *
 * @ORM\Entity
 */
class ZedProduct
{
    /**
     * @var string
     * @ORM\Column(name="sku", type="string", length=255, nullable=false)
     */
    private $sku;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="short_name", type="string", length=255, nullable=true)
     */
    private $shortName;

    /**
     * @var string
     * @ORM\Column(name="brand", type="string", length=255, nullable=true)
     */
    private $brand;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(name="attribute_set", type="string", length=255, nullable=false)
     */
    private $attributeSet;

    /**
     * @var float
     * @ORM\Column(name="gross_weight", type="decimal", precision=15, scale=5, nullable=true)
     */
    private $grossWeight;

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
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @var ZedSupplier
     * @ORM\ManyToOne(targetEntity="ZedSupplier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_supplier", referencedColumnName="id")
     * })
     */
    private $zedSupplier;

    /**
     * @var string
     * @ORM\Column(name="pis_cofins", type="string", length=255, nullable=true)
     */
    private $pisCofins;

    /**
     * @var bool
     * @ORM\Column(name="is_book", type="boolean", options={"default": 0})
     */
    private $isBook;

    /**
     * @var float
     * @ORM\Column(name="estimated_total_taxes_aliquot", type="float", scale=2, nullable=true)
     */
    private $estimatedTotalTaxesAliquot;

    /**
     * @var int
     * @ORM\Column(name="ncm", type="integer", nullable=true)
     */
    private $ncm;

    /**
     * @var string
     * @ORM\Column(name="cest", type="string", length=7, nullable=true)
     */
    private $cest;

    /**
     * @var bool
     * @ORM\Column(name="is_st", type="boolean", nullable=true, options={"default":0})
     */
    private $isSt;

    /**
     * @var float
     * @ORM\Column(name="markup", type="decimal", precision=15, scale=5, nullable=true, options={"default":0})
     */
    private $markup;

    /**
     * @var integer
     * @ORM\Column(name="cost", type="integer", nullable=true, options={"default":0})
     */
    private $cost;

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return ZedProduct
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
     * Set name
     *
     * @param string $name
     *
     * @return ZedProduct
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set shortName
     *
     * @param string $shortName
     *
     * @return ZedProduct
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Get shortName
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Set brand
     *
     * @param string $brand
     *
     * @return ZedProduct
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ZedProduct
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set attributeSet
     *
     * @param string $attributeSet
     *
     * @return ZedProduct
     */
    public function setAttributeSet($attributeSet)
    {
        $this->attributeSet = $attributeSet;

        return $this;
    }

    /**
     * Get attributeSet
     *
     * @return string
     */
    public function getAttributeSet()
    {
        return $this->attributeSet;
    }

    /**
     * Set grossWeight
     *
     * @param float $grossWeight
     *
     * @return ZedProduct
     */
    public function setGrossWeight($grossWeight)
    {
        $this->grossWeight = $grossWeight;

        return $this;
    }

    /**
     * Get grossWeight
     *
     * @return float
     */
    public function getGrossWeight()
    {
        return $this->grossWeight;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ZedProduct
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
     * @return ZedProduct
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
     * Set id
     *
     * @param integer $id
     *
     * @return ZedProduct
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set zedSupplier
     *
     * @param ZedSupplier $zedSupplier
     *
     * @return ZedProduct
     */
    public function setZedSupplier(ZedSupplier $zedSupplier = null)
    {
        $this->zedSupplier = $zedSupplier;

        return $this;
    }

    /**
     * Get zedSupplier
     *
     * @return ZedSupplier
     */
    public function getZedSupplier()
    {
        return $this->zedSupplier;
    }

    /**
     * @param string $pisCofins
     *
     * @return ZedProduct
     */
    public function setPisCofins($pisCofins)
    {
        $this->pisCofins = $pisCofins;

        return $this;
    }

    /**
     * @return string
     */
    public function getPisCofins()
    {
        return $this->pisCofins;
    }

    /**
     * @param boolean $isBook
     *
     * @return ZedProduct
     */
    public function setIsBook($isBook)
    {
        $this->isBook = $isBook;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isBook()
    {
        return $this->isBook;
    }

    /**
     * @param float $estimatedTotalTaxesAliquot
     *
     * @return ZedProduct
     */
    public function setEstimatedTotalTaxesAliquot($estimatedTotalTaxesAliquot)
    {
        $this->estimatedTotalTaxesAliquot = $estimatedTotalTaxesAliquot;

        return $this;
    }

    /**
     * @return float
     */
    public function getEstimatedTotalTaxesAliquot()
    {
        return $this->estimatedTotalTaxesAliquot;
    }

    /**
     * @return int
     */
    public function getNcm()
    {
        return $this->ncm;
    }

    /**
     * @param int $ncm
     * @return ZedProduct
     */
    public function setNcm($ncm)
    {
        $this->ncm = $ncm;

        return $this;
    }

    /**
     * @return string
     */
    public function getCest()
    {
        return $this->cest;
    }

    /**
     * @param string $cest
     * @return ZedProduct
     */
    public function setCest($cest)
    {
        $this->cest = $cest;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSt()
    {
        return $this->isSt;
    }

    /**
     * @param boolean $isSt
     * @return $this
     */
    public function setIsSt($isSt)
    {
        $this->isSt = $isSt;

        return $this;
    }

    /**
     * Get icmsLabel
     *
     * @return mixed
     */
    public function getIcmsLabel()
    {
        if ($this->isSt()) {
            return 'ST';
        }

        return 'ICMS Alíquota';
    }

    public function getPisLabel()
    {
        $label = $this->getPisCofins();

        if (!$label || $label === '-') {
            return 'PIS Alíquota';
        }

        return $label;
    }

    /**
     * @param float $markup
     * @return $this
     */
    public function setMarkup($markup)
    {
        $this->markup = $markup;

        return $this;
    }

    /**
     * @return float
     */
    public function getMarkup()
    {
        return $this->markup;
    }

    /**
     * @param int $cost
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return int
     */
    public function getCost()
    {
        return $this->cost;
    }
}
