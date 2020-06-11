<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderExtended
 *
 * @ORM\Table(
 *  name="order_extended",
 *  indexes={@ORM\Index(name="order_extended_fk_order_return_reason", columns={"order_return_reason"}),
 * @ORM\Index(name="order_extended_fk_shipping_picking_list", columns={"shipping_picking_list"})}
 * )
 *
 * @ORM\Entity
 */
class OrderExtended
{
    /**
     * @var string
     * @ORM\Column(name="invoice_key", type="string", length=255, nullable=true)
     */
    private $invoiceKey;

    /**
     * @var boolean
     * @ORM\Column(name="ready_for_picking", type="boolean", nullable=false)
     */
    private $readyForPicking;

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
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @var ZedOrder
     * @ORM\OneToOne(targetEntity="ZedOrder", inversedBy="orderExtended")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_order_id", referencedColumnName="id")
     * })
     */
    private $zedOrder;

    /**
     * @var \Natue\Bundle\ShippingBundle\Entity\ShippingPickingList
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ShippingBundle\Entity\ShippingPickingList")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shipping_picking_list", referencedColumnName="id")
     * })
     */
    private $shippingPickingList;

    /**
     * @var OrderReturnReason
     * @ORM\ManyToOne(targetEntity="OrderReturnReason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_return_reason", referencedColumnName="id")
     * })
     */
    private $orderReturnReason;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setReadyForPicking(true);
    }

    /**
     * Set invoiceKey
     *
     * @param string $invoiceKey
     *
     * @return OrderExtended
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
     * Set readyForPicking
     *
     * @param boolean $readyForPicking
     *
     * @return OrderExtended
     */
    public function setReadyForPicking($readyForPicking)
    {
        $this->readyForPicking = $readyForPicking;

        return $this;
    }

    /**
     * Get readyForPicking
     *
     * @return boolean
     */
    public function getReadyForPicking()
    {
        return $this->readyForPicking;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return OrderExtended
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
     * @return OrderExtended
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
     * @param ZedOrder $id
     *
     * @return OrderExtended
     */
    public function setZedOrder(ZedOrder $zedOrder)
    {
        $this->zedOrder = $zedOrder;

        return $this;
    }

    /**
     * Get id
     *
     * @return ZedOrder
     */
    public function getZedOrder()
    {
        return $this->zedOrder;
    }

    /**
     * Set shippingPickingList
     *
     * @param \Natue\Bundle\ShippingBundle\Entity\ShippingPickingList $shippingPickingList
     *
     * @return OrderExtended
     */
    public function setShippingPickingList(
        \Natue\Bundle\ShippingBundle\Entity\ShippingPickingList $shippingPickingList = null
    ) {
        $this->shippingPickingList = $shippingPickingList;

        return $this;
    }

    /**
     * Get shippingPickingList
     *
     * @return \Natue\Bundle\ShippingBundle\Entity\ShippingPickingList
     */
    public function getShippingPickingList()
    {
        return $this->shippingPickingList;
    }

    /**
     * Set orderReturnReason
     *
     * @param OrderReturnReason $orderReturnReason
     *
     * @return OrderExtended
     */
    public function setOrderReturnReason(OrderReturnReason $orderReturnReason = null)
    {
        $this->orderReturnReason = $orderReturnReason;

        return $this;
    }

    /**
     * Get orderReturnReason
     *
     * @return OrderReturnReason
     */
    public function getOrderReturnReason()
    {
        return $this->orderReturnReason;
    }
}
