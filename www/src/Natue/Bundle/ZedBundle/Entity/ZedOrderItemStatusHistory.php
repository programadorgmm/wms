<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZedOrderItemStatusHistory
 *
 * @ORM\Table(
 *  name="zed_order_item_status_history",
 *  indexes={@ORM\Index(name="zed_order_item_status_history_fk_zed_order_item", columns={"zed_order_item"}),
 * @ORM\Index(name="zed_order_item_status_history_fk_zed_order_item_status", columns={"zed_order_item_status"})}
 * )
 *
 * @ORM\Entity
 */
class ZedOrderItemStatusHistory
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
     */
    private $id;

    /**
     * @var ZedOrderItemStatus
     * @ORM\ManyToOne(targetEntity="ZedOrderItemStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_order_item_status", referencedColumnName="id")
     * })
     */
    private $zedOrderItemStatus;

    /**
     * @var ZedOrderItem
     * @ORM\ManyToOne(targetEntity="ZedOrderItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_order_item", referencedColumnName="id")
     * })
     */
    private $zedOrderItem;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ZedOrderItemStatusHistory
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
     * @return ZedOrderItemStatusHistory
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
     * @return ZedOrderItemStatusHistory
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
     * Set zedOrderItemStatus
     *
     * @param ZedOrderItemStatus $zedOrderItemStatus
     *
     * @return ZedOrderItemStatusHistory
     */
    public function setZedOrderItemStatus(ZedOrderItemStatus $zedOrderItemStatus = null)
    {
        $this->zedOrderItemStatus = $zedOrderItemStatus;

        return $this;
    }

    /**
     * Get zedOrderItemStatus
     *
     * @return ZedOrderItemStatus
     */
    public function getZedOrderItemStatus()
    {
        return $this->zedOrderItemStatus;
    }

    /**
     * Set zedOrderItem
     *
     * @param ZedOrderItem $zedOrderItem
     *
     * @return ZedOrderItemStatusHistory
     */
    public function setZedOrderItem(ZedOrderItem $zedOrderItem = null)
    {
        $this->zedOrderItem = $zedOrderItem;

        return $this;
    }

    /**
     * Get zedOrderItem
     *
     * @return ZedOrderItem
     */
    public function getZedOrderItem()
    {
        return $this->zedOrderItem;
    }
}
