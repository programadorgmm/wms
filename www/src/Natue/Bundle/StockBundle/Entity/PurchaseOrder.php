<?php

namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrder
 *
 * @ORM\Table(
 *  name="purchase_order",
 *  indexes={@ORM\Index(name="purchase_order_fk_zed_supplier", columns={"zed_supplier"}),
 * @ORM\Index(name="invoice_key_UNIQUE", columns={"invoice_key"}),
 * @ORM\Index(name="purchase_order_fk_user", columns={"user"})}
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\StockBundle\Repository\PurchaseOrderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PurchaseOrder
{
    /**
     * @var string
     * @ORM\Column(name="invoice_key", type="string", length=255, nullable=true)
     */
    private $invoiceKey;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_ordered", type="datetime", nullable=true)
     */
    private $dateOrdered;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_expected_delivery", type="datetime", nullable=true)
     */
    private $dateExpectedDelivery;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_actual_delivery", type="datetime", nullable=true)
     */
    private $dateActualDelivery;

    /**
     * @var integer
     * @ORM\Column(name="volumes_total", type="integer", nullable=true)
     */
    private $volumesTotal;

    /**
     * @var integer
     * @ORM\Column(name="volumes_received", type="integer", nullable=true)
     */
    private $volumesReceived;

    /**
     * @var integer
     * @ORM\Column(name="cost_total", type="integer", nullable=true)
     */
    private $costTotal;

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
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Natue\Bundle\UserBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \Natue\Bundle\ZedBundle\Entity\ZedSupplier
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ZedBundle\Entity\ZedSupplier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_supplier", referencedColumnName="id")
     * })
     */
    private $zedSupplier;

    /**
     * @var \Natue\Bundle\StockBundle\Entity\OrderRequest
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\StockBundle\Entity\OrderRequest")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_request", referencedColumnName="id")
     * })
     */
    private $orderRequest;

    /**
     *
     * @ORM\OneToMany(targetEntity="Natue\Bundle\StockBundle\Entity\PurchaseOrderItem", mappedBy="purchaseOrder", cascade={"persist"})
     */
    private $purchaseOrderItems;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->purchaseOrderItems = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setCreatedAt(new \DateTime());
        $this->setVolumesReceived(0);
    }

    /**
     * @ORM\PreUpdate()
     */
    public function setUpdatedDate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * Set invoiceKey
     *
     * @param string $invoiceKey
     *
     * @return PurchaseOrder
     */
    public function setInvoiceKey($invoiceKey)
    {
        $this->invoiceKey = $invoiceKey;

        return $this;
    }

    /**
     * Get invoiceKey
     *
     * @return string
     */
    public function getInvoiceKey()
    {
        return $this->invoiceKey;
    }

    /**
     * Set dateOrdered
     *
     * @param \DateTime $dateOrdered
     *
     * @return PurchaseOrder
     */
    public function setDateOrdered($dateOrdered)
    {
        $this->dateOrdered = $dateOrdered;

        return $this;
    }

    /**
     * Get dateOrdered
     *
     * @return \DateTime
     */
    public function getDateOrdered()
    {
        return $this->dateOrdered;
    }

    /**
     * Set dateExpectedDelivery
     *
     * @param \DateTime $dateExpectedDelivery
     *
     * @return PurchaseOrder
     */
    public function setDateExpectedDelivery($dateExpectedDelivery)
    {
        $this->dateExpectedDelivery = $dateExpectedDelivery;

        return $this;
    }

    /**
     * Get dateExpectedDelivery
     *
     * @return \DateTime
     */
    public function getDateExpectedDelivery()
    {
        return $this->dateExpectedDelivery;
    }

    /**
     * Set dateActualDelivery
     *
     * @param \DateTime $dateActualDelivery
     *
     * @return PurchaseOrder
     */
    public function setDateActualDelivery($dateActualDelivery)
    {
        $this->dateActualDelivery = $dateActualDelivery;

        return $this;
    }

    /**
     * Get dateActualDelivery
     *
     * @return \DateTime
     */
    public function getDateActualDelivery()
    {
        return $this->dateActualDelivery;
    }

    /**
     * Set volumesTotal
     *
     * @param integer $volumesTotal
     *
     * @return PurchaseOrder
     */
    public function setVolumesTotal($volumesTotal)
    {
        $this->volumesTotal = $volumesTotal;

        return $this;
    }

    /**
     * Get volumesTotal
     *
     * @return integer
     */
    public function getVolumesTotal()
    {
        return $this->volumesTotal;
    }

    /**
     * Set volumesReceived
     *
     * @param integer $volumesReceived
     *
     * @return PurchaseOrder
     */
    public function setVolumesReceived($volumesReceived)
    {
        $this->volumesReceived = $volumesReceived;

        return $this;
    }

    /**
     * Get volumesReceived
     *
     * @return integer
     */
    public function getVolumesReceived()
    {
        return $this->volumesReceived;
    }

    /**
     * Set costTotal
     *
     * @param integer $costTotal
     *
     * @return PurchaseOrder
     */
    public function setCostTotal($costTotal)
    {
        $this->costTotal = $costTotal;

        return $this;
    }

    /**
     * Get costTotal
     *
     * @return integer
     */
    public function getCostTotal()
    {
        return $this->costTotal;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PurchaseOrder
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
     * @return PurchaseOrder
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
     * Set user
     *
     * @param \Natue\Bundle\UserBundle\Entity\User $user
     *
     * @return PurchaseOrder
     */
    public function setUser(\Natue\Bundle\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Natue\Bundle\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set zedSupplier
     *
     * @param \Natue\Bundle\ZedBundle\Entity\ZedSupplier $zedSupplier
     *
     * @return PurchaseOrder
     */
    public function setZedSupplier(\Natue\Bundle\ZedBundle\Entity\ZedSupplier $zedSupplier = null)
    {
        $this->zedSupplier = $zedSupplier;

        return $this;
    }

    /**
     * Get zedSupplier
     *
     * @return \Natue\Bundle\ZedBundle\Entity\ZedSupplier
     */
    public function getZedSupplier()
    {
        return $this->zedSupplier;
    }

    /**
     * Set orderRequest
     *
     * @param \Natue\Bundle\StockBundle\Entity\OrderRequest $orderRequest
     * @return PurchaseOrder
     */
    public function setOrderRequest(\Natue\Bundle\StockBundle\Entity\OrderRequest $orderRequest = null)
    {
        $this->orderRequest = $orderRequest;

        return $this;
    }

    /**
     * Get orderRequest
     *
     * @return \Natue\Bundle\StockBundle\Entity\OrderRequest
     */
    public function getOrderRequest()
    {
        return $this->orderRequest;
    }

    /**
     * Add purchaseOrderItems
     *
     * @param \Natue\Bundle\StockBundle\Entity\PurchaseOrderItem $purchaseOrderItem
     * @return PurchaseOrder
     */
    public function addPurchaseOrderItem(\Natue\Bundle\StockBundle\Entity\PurchaseOrderItem $purchaseOrderItem)
    {
        $this->purchaseOrderItems[] = $purchaseOrderItem;

        return $this;
    }

    /**
     * Remove purchaseOrderItems
     *
     * @param \Natue\Bundle\StockBundle\Entity\PurchaseOrderItem $purchaseOrderItem
     */
    public function removePurchaseOrderItem(\Natue\Bundle\StockBundle\Entity\PurchaseOrderItem $purchaseOrderItem)
    {
        $this->purchaseOrderItems->removeElement($purchaseOrderItem);
    }

    /**
     * @return mixed
     */
    public function getPurchaseOrderItems()
    {
        return $this->purchaseOrderItems;
    }
}
