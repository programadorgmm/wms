<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Natue\Bundle\StockBundle\Entity\PurchaseOrderItemReception;

/**
 * ProductCostAverageHistory
 *
 * @ORM\Table(
 *  name="product_cost_average_history",
 *  indexes={
 *    @ORM\Index(name="product_cost_avarage_history_fk_zed_product", columns={"zed_product"}),
 *    @ORM\Index(
 *      name="product_cost_average_history_fk_purchase_order_item_reception",
 *      columns={"purchase_order_item_reception"}
 *    )
 *  }
 * )
 *
 * @ORM\Entity(repositoryClass="Natue\Bundle\ZedBundle\Repository\ProductCostAverageHistoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductCostAverageHistory
{
    /**
     * @var int
     * @ORM\Column(name="cost_average", type="integer", nullable=false)
     */
    private $costAverage;

    /**
     * @var int
     * @ORM\Column(name="previous_count", type="integer", nullable=true)
     */
    private $previousCount;

    /**
     * @var int
     * @ORM\Column(name="previous_cost", type="integer", nullable=true)
     */
    private $previousCost;

    /**
     * @var int
     * @ORM\Column(name="added_count", type="integer", nullable=true)
     */
    private $addedCount;

    /**
     * @var int
     * @ORM\Column(name="added_cost", type="integer", nullable=true)
     */
    private $addedCost;

    /**
     * @var PurchaseOrderItemReception
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\StockBundle\Entity\PurchaseOrderItemReception")
     * @ORM\JoinColumn(name="purchase_order_item_reception", referencedColumnName="id")
     */
    private $purchaseOrderItemReception;

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
     * @var ZedProduct
     * @ORM\ManyToOne(targetEntity="ZedProduct")
     * @ORM\JoinColumn(name="zed_product", referencedColumnName="id")
     */
    private $zedProduct;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @param int $costAverage
     * @return ProductCostAverageHistory
     */
    public function setCostAverage($costAverage)
    {
        $this->costAverage = round($costAverage);
        return $this;
    }

    /**
     * @return int
     */
    public function getCostAverage()
    {
        return $this->costAverage;
    }

    /**
     * @param \DateTime $createdAt
     * @return ProductCostAverageHistory
     */
    public function setCreatedAt($createdAt)
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
     * @return ProductCostAverageHistory
     */
    public function setUpdatedAt($updatedAt)
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
     * @param ZedProduct $zedProduct
     * @return ProductCostAverageHistory
     */
    public function setZedProduct(ZedProduct $zedProduct = null)
    {
        $this->zedProduct = $zedProduct;
        return $this;
    }

    /**
     * @return ZedProduct
     */
    public function getZedProduct()
    {
        return $this->zedProduct;
    }

    /**
     * @param int $previousCount
     * @return ProductCostAverageHistory
     */
    public function setPreviousCount($previousCount)
    {
        $this->previousCount = $previousCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getPreviousCount()
    {
        return $this->previousCount;
    }

    /**
     * @param int $previousCost
     * @return ProductCostAverageHistory
     */
    public function setPreviousCost($previousCost)
    {
        $this->previousCost = $previousCost;
        return $this;
    }

    /**
     * @return int
     */
    public function getPreviousCost()
    {
        return $this->previousCost;
    }

    /**
     * @param int $addedCount
     * @return ProductCostAverageHistory
     */
    public function setAddedCount($addedCount)
    {
        $this->addedCount = $addedCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getAddedCount()
    {
        return $this->addedCount;
    }

    /**
     * @param int $addedCost
     * @return ProductCostAverageHistory
     */
    public function setAddedCost($addedCost)
    {
        $this->addedCost = $addedCost;
        return $this;
    }

    /**
     * @return int
     */
    public function getAddedCost()
    {
        return $this->addedCost;
    }

    /**
     * @param PurchaseOrderItemReception $purchaseOrderItemReception
     * @return ProductCostAverageHistory
     */
    public function setPurchaseOrderItemReception(PurchaseOrderItemReception $purchaseOrderItemReception = null)
    {
        $this->purchaseOrderItemReception = $purchaseOrderItemReception;
        return $this;
    }

    /**
     * @return PurchaseOrderItemReception
     */
    public function getPurchaseOrderItemReception()
    {
        return $this->purchaseOrderItemReception;
    }

    /**
     * @return int
     */
    public function calculateCostAverage()
    {
        $average = (($this->getPreviousCost() * $this->getPreviousCount())
            + ($this->getAddedCost() * $this->getAddedCount()))
            / ($this->getPreviousCount() + $this->getAddedCount());

        $this->setCostAverage($average);

        return $this->getCostAverage();
    }

    /**
     * @param int $newCost
     * @return int
     */
    public function updateCostPerItem($newCost)
    {
        $this->setAddedCost($newCost);
        $this->calculateCostAverage();
    }

    /**
     * @param int $newCost
     * @return int
     */
    public function updatePreviousCost($newCost)
    {
        $this->setPreviousCost($newCost);
        $this->calculateCostAverage();
    }
}
