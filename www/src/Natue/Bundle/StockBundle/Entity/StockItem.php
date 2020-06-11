<?php

namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Finite\StatefulInterface;

use Natue\Bundle\InvoiceBundle\Entity\Invoice;
use Natue\Bundle\UserBundle\Common\TrackableInterface;
use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Natue\Bundle\ZedBundle\Entity\ZedOrderItem;
use Natue\Bundle\ShippingBundle\Entity\ShippingVolume;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="Natue\Bundle\StockBundle\Repository\StockItemRepository")
 *
 * @ORM\Table(
 *  name="stock_item",
 *  indexes={@ORM\Index(name="stock_item_fk_purchase_order_item",
 *  columns={"purchase_order_item"}),
 * @ORM\Index(name="stock_item_fk_zed_product", columns={"zed_product"}),
 * @ORM\Index(name="stock_item_fk_stock_position", columns={"stock_position"}),
 * @ORM\Index(name="stock_item_fk_zed_order_item", columns={"zed_order_item"}),
 * @ORM\Index(name="stock_item_fk_shipping_volume", columns={"shipping_volume"}),
 * @ORM\Index(name="stock_item_status", columns={"status"}),
 * @ORM\Index(name="stock_item_date_expiration", columns={"date_expiration"}),
 * @ORM\Index(name="stock_item_barcode_status_date", columns={"barcode","status","date_expiration"})}
 * )
 */
class StockItem implements StatefulInterface, TrackableInterface
{
    /**
     * @var \DateTime
     * @ORM\Column(name="date_expiration", type="date", nullable=true)
     */
    private $dateExpiration;

    /**
     * @var string
     * @ORM\Column(name="barcode", type="string", length=255, nullable=true)
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
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="status", type="EnumStockItemStatusType", nullable=false)
     */
    private $status;

    /**
     * @var StockPosition
     * @ORM\ManyToOne(targetEntity="StockPosition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="stock_position", referencedColumnName="id")
     * })
     */
    private $stockPosition;

    /**
     * @var \Natue\Bundle\ShippingBundle\Entity\ShippingVolume
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ShippingBundle\Entity\ShippingVolume")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shipping_volume", referencedColumnName="id")
     * })
     */
    private $shippingVolume;

    /**
     * @var \Natue\Bundle\ZedBundle\Entity\ZedProduct
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ZedBundle\Entity\ZedProduct")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_product", referencedColumnName="id")
     * })
     */
    private $zedProduct;

    /**
     * @var \Natue\Bundle\ZedBundle\Entity\ZedOrderItem
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ZedBundle\Entity\ZedOrderItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_order_item", referencedColumnName="id")
     * })
     */
    private $zedOrderItem;

    /**
     * @var PurchaseOrderItem
     * @ORM\ManyToOne(targetEntity="PurchaseOrderItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_order_item", referencedColumnName="id")
     * })
     */
    private $purchaseOrderItem;

    /**
     * @var \Natue\Bundle\UserBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Natue\Bundle\InvoiceBundle\Entity\Invoice", mappedBy="items")
     */
    private $invoice;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->setStatus(EnumStockItemStatusType::STATUS_INCOMING);
        $this->setCreatedAt(new \DateTime());
    }

    public function isAssigned()
    {
        return $this->getStatus() == EnumStockItemStatusType::STATUS_ASSIGNED;
    }

    /**
     * @param \DateTime $dateExpiration
     * @return StockItem
     */
    public function setDateExpiration($dateExpiration)
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    /**
     * Get dateExpiration
     *
     * @return \DateTime
     */
    public function getDateExpiration()
    {
        return $this->dateExpiration;
    }

    /**
     * Set barcode
     *
     * @param string $barcode
     *
     * @return StockItem
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
     * @return StockItem
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
     * @return StockItem
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
     * Set status
     *
     * @param string $status
     *
     * @return StockItem
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, EnumStockItemStatusType::$values)) {
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
     * Set stockPosition
     *
     * @param StockPosition $stockPosition
     *
     * @return StockItem
     */
    public function setStockPosition(StockPosition $stockPosition = null)
    {
        $this->stockPosition = $stockPosition;

        return $this;
    }

    /**
     * Get stockPosition
     *
     * @return StockPosition
     */
    public function getStockPosition()
    {
        return $this->stockPosition;
    }

    /**
     * Set shippingVolume
     *
     * @param ShippingVolume $shippingVolume
     *
     * @return StockItem
     */
    public function setShippingVolume(ShippingVolume $shippingVolume = null)
    {
        $this->shippingVolume = $shippingVolume;

        return $this;
    }

    /**
     * Get shippingVolume
     *
     * @return ShippingVolume
     */
    public function getShippingVolume()
    {
        return $this->shippingVolume;
    }

    /**
     * Set zedProduct
     *
     * @param ZedProduct $zedProduct
     *
     * @return StockItem
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
     * Set zedOrderItem
     *
     * @param ZedOrderItem $zedOrderItem
     *
     * @return StockItem
     */
    public function setZedOrderItem(ZedOrderItem $zedOrderItem = null)
    {
        $this->zedOrderItem = $zedOrderItem;

        return $this;
    }

    /**
     * Get zedOrderItem
     *
     * @return \Natue\Bundle\ZedBundle\Entity\ZedOrderItem
     */
    public function getZedOrderItem()
    {
        return $this->zedOrderItem;
    }

    /**
     * Set purchaseOrderItem
     *
     * @param PurchaseOrderItem $purchaseOrderItem
     *
     * @return StockItem
     */
    public function setPurchaseOrderItem(PurchaseOrderItem $purchaseOrderItem = null)
    {
        $this->purchaseOrderItem = $purchaseOrderItem;

        return $this;
    }

    /**
     * Get purchaseOrderItem
     *
     * @return PurchaseOrderItem
     */
    public function getPurchaseOrderItem()
    {
        return $this->purchaseOrderItem;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     *
     * @return $this
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
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
     * Get invoice
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}
