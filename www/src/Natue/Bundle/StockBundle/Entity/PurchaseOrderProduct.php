<?php

namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PurchaseOrderProduct
 *
 * @ORM\Table(
 *  name="purchase_order_product",
 *  indexes={@ORM\Index(name="purchase_order_product_fk_purchase_order", columns={"purchase_order"}),
 * @ORM\Index(name="purchase_order_product_fk_zed_product", columns={"zed_product"})}
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\StockBundle\Repository\PurchaseOrderProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PurchaseOrderProduct
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var PurchaseOrder
     * @Assert\NotBlank(
     *      message = "purchase_order should not be blank"
     * )
     *
     * @ORM\ManyToOne(targetEntity="PurchaseOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_order", referencedColumnName="id")
     * })
     */
    private $purchaseOrder;

    /**
     * @var \Natue\Bundle\ZedBundle\Entity\ZedProduct
     * @Assert\NotBlank(
     *      message = "zed_product should not be blank"
     * )
     *
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ZedBundle\Entity\ZedProduct")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_product", referencedColumnName="id")
     * })
     */
    private $zedProduct;

    /**
     * @var string
     *
     * @ORM\Column(name="sku_supplier", type="string", length=255, nullable=true)
     */
    private $skuSupplier;

    /**
     * @var integer
     * @Assert\NotBlank(
     *      message = "nfe_sequential should not be blank"
     * )
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "nfe_sequential number should be greater than 0"
     * )
     *
     * @ORM\Column(name="nfe_sequential", type="integer", nullable=false)
     */
    private $nfeSequential;

    /**
     * @var integer
     * @Assert\NotBlank(
     *      message = "ncm should not be blank"
     * )
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "ncm number should be greater than 0"
     * )
     *
     * @ORM\Column(name="ncm", type="integer", nullable=false)
     */
    private $ncm;

    /**
     * @var integer
     * @Assert\NotBlank(
     *      message = "cst_pis should not be blank"
     * )
     *
     * @ORM\Column(name="cst_pis", type="integer", nullable=false)
     */
    private $cstPis;

    /**
     * @var integer
     * @Assert\NotBlank(
     *      message = "cst_icms should not be blank"
     * )
     *
     * @ORM\Column(name="cst_icms", type="integer", nullable=false)
     */
    private $cstIcms;

    /**
     * @var integer
     * @Assert\NotBlank(
     *      message = "cfop should not be blank"
     * )
     *
     * @ORM\Column(name="cfop", type="integer", nullable=false)
     */
    private $cfop;

    /**
     * @var \DateTime
     * @Assert\NotBlank(
     *      message = "CreatedAt should not be blank"
     * )
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

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
     * Set purchaseOrder
     *
     * @param PurchaseOrder $purchaseOrder
     * @return PurchaseOrderProduct
     */
    public function setPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    /**
     * Get purchaseOrder
     *
     * @return PurchaseOrder
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * Set zedProduct
     *
     * @param ZedProduct $zedProduct
     * @return PurchaseOrderProduct
     */
    public function setZedProduct(ZedProduct $zedProduct)
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
     * Set skuSupplier
     *
     * @param string $skuSupplier
     * @return PurchaseOrderProduct
     */
    public function setSkuSupplier($skuSupplier)
    {
        $this->skuSupplier = $skuSupplier;

        return $this;
    }

    /**
     * Get skuSupplier
     *
     * @return string 
     */
    public function getSkuSupplier()
    {
        return $this->skuSupplier;
    }

    /**
     * Set nfeSequential
     *
     * @param string $nfeSequential
     * @return PurchaseOrderProduct
     */
    public function setNfeSequential($nfeSequential)
    {
        $this->nfeSequential = $nfeSequential;

        return $this;
    }

    /**
     * Get nfeSequential
     *
     * @return integer
     */
    public function getNfeSequential()
    {
        return $this->nfeSequential;
    }

    /**
     * Set ncm
     *
     * @param integer $ncm
     * @return PurchaseOrderProduct
     */
    public function setNcm($ncm)
    {
        $this->ncm = $ncm;

        return $this;
    }

    /**
     * Get ncm
     *
     * @return integer 
     */
    public function getNcm()
    {
        return $this->ncm;
    }

    /**
     * Set cstPis
     *
     * @param integer $cstPis
     * @return PurchaseOrderProduct
     */
    public function setCstPis($cstPis)
    {
        $this->cstPis = $cstPis;

        return $this;
    }

    /**
     * Get cstPis
     *
     * @return integer 
     */
    public function getCstPis()
    {
        return $this->cstPis;
    }

    /**
     * Set cstIcms
     *
     * @param integer $cstIcms
     * @return PurchaseOrderProduct
     */
    public function setCstIcms($cstIcms)
    {
        $this->cstIcms = $cstIcms;

        return $this;
    }

    /**
     * Get cstIcms
     *
     * @return integer 
     */
    public function getCstIcms()
    {
        return $this->cstIcms;
    }

    /**
     * Set cfop
     *
     * @param integer $cfop
     * @return PurchaseOrderProduct
     */
    public function setCfop($cfop)
    {
        $this->cfop = $cfop;

        return $this;
    }

    /**
     * Get cfop
     *
     * @return integer 
     */
    public function getCfop()
    {
        return $this->cfop;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return PurchaseOrderProduct
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
     * @return PurchaseOrderProduct
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
     * Gets triggered only on insert
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->setCreatedAt(new \DateTime("now"));
    }

    /**
     * Gets triggered every time on update
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->setUpdatedAt(new \DateTime("now"));
    }
}
