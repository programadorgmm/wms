<?php

namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Finite\StatefulInterface;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PurchaseOrderItem
 *
 * @ORM\Table(
 *  name="purchase_order_item",
 *  indexes={@ORM\Index(name="purchase_order_item_fk_purchase_order", columns={"purchase_order"}),
 * @ORM\Index(name="purchase_order_item_fk_zed_product", columns={"zed_product"}),
 * @ORM\Index(name="purchase_order_item_fk_purchase_order_item_reception", columns={"purchase_order_item_reception"})}
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\StockBundle\Repository\PurchaseOrderItemRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PurchaseOrderItem implements StatefulInterface
{
    /**
     * @var integer
     * @Assert\NotBlank
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "An item should have a cost of 0 at least"
     * )
     * @ORM\Column(name="cost", type="integer", nullable=true)
     */
    private $cost;

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
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="icms", type="integer")
     */
    private $icms = 0;

    /**
     * @var integer
     * @ORM\Column(name="icms_st", type="integer")
     */
    private $icmsSt = 0;

    /**
     * @var integer
     * @ORM\Column(name="icms_st_calc_base", type="integer", nullable=true)
     */
    private $icmsStCalcBase = 0;

    /**
     * @var integer
     * @ORM\Column(name="invoice_cost", type="integer")
     */
    private $invoiceCost = 0;

    /**
     * @var PurchaseOrderItemReception
     * @ORM\ManyToOne(targetEntity="PurchaseOrderItemReception")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_order_item_reception", referencedColumnName="id")
     * })
     */
    private $purchaseOrderItemReception;

    /**
     * @var \Natue\Bundle\ZedBundle\Entity\ZedProduct
     * @Assert\NotBlank(
     *      message = "ZedProduct should not be blank"
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
     * @Assert\NotBlank
     *
     * @Assert\NotBlank(
     *      message = "Status should not be blank"
     * )
     *
     * @ORM\Column(name="status", type="EnumPurchaseOrderItemStatusType", nullable=false)
     */
    private $status;

    /**
     * @var PurchaseOrder
     * @Assert\NotBlank(
     *      message = "PurchaseOrder should not be blank"
     * )
     * @ORM\ManyToOne(targetEntity="PurchaseOrder", inversedBy="purchaseOrderItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_order", referencedColumnName="id")
     * })
     */
    private $purchaseOrder;

    /**
     * @var PurchaseOrderProduct
     *
     * @ORM\ManyToOne(targetEntity="PurchaseOrderProduct", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_order_product", referencedColumnName="id")
     * })
     */
    private $purchaseOrderProduct;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setStatus(EnumPurchaseOrderItemStatusType::STATUS_INCOMING);
    }

    /**
     * @ORM\PreUpdate()
     */
    public function setUpdatedDate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * Set cost
     *
     * @param integer $cost
     *
     * @return PurchaseOrderItem
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PurchaseOrderItem
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
     * @return PurchaseOrderItem
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set purchaseOrderItemReception
     *
     * @param PurchaseOrderItemReception $purchaseOrderItemReception
     *
     * @return PurchaseOrderItem
     */
    public function setPurchaseOrderItemReception(PurchaseOrderItemReception $purchaseOrderItemReception = null)
    {
        $this->purchaseOrderItemReception = $purchaseOrderItemReception;

        return $this;
    }

    /**
     * Get purchaseOrderItemReception
     *
     * @return PurchaseOrderItemReception
     */
    public function getPurchaseOrderItemReception()
    {
        return $this->purchaseOrderItemReception;
    }

    /**
     * Set zedProduct
     *
     * @param \Natue\Bundle\ZedBundle\Entity\ZedProduct $zedProduct
     *
     * @return PurchaseOrderItem
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

    /**
     * Set status
     *
     * @param string $status
     *
     * @return PurchaseOrderItem
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, EnumPurchaseOrderItemStatusType::$values)) {
            throw new \InvalidArgumentException("Invalid status");
        }

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
     * Set purchaseOrder
     *
     * @param PurchaseOrder $purchaseOrder
     *
     * @return PurchaseOrderItem
     */
    public function setPurchaseOrder(PurchaseOrder $purchaseOrder = null)
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
     * Set FiniteState (status)
     *
     * @param string $status
     *
     * @return void
     */
    public function setFiniteState($status)
    {
        $this->status = $status;
    }

    /**
     * Get FiniteState (status)
     *
     * @return string
     */
    public function getFiniteState()
    {
        return $this->status;
    }

    /**
     * Set icms
     *
     * @param integer $icms
     * @return PurchaseOrderItem
     */
    public function setIcms($icms)
    {
        $this->icms = $icms;

        return $this;
    }

    /**
     * Get icms
     *
     * @return integer
     */
    public function getIcms()
    {
        return $this->icms;
    }

    /**
     * Set icmsSt
     *
     * @param integer $icmsSt
     * @return PurchaseOrderItem
     */
    public function setIcmsSt($icmsSt)
    {
        $this->icmsSt = $icmsSt;

        return $this;
    }

    /**
     * Get icmsSt
     *
     * @return integer
     */
    public function getIcmsSt()
    {
        return $this->icmsSt;
    }

    /**
     * @return int
     */
    public function getIcmsStCalcBase()
    {
        return $this->icmsStCalcBase;
    }

    /**
     * @param int $icmsStCalcBase
     */
    public function setIcmsStCalcBase($icmsStCalcBase)
    {
        $this->icmsStCalcBase = $icmsStCalcBase;
    }

    /**
     * @return int
     */
    public function getInvoiceCost()
    {
        return $this->invoiceCost;
    }

    /**
     * @param int $invoiceCost
     *
     * @return PurchaseOrderItem
     */
    public function setInvoiceCost($invoiceCost)
    {
        $this->invoiceCost = $invoiceCost;

        return $this;
    }

    /**
     * @return PurchaseOrderProduct
     */
    public function getPurchaseOrderProduct()
    {
        return $this->purchaseOrderProduct;
    }

    /**
     * @param PurchaseOrderProduct $purchaseOrderProduct
     *
     * @return PurchaseOrderItem
     */
    public function setPurchaseOrderProduct(PurchaseOrderProduct $purchaseOrderProduct)
    {
        $this->purchaseOrderProduct = $purchaseOrderProduct;

        return $this;
    }
}
