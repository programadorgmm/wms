<?php

namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumInventoryItemStatusType;

/**
 * InventoryItem
 *
 * @ORM\Table(
 *  name="inventory_item",
 *  indexes={@ORM\Index(name="inventory_item_fk_inventory", columns={"inventory"}),
 * @ORM\Index(name="inventory_item_fk_stock_item", columns={"stock_item"}),
 * @ORM\Index(name="inventory_item_fk_zed_product", columns={"zed_product"}),
 * })
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\StockBundle\Repository\InventoryItemRepository")
 * @ORM\HasLifecycleCallbacks
 */
class InventoryItem
{

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
     * @var StockItem
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="stock_item", referencedColumnName="id")
     * })
     */
    private $stockItem;

    /**
     * @var \Natue\Bundle\ZedBundle\Entity\ZedProduct
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ZedBundle\Entity\ZedProduct")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_product", referencedColumnName="id")
     * })
     */
    private $zedProduct;

    /**
     * @var string
     * @ORM\Column(name="status", type="EnumInventoryItemStatusType", nullable=false)
     */
    private $status;

    /**
     * @var Inventory
     * @ORM\ManyToOne(targetEntity="Inventory", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="inventory", referencedColumnName="id")
     * })
     */
    private $inventory;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->setStatus(EnumInventoryItemStatusType::STATUS_NEW);
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @ORM\PreUpdate()
     */
    public function setUpdatedDate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return InventoryItem
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
     * @return InventoryItem
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
     * Set stockItem
     *
     * @param StockItem $stockItem
     *
     * @return InventoryItem
     */
    public function setStockItem(StockItem $stockItem = null)
    {
        $this->stockItem = $stockItem;

        return $this;
    }

    /**
     * Get stockItem
     *
     * @return StockItem
     */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    /**
     * Set zedProduct
     *
     * @param ZedProduct $zedProduct
     *
     * @return InventoryItem
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
     * Set status
     *
     * @param string $status
     *
     * @return InventoryItem
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, EnumInventoryItemStatusType::$values)) {
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
     * Set inventory
     *
     * @param Inventory $inventory
     *
     * @return InventoryItem
     */
    public function setInventory(Inventory $inventory = null)
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * Get inventory
     *
     * @return Inventory
     */
    public function getInventory()
    {
        return $this->inventory;
    }
}
