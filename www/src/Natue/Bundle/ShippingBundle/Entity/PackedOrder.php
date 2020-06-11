<?php

namespace Natue\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;

/**
 * @ORM\Entity(repositoryClass="Natue\Bundle\ShippingBundle\Repository\PackedOrderRepository")
 * @ORM\Table(name="packed_order")
 * @ORM\HasLifecycleCallbacks
 */
class PackedOrder
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\UserBundle\Entity\User", inversedBy="packedOrders")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="Natue\Bundle\ZedBundle\Entity\ZedOrder", inversedBy="packedOrder")
     * @ORM\JoinColumn(name="zed_order", referencedColumnName="id")
     */
    private $zedOrder;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

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
     * @param \DateTime $createdAt
     * @return ShippingPackage
     */
    protected function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return ShippingPackage
     */
    protected function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }
}
