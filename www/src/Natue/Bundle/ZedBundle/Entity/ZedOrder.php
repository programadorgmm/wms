<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Natue\Bundle\ShippingBundle\Entity\ShippingLogisticsProvider;

/**
 * @ORM\Table(
 *  name="zed_order",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="zed_order_increment_id_UNIQUE", columns={"increment_id"})}
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\ZedBundle\Repository\ZedOrderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ZedOrder
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="increment_id", type="string", length=255, nullable=false)
     */
    private $incrementId;

    /**
     * @var string
     * @ORM\Column(name="customer_firstname", type="string", length=255, nullable=true)
     */
    private $customerFirstname;

    /**
     * @var string
     * @ORM\Column(name="customer_lastname", type="string", length=255, nullable=true)
     */
    private $customerLastname;

    /**
     * @var string
     * @ORM\Column(name="customer_cpf", type="string", length=45, nullable=true)
     */
    private $customerCpf;

    /**
     * @var string
     * @ORM\Column(name="customer_phone", type="string", length=45, nullable=true)
     */
    private $customerPhone;

    /**
     * @var string
     * @ORM\Column(name="customer_zipcode", type="string", length=15, nullable=true)
     */
    private $customerZipcode;

    /**
     * @var string
     * @ORM\Column(name="customer_address1", type="string", length=255, nullable=true)
     */
    private $customerAddress1;

    /**
     * @var string
     * @ORM\Column(name="customer_address2", type="string", length=255, nullable=true)
     */
    private $customerAddress2;

    /**
     * @var string
     * @ORM\Column(name="customer_quarter", type="string", length=150, nullable=true)
     */
    private $customerQuarter;

    /**
     * @var string
     * @ORM\Column(name="customer_additional", type="string", length=150, nullable=true)
     */
    private $customerAdditional;

    /**
     * @var string
     * @ORM\Column(name="customer_state", type="string", length=2, nullable=true)
     */
    private $customerState;

    /**
     * @var string
     * @ORM\Column(name="customer_city", type="string", length=255, nullable=true)
     */
    private $customerCity;

    /**
     * @var string
     * @ORM\Column(name="customer_address_reference", type="string", length=255, nullable=true)
     */
    private $customerAddressReference;

    /**
     * @var integer
     * @ORM\Column(name="price_shipping", type="integer", nullable=true)
     */
    private $priceShipping;

    /**
     * @var string
     * @ORM\Column(name="picking_observation", type="text", nullable=true)
     */
    private $pickingObservation;

    /**
     * @var integer
     * @ORM\Column(name="shipping_tariff_code", type="integer", nullable=true)
     */
    private $shippingTariffCode;

    /**
     * @var string
     * @ORM\Column(name="invoice_key", type="string", length=255, nullable=true)
     */
    private $invoiceKey;

    /**
     * @var integer
     * @ORM\Column(name="fk_subscription", type="integer", nullable=true)
     */
    private $fkSubscription;

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
     * @ORM\OneToMany(targetEntity="ZedOrderItem", mappedBy="zedOrder")
     */
    private $zedOrderItems;

    /**
     * @ORM\OneToOne(targetEntity="OrderExtended", mappedBy="zedOrder", orphanRemoval=true)
     */
    private $orderExtended;

    /**
     * @ORM\OneToOne(targetEntity="Natue\Bundle\ShippingBundle\Entity\PackedOrder", mappedBy="zedOrder", fetch="EAGER")
     */
    private $packedOrder;

    /**
     * @param string $incrementId
     * @return boolean
     */
    public function matches($incrementId)
    {
        return (strcasecmp($this->getIncrementId(), $incrementId) == 0);
    }

    /**
     * @param ShippingLogisticsProvider $logisticProvider
     * @return boolean
     */
    public function matchesShippingTariff(ShippingLogisticsProvider $logisticProvider)
    {
        return $logisticProvider->matchesShippingTariff($this->getShippingTariffCode());
    }

    /**
     * @param string $incrementId
     * @return ZedOrder
     */
    public function setIncrementId($incrementId)
    {
        $this->incrementId = $incrementId;

        return $this;
    }

    /**
     * Get incrementId
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->incrementId;
    }

    /**
     * Set customerFirstname
     *
     * @param string $customerFirstname
     *
     * @return ZedOrder
     */
    public function setCustomerFirstname($customerFirstname)
    {
        $this->customerFirstname = $customerFirstname;

        return $this;
    }

    /**
     * Get customerFirstname
     *
     * @return string
     */
    public function getCustomerFirstname()
    {
        return $this->customerFirstname;
    }

    /**
     * Set customerLastname
     *
     * @param string $customerLastname
     *
     * @return ZedOrder
     */
    public function setCustomerLastname($customerLastname)
    {
        $this->customerLastname = $customerLastname;

        return $this;
    }

    /**
     * Get customerLastname
     *
     * @return string
     */
    public function getCustomerLastname()
    {
        return $this->customerLastname;
    }

    /**
     * Set customerCpf
     *
     * @param string $customerCpf
     *
     * @return ZedOrder
     */
    public function setCustomerCpf($customerCpf)
    {
        $this->customerCpf = $customerCpf;

        return $this;
    }

    /**
     * Get customerCpf
     *
     * @return string
     */
    public function getCustomerCpf()
    {
        return $this->customerCpf;
    }

    /**
     * Set customerPhone
     *
     * @param string $customerPhone
     *
     * @return ZedOrder
     */
    public function setCustomerPhone($customerPhone)
    {
        $this->customerPhone = $customerPhone;

        return $this;
    }

    /**
     * Get customerPhone
     *
     * @return string
     */
    public function getCustomerPhone()
    {
        return $this->customerPhone;
    }

    /**
     * Set customerZipcode
     *
     * @param string $customerZipcode
     *
     * @return ZedOrder
     */
    public function setCustomerZipcode($customerZipcode)
    {
        $this->customerZipcode = $customerZipcode;

        return $this;
    }

    /**
     * Get customerZipcode
     *
     * @return string
     */
    public function getCustomerZipcode()
    {
        return $this->customerZipcode;
    }

    /**
     * Set customerAddress1
     *
     * @param string $customerAddress1
     *
     * @return ZedOrder
     */
    public function setCustomerAddress1($customerAddress1)
    {
        $this->customerAddress1 = $customerAddress1;

        return $this;
    }

    /**
     * Get customerAddress1
     *
     * @return string
     */
    public function getCustomerAddress1()
    {
        return $this->customerAddress1;
    }

    /**
     * Set customerAddress2
     *
     * @param string $customerAddress2
     *
     * @return ZedOrder
     */
    public function setCustomerAddress2($customerAddress2)
    {
        $this->customerAddress2 = $customerAddress2;

        return $this;
    }

    /**
     * Get customerAddress2
     *
     * @return string
     */
    public function getCustomerAddress2()
    {
        return $this->customerAddress2;
    }

    /**
     * Set customerQuarter
     *
     * @param string $customerQuarter
     *
     * @return ZedOrder
     */
    public function setCustomerQuarter($customerQuarter)
    {
        $this->customerQuarter = $customerQuarter;

        return $this;
    }

    /**
     * Get customerQuarter
     *
     * @return string
     */
    public function getCustomerQuarter()
    {
        return $this->customerQuarter;
    }

    /**
     * Set customerAdditional
     *
     * @param string $customerAdditional
     *
     * @return ZedOrder
     */
    public function setCustomerAdditional($customerAdditional)
    {
        $this->customerAdditional = $customerAdditional;

        return $this;
    }

    /**
     * Get customerAdditional
     *
     * @return string
     */
    public function getCustomerAdditional()
    {
        return $this->customerAdditional;
    }

    /**
     * Set customerState
     *
     * @param string $customerState
     *
     * @return ZedOrder
     */
    public function setCustomerState($customerState)
    {
        $this->customerState = $customerState;

        return $this;
    }

    /**
     * Get customerState
     *
     * @return string
     */
    public function getCustomerState()
    {
        return $this->customerState;
    }

    /**
     * Set customerCity
     *
     * @param string $customerCity
     *
     * @return ZedOrder
     */
    public function setCustomerCity($customerCity)
    {
        $this->customerCity = $customerCity;

        return $this;
    }

    /**
     * Get customerCity
     *
     * @return string
     */
    public function getCustomerCity()
    {
        return $this->customerCity;
    }

    /**
     * Set customerAddressReference
     *
     * @param string $customerAddressReference
     *
     * @return ZedOrder
     */
    public function setCustomerAddressReference($customerAddressReference)
    {
        $this->customerAddressReference = $customerAddressReference;

        return $this;
    }

    /**
     * Get customerAddressReference
     *
     * @return string
     */
    public function getCustomerAddressReference()
    {
        return $this->customerAddressReference;
    }

    /**
     * Set priceShipping
     *
     * @param integer $priceShipping
     *
     * @return ZedOrder
     */
    public function setPriceShipping($priceShipping)
    {
        $this->priceShipping = $priceShipping;

        return $this;
    }

    /**
     * Get priceShipping
     *
     * @return integer
     */
    public function getPriceShipping()
    {
        return $this->priceShipping;
    }

    /**
     * Set pickingObservation
     *
     * @param string $pickingObservation
     *
     * @return ZedOrder
     */
    public function setPickingObservation($pickingObservation)
    {
        $this->pickingObservation = $pickingObservation;

        return $this;
    }

    /**
     * Get pickingObservation
     *
     * @return string
     */
    public function getPickingObservation()
    {
        return $this->pickingObservation;
    }

    /**
     * Set shippingTariffCode
     *
     * @param integer $shippingTariffCode
     *
     * @return ZedOrder
     */
    public function setShippingTariffCode($shippingTariffCode)
    {
        $this->shippingTariffCode = $shippingTariffCode;

        return $this;
    }

    /**
     * Get shippingTariffCode
     *
     * @return integer
     */
    public function getShippingTariffCode()
    {
        return $this->shippingTariffCode;
    }

    /**
     * @return string
     */
    public function getInvoiceKey()
    {
        return $this->invoiceKey;
    }

    /**
     * Set invoiceKey
     *
     * @param string $invoiceKey
     *
     * @return ZedOrder
     */
    public function setInvoiceKey($invoiceKey)
    {
        $this->invoiceKey = $invoiceKey;

        return $this;
    }


    /**
     * Set fkSubscription
     *
     * @param integer $fkSubscription
     *
     * @return ZedOrder
     */
    public function setFkSubscription($fkSubscription)
    {
        $this->fkSubscription = $fkSubscription;

        return $this;
    }

    /**
     * Get fkSubscription
     *
     * @return integer
     */
    public function getFkSubscription()
    {
        return $this->fkSubscription;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ZedOrder
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
     * @return ZedOrder
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
     * @return ZedOrder
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection|ZedOrderItem[]
     */
    public function getZedOrderItems()
    {
        return $this->zedOrderItems;
    }

    public function getOrderExtended()
    {
        return $this->orderExtended;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->zedOrderItems = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add zedOrderItems
     *
     * @param \Natue\Bundle\ZedBundle\Entity\ZedOrderItem $zedOrderItems
     * @return ZedOrder
     */
    public function addZedOrderItem(\Natue\Bundle\ZedBundle\Entity\ZedOrderItem $zedOrderItems)
    {
        $this->zedOrderItems[] = $zedOrderItems;

        return $this;
    }

    /**
     * Remove zedOrderItems
     *
     * @param \Natue\Bundle\ZedBundle\Entity\ZedOrderItem $zedOrderItems
     */
    public function removeZedOrderItem(\Natue\Bundle\ZedBundle\Entity\ZedOrderItem $zedOrderItems)
    {
        $this->zedOrderItems->removeElement($zedOrderItems);
    }

    /**
     * Set orderExtended
     *
     * @param \Natue\Bundle\ZedBundle\Entity\OrderExtended $orderExtended
     * @return ZedOrder
     */
    public function setOrderExtended(\Natue\Bundle\ZedBundle\Entity\OrderExtended $orderExtended = null)
    {
        $this->orderExtended = $orderExtended;

        return $this;
    }

    /**
     * Set packedOrder
     *
     * @param \Natue\Bundle\ShippingBundle\Entity\PackedOrder $packedOrder
     * @return ZedOrder
     */
    public function setPackedOrder(\Natue\Bundle\ShippingBundle\Entity\PackedOrder $packedOrder = null)
    {
        $this->packedOrder = $packedOrder;

        return $this;
    }

    /**
     * Get packedOrder
     *
     * @return \Natue\Bundle\ShippingBundle\Entity\PackedOrder 
     */
    public function getPackedOrder()
    {
        return $this->packedOrder;
    }

    public function removeOrderExtended()
    {
        unset($this->orderExtended);
    }
}
