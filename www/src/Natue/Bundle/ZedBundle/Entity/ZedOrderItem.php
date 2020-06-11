<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZedOrderItem
 *
 * @ORM\Table(
 *  name="zed_order_item",
 *  indexes={@ORM\Index(name="zed_order_item_fk_zed_product", columns={"zed_product"}),
 * @ORM\Index(name="zed_order_item_fk_zed_order_item_status", columns={"zed_order_item_status"}),
 * @ORM\Index(name="zed_order_item_fk_zed_order", columns={"zed_order"})}
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\ZedBundle\Repository\ZedOrderItemRepository")
 */
class ZedOrderItem
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
     * @var ZedProduct
     * @ORM\ManyToOne(targetEntity="ZedProduct", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_product", referencedColumnName="id")
     * })
     */
    private $zedProduct;

    /**
     * @var ZedOrderItemStatus
     * @ORM\ManyToOne(targetEntity="ZedOrderItemStatus", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_order_item_status", referencedColumnName="id")
     * })
     */
    private $zedOrderItemStatus;

    /**
     * @var ZedOrder
     * @ORM\ManyToOne(targetEntity="ZedOrder", cascade={"persist"}, inversedBy="zedOrderItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_order", referencedColumnName="id")
     * })
     */
    private $zedOrder;

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ZedOrderItem
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
     * @return ZedOrderItem
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
     * @return ZedOrderItem
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
     * Set zedProduct
     *
     * @param ZedProduct $zedProduct
     *
     * @return ZedOrderItem
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
     * Set zedOrderItemStatus
     *
     * @param ZedOrderItemStatus $zedOrderItemStatus
     *
     * @return ZedOrderItem
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
     * Set zedOrder
     *
     * @param ZedOrder $zedOrder
     *
     * @return ZedOrderItem
     */
    public function setZedOrder(ZedOrder $zedOrder = null)
    {
        $this->zedOrder = $zedOrder;

        return $this;
    }

    /**
     * Get zedOrder
     *
     * @return ZedOrder
     */
    public function getZedOrder()
    {
        return $this->zedOrder;
    }
}
