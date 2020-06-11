<?php

namespace Natue\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShippingTariff
 *
 * @ORM\Table(
 *  name="shipping_tariff",
 *  indexes={@ORM\Index(name="shipping_tariff_fk_logistics_provider", columns={"logistics_provider"})}
 * )
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ShippingTariff
{
    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="comment", type="string", length=255, nullable=true)
     */
    private $comment;

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
     * @var ShippingLogisticsProvider
     * @ORM\ManyToOne(targetEntity="ShippingLogisticsProvider", inversedBy="shippingTariffs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="logistics_provider", referencedColumnName="id")
     * })
     */
    private $logisticsProvider;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    public function matches($otherId)
    {
        return $this->getId() == $otherId;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function setUpdatedDate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ShippingTariff
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return ShippingTariff
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ShippingTariff
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
     * @return ShippingTariff
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
     * Set logisticsProvider
     *
     * @param ShippingLogisticsProvider $logisticsProvider
     *
     * @return ShippingTariff
     */
    public function setLogisticsProvider(ShippingLogisticsProvider $logisticsProvider = null)
    {
        $this->logisticsProvider = $logisticsProvider;

        return $this;
    }

    /**
     * Get logisticsProvider
     *
     * @return ShippingLogisticsProvider
     */
    public function getLogisticsProvider()
    {
        return $this->logisticsProvider;
    }
}
