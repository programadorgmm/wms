<?php
namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * @ORM\Entity(repositoryClass="Natue\Bundle\StockBundle\Repository\OrderRequestItemRepository")
 *
 * @ORM\Table(
 *  name="order_request_item"
 * )
 */
class OrderRequestItem
{
    /**
     * @var OrderRequest
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\StockBundle\Entity\OrderRequest", inversedBy="items")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_request", referencedColumnName="id")
     * })
     */
    protected $orderRequest;

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Natue\Bundle\ZedBundle\Entity\ZedProduct
     *
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ZedBundle\Entity\ZedProduct")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_product", referencedColumnName="id")
     * })
     */
    protected $zedProduct;

    /**
     * @var int
     * @ORM\Column(name="quantity", type="integer", length=11, nullable=false)
     */
    protected $quantity;

    /**
     * @var int
     * @ORM\Column(name="requested_invoice_cost", type="integer", length=11, nullable=false)
     */
    protected $requestedInvoiceCost;

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
     * Set quantity
     *
     * @param integer $quantity
     * @return OrderRequestItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set orderRequest
     *
     * @param OrderRequest $orderRequest
     * @return OrderRequestItem
     */
    public function setOrderRequest(OrderRequest $orderRequest = null)
    {
        $this->orderRequest = $orderRequest;

        return $this;
    }

    /**
     * Get orderRequest
     *
     * @return \Natue\Bundle\StockBundle\Entity\OrderRequest
     */
    public function getOrderRequest()
    {
        return $this->orderRequest;
    }

    /**
     * Set zedProduct
     *
     * @param  ZedProduct $zedProduct
     * @return OrderRequestItem
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
     * Set requested invoice cost
     *
     * @param integer $requestedInvoiceCost
     * @return OrderRequestItem
     */
    public function setRequestedInvoiceCost($requestedInvoiceCost)
    {
        $this->requestedInvoiceCost = $requestedInvoiceCost;

        return $this;
    }

    /**
     * Get requested invoice cost
     *
     * @return integer
     */
    public function getRequestedInvoiceCost()
    {
        return $this->requestedInvoiceCost;
    }
}
