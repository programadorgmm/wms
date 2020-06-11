<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class PurchaseOrderVolumeConfirmation
 * @package Natue\Bundle\StockBundle\Form\Model
 */
class PurchaseOrderVolumeConfirmation
{
    /**
     * @var int
     */
    private $purchaseOrderId;

    /**
     * @param int $purchaseOrderId
     */
    public function setPurchaseOrderId($purchaseOrderId)
    {
        $this->purchaseOrderId = $purchaseOrderId;
    }

    /**
     * @return int
     */
    public function getPurchaseOrderId()
    {
        return $this->purchaseOrderId;
    }
}
