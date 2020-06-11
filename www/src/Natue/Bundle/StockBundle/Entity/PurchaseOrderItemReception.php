<?php

namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrderItemReception
 *
 * @ORM\Table(
 *  name="purchase_order_item_reception",
 *  indexes={@ORM\Index(name="purchase_order_item_reception_fk_user", columns={"user"})}
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\StockBundle\Repository\PurchaseOrderItemReceptionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PurchaseOrderItemReception
{
    /**
     * @var integer
     * @ORM\Column(name="volumes", type="integer", nullable=false)
     */
    private $volumes;

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
     * @var \Natue\Bundle\StockBundle\Entity\PurchaseOrder
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\StockBundle\Entity\PurchaseOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_order", referencedColumnName="id")
     * })
     */
    private $purchaseOrder;

    /**
     * @var \Natue\Bundle\UserBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id")
     * })
     */
    private $user;

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
     * Set volumes
     *
     * @param integer $volumes
     *
     * @return PurchaseOrderItemReception
     */
    public function setVolumes($volumes)
    {
        $this->volumes = $volumes;

        return $this;
    }

    /**
     * Get volumes
     *
     * @return integer
     */
    public function getVolumes()
    {
        return $this->volumes;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PurchaseOrderItemReception
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
     * @return PurchaseOrderItemReception
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
     * Set PurchaseOrder
     *
     * @param \Natue\Bundle\StockBundle\Entity\PurchaseOrder $purchaseOrder
     *
     * @return PurchaseOrderItemReception
     */
    public function setPurchaseOrder($purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    /**
     * Get PurchaseOrder
     *
     * @return \Natue\Bundle\StockBundle\Entity\PurchaseOrder
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * Set user
     *
     * @param \Natue\Bundle\UserBundle\Entity\User $user
     *
     * @return PurchaseOrderItemReception
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
}
