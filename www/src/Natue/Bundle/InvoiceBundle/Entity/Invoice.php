<?php

namespace Natue\Bundle\InvoiceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\StockItem;

/**
 * Invoice
 *
 * @ORM\Table(name="invoice")
 * @ORM\Entity(repositoryClass="Natue\Bundle\InvoiceBundle\Repository\InvoiceRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Invoice
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber
     *
     * @ORM\ManyToOne(targetEntity="InvoiceNumber", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="invoice_number_id", referencedColumnName="id", nullable=false)
     */
    private $invoiceNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="EnumInvoiceStatusType")
     */
    private $status;

    /**
     * @var \Natue\Bundle\StockBundle\Entity\PurchaseOrder
     *
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\StockBundle\Entity\PurchaseOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_order_id", referencedColumnName="id")
     * })
     */
    private $purchaseOrder;

    /**
     * @var string
     *
     * @ORM\Column(name="nfe_key", type="string", length=44, nullable=true)
     */
    private $nfeKey;

    /**
     * @var string
     *
     * @ORM\Column(name="nfe_xml", type="text", nullable=true)
     */
    private $nfeXml;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\Natue\Bundle\StockBundle\Entity\StockItem[]
     *
     * @ORM\ManyToMany(targetEntity="Natue\Bundle\StockBundle\Entity\StockItem", inversedBy="invoice")
     * @ORM\JoinTable(
     *     name="invoice_item",
     *     joinColumns={@ORM\JoinColumn(name="invoice_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="stock_item_id", referencedColumnName="id", unique=true)}
     * )
     */
    private $items;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->items = new ArrayCollection();
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
     * Set invoiceNumberId
     *
     * @param \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber $invoiceNumber
     * @return Invoice
     */
    public function setInvoiceNumber(InvoiceNumber $invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    /**
     * Get invoiceNumber
     *
     * @return \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Invoice
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set purchaseOrder
     *
     * @param \Natue\Bundle\StockBundle\Entity\PurchaseOrder $purchaseOrder
     * @return Invoice
     */
    public function setPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    /**
     * Get purchaseOrder
     *
     * @return \Natue\Bundle\StockBundle\Entity\PurchaseOrder
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * Set nfeKey
     *
     * @param string $nfeKey
     * @return Invoice
     */
    public function setNfeKey($nfeKey)
    {
        $this->nfeKey = $nfeKey;

        return $this;
    }

    /**
     * Get nfeKey
     *
     * @return string
     */
    public function getNfeKey()
    {
        return $this->nfeKey;
    }

    /**
     * Set nfeXml
     *
     * @param string $nfeXml
     * @return Invoice
     */
    public function setNfeXml($nfeXml)
    {
        $this->nfeXml = $nfeXml;

        return $this;
    }

    /**
     * Get nfeXml
     *
     * @return string
     */
    public function getNfeXml()
    {
        return $this->nfeXml;
    }

    /**
     * Push to items
     *
     * @param \Natue\Bundle\StockBundle\Entity\StockItem $item
     * @return Invoice
     */
    public function addItem(StockItem $item)
    {
        $this->items->add($item);

        return $this;
    }

    /**
     * Remove from items
     *
     * @param \Natue\Bundle\StockBundle\Entity\StockItem $item
     * @return Invoice
     */
    public function removeItem(StockItem $item)
    {
        $this->items->remove($item);

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Natue\Bundle\StockBundle\Entity\StockItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Invoice
     */
    public function setCreatedAt(\DateTime $createdAt)
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
     * @return Invoice
     */
    public function setUpdatedAt(\DateTime $updatedAt)
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
     * Gets triggered only on insert
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->setCreatedAt(new \DateTime("now"));
    }

    /**
     * Gets triggered every time on update
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->setUpdatedAt(new \DateTime("now"));
    }
}
