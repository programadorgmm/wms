<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductExtended
 *
 * @ORM\Table(name="product_extended")
 *
 * @ORM\Entity
 */
class ProductExtended
{
    /**
     * @var integer
     * @ORM\Column(name="cost_average", type="integer", nullable=true)
     */
    private $costAverage;

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
     * @var ZedProduct
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="ZedProduct")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="id")
     * })
     */
    private $id;


    /**
     * Set costAverage
     *
     * @param integer $costAverage
     *
     * @return ProductExtended
     */
    public function setCostAverage($costAverage)
    {
        $this->costAverage = $costAverage;

        return $this;
    }

    /**
     * Get costAverage
     *
     * @return integer
     */
    public function getCostAverage()
    {
        return $this->costAverage;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ProductExtended
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
     * @return ProductExtended
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
     * @param ZedProduct $id
     *
     * @return ProductExtended
     */
    public function setId(ZedProduct $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return ZedProduct
     */
    public function getId()
    {
        return $this->id;
    }
}
