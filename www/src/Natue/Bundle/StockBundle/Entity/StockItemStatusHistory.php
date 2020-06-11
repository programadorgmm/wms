<?php

namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;

/**
 * StockItemStatusHistory
 *
 * @ORM\Table(
 *  name="stock_item_status_history",
 *  indexes={@ORM\Index(name="stock_item_status_history_fk_stock_item", columns={"stock_item"}),
 * @ORM\Index(name="stock_item_status_history_fk_user", columns={"user"})}
 * )
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class StockItemStatusHistory
{
    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

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
     * @var string
     * @ORM\Column(name="status", type="EnumStockItemStatusType", nullable=false)
     */
    private $status;

    /**
     * @var StockItem
     * @ORM\ManyToOne(targetEntity="StockItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="stock_item", referencedColumnName="id")
     * })
     */
    private $stockItem;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return StockItemStatusHistory
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
     * @return StockItemStatusHistory
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
     * Set status
     *
     * @param string $status
     *
     * @throws \InvalidArgumentException
     * @return StockItem
     */
    public function setStatus($status = null)
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
     * Set stockItem
     *
     * @param StockItem $stockItem
     *
     * @return StockItemStatusHistory
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
}
