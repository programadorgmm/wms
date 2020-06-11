<?php

namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StockItemPositionHistory
 *
 * @ORM\Table(
 *  name="stock_item_position_history",
 *  indexes={@ORM\Index(name="stock_item_position_history_fk_stock_item", columns={"stock_item"}),
 * @ORM\Index(name="stock_item_position_history_fk_stock_position", columns={"stock_position"}),
 * @ORM\Index(name="stock_item_position_history_fk_user", columns={"user"})}
 * )
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class StockItemPositionHistory
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
     * @var StockPosition
     * @ORM\ManyToOne(targetEntity="StockPosition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="stock_position", referencedColumnName="id")
     * })
     */
    private $stockPosition;

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
     * @return StockItemPositionHistory
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
     * @return StockItemPositionHistory
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
     * Set stockPosition
     *
     * @param StockPosition $stockPosition
     *
     * @return StockItemPositionHistory
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
     * Set stockItem
     *
     * @param StockItem $stockItem
     *
     * @return StockItemPositionHistory
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
