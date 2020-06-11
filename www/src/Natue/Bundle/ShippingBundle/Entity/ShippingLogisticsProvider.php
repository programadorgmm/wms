<?php

namespace Natue\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="shipping_logistics_provider")
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Natue\Bundle\ShippingBundle\Repository\ShippingLogisticsProviderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ShippingLogisticsProvider
{
    /**
     * @var string
     * @ORM\Column(name="name_internal", type="string", length=255, nullable=false)
     */
    private $nameInternal;

    /**
     * @var string
     * @ORM\Column(name="name_official", type="string", length=255, nullable=false)
     */
    private $nameOfficial;

    /**
     * @var string
     * @ORM\Column(name="cnpj", type="string", length=255, nullable=true)
     */
    private $cnpj;

    /**
     * @var string
     * @ORM\Column(name="ie", type="string", length=255, nullable=true)
     */
    private $ie;

    /**
     * @var string
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     * @ORM\Column(name="cep", type="string", length=255, nullable=true)
     */
    private $cep;

    /**
     * @var boolean
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

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
     * @ORM\OneToMany(targetEntity="ShippingTariff", mappedBy="logisticsProvider")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shipping_tariff", referencedColumnName="logistics_provider")
     * })
     */
    private $shippingTariffs;

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @param int $otherId
     * @return boolean
     */
    public function matchesShippingTariff($otherId)
    {
        foreach ($this->getShippingTariffs() as $tariff) {
            if ($tariff->matches($otherId)) {
                return true;
            }
        }
        return false;
    }

    public function getShippingTariffs()
    {
        return $this->shippingTariffs;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function setUpdatedDate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * Set nameInternal
     *
     * @param string $nameInternal
     *
     * @return ShippingLogisticsProvider
     */
    public function setNameInternal($nameInternal)
    {
        $this->nameInternal = $nameInternal;

        return $this;
    }

    /**
     * Get nameInternal
     *
     * @return string
     */
    public function getNameInternal()
    {
        return $this->nameInternal;
    }

    /**
     * Set nameOfficial
     *
     * @param string $nameOfficial
     *
     * @return ShippingLogisticsProvider
     */
    public function setNameOfficial($nameOfficial)
    {
        $this->nameOfficial = $nameOfficial;

        return $this;
    }

    /**
     * Get nameOfficial
     *
     * @return string
     */
    public function getNameOfficial()
    {
        return $this->nameOfficial;
    }

    /**
     * Set cnpj
     *
     * @param string $cnpj
     *
     * @return ShippingLogisticsProvider
     */
    public function setCnpj($cnpj)
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    /**
     * Get cnpj
     *
     * @return string
     */
    public function getCnpj()
    {
        return $this->cnpj;
    }

    /**
     * Set ie
     *
     * @param string $ie
     *
     * @return ShippingLogisticsProvider
     */
    public function setIe($ie)
    {
        $this->ie = $ie;

        return $this;
    }

    /**
     * Get ie
     *
     * @return string
     */
    public function getIe()
    {
        return $this->ie;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return ShippingLogisticsProvider
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set cep
     *
     * @param string $cep
     *
     * @return ShippingLogisticsProvider
     */
    public function setCep($cep)
    {
        $this->cep = $cep;

        return $this;
    }

    /**
     * Get cep
     *
     * @return string
     */
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return ShippingLogisticsProvider
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ShippingLogisticsProvider
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
     * @return ShippingLogisticsProvider
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
}
