<?php

namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inventory
 *
 * @ORM\Table(
 *  name="inventory",
 *  indexes={@ORM\Index(name="inventory_fk_stock_position", columns={"stock_position"}),
 * @ORM\Index(name="inventory_fk_user", columns={"user"})}
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\StockBundle\Repository\InventoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Inventory
{
    /**
     * @var \DateTime
     * @ORM\Column(name="started_at", type="datetime", nullable=false)
     */
    private $startedAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="finished_at", type="datetime", nullable=true)
     */
    private $finishedAt;

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
     * @var StockPosition
     * @ORM\ManyToOne(targetEntity="StockPosition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="stock_position", referencedColumnName="id")
     * })
     */
    private $stockPosition;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
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
     * Set startedAt
     *
     * @param \DateTime $startedAt
     *
     * @return Inventory
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * Get startedAt
     *
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set finishedAt
     *
     * @param \DateTime $finishedAt
     *
     * @return Inventory
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * Get finishedAt
     *
     * @return \DateTime
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Inventory
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
     * @return Inventory
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
     * @return Inventory
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
     * @return Inventory
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
}
