<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class PurchaseOrderReceive
 * @package Natue\Bundle\StockBundle\Form\Model
 */
class PurchaseOrderReceive
{
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "PurchaseOrderReference should not be blank"
     * )
     */
    private $purchaseOrderReference;

    /**
     * @var integer
     *
     * @Assert\NotBlank(
     *      message    = "Volumes should not be blank"
     * )
     * @Assert\Range(
     *      min        = 1,
     *      minMessage = "Volumes should be 1 at least"
     * )
     */
    private $volumes;

    /**
     * @param mixed $purchaseOrderReference
     */
    public function setPurchaseOrderReference($purchaseOrderReference)
    {
        $this->purchaseOrderReference = $purchaseOrderReference;
    }

    /**
     * @return mixed
     */
    public function getPurchaseOrderReference()
    {
        return $this->purchaseOrderReference;
    }

    /**
     * @param int $volumes
     */
    public function setVolumes($volumes)
    {
        $this->volumes = $volumes;
    }

    /**
     * @return int
     */
    public function getVolumes()
    {
        return $this->volumes;
    }
}
