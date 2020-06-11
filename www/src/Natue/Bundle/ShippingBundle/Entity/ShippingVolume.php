<?php

namespace Natue\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShippingVolume
 *
 * @ORM\Table(
 *  name="shipping_volume",
 *  indexes={@ORM\Index(name="shipping_volume_fk_shipping_packaging", columns={"shipping_package"}),
 * @ORM\Index(name="shipping_volume_fk_zed_order", columns={"zed_order"}),
 * @ORM\Index(name="shipping_volume_fk_user", columns={"user"})}
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\ShippingBundle\Repository\ShippingVolumeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ShippingVolume
{
    /**
     * @var string
     * @ORM\Column(name="tracking_code", type="string", length=255, nullable=true)
     */
    private $trackingCode;

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
     * @var \Natue\Bundle\ZedBundle\Entity\ZedOrder
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ZedBundle\Entity\ZedOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_order", referencedColumnName="id")
     * })
     */
    private $zedOrder;

    /**
     * @var ShippingPackage
     * @ORM\ManyToOne(targetEntity="ShippingPackage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shipping_package", referencedColumnName="id")
     * })
     */
    private $shippingPackage;

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
     * Set trackingCode
     *
     * @param string $trackingCode
     *
     * @return ShippingVolume
     */
    public function setTrackingCode($trackingCode)
    {
        $this->trackingCode = $trackingCode;

        return $this;
    }

    /**
     * Get trackingCode
     *
     * @return string
     */
    public function getTrackingCode()
    {
        return $this->trackingCode;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ShippingVolume
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
     * @return ShippingVolume
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
     * @return ShippingVolume
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
     * Set zedOrder
     *
     * @param \Natue\Bundle\ZedBundle\Entity\ZedOrder $zedOrder
     *
     * @return ShippingVolume
     */
    public function setZedOrder(\Natue\Bundle\ZedBundle\Entity\ZedOrder $zedOrder = null)
    {
        $this->zedOrder = $zedOrder;

        return $this;
    }

    /**
     * Get zedOrder
     *
     * @return \Natue\Bundle\ZedBundle\Entity\ZedOrder
     */
    public function getZedOrder()
    {
        return $this->zedOrder;
    }

    /**
     * Set shippingPackage
     *
     * @param ShippingPackage $shippingPackage
     *
     * @return ShippingVolume
     */
    public function setShippingPackage(ShippingPackage $shippingPackage = null)
    {
        $this->shippingPackage = $shippingPackage;

        return $this;
    }

    /**
     * Get shippingPackage
     *
     * @return ShippingPackage
     */
    public function getShippingPackage()
    {
        return $this->shippingPackage;
    }
}
