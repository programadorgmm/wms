<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Natue\Bundle\StockBundle\Entity\OrderRequest;

/**
 * Check if the csv file is valid
 */
class PurchaseOrderItemXml
{
    /**
     * @Assert\Valid()
     * @Assert\File(
     *     maxSize          = "10M",
     *     maxSizeMessage   = "The file is too large. Allowed maximum size is 10 MegaBytes.",
     *     mimeTypes        = {"application/xml", "text/plain"},
     *     mimeTypesMessage = "The file uploaded is not a XML file. Please upload a xml file.",
     *     notFoundMessage  = "The XML file could not be found."
     * )
     */
    protected $submitFile;

    /**
     * @var integer
     *
     * @Assert\NotBlank(
     *      message    = "Order Request should not be blank"
     * )
     */
    protected $orderRequest;

    /**
     * @var float
     */
    protected $shippingCost;

    /**
     * @return mixed
     */
    public function getSubmitFile()
    {
        return $this->submitFile;
    }

    /**
     * @param $submitFile
     *
     * @return void
     */
    public function setSubmitFile($submitFile)
    {
        $this->submitFile = $submitFile;
    }

    /**
     * @return int
     */
    public function getOrderRequest()
    {
        return $this->orderRequest;
    }

    /**
     * @param int $orderRequest
     */
    public function setOrderRequest($orderRequest)
    {
        $this->orderRequest = $orderRequest;
    }

    /**
     * @return float
     */
    public function getShippingCost()
    {
        return $this->shippingCost;
    }

    /**
     * @param float $shippingCost
     */
    public function setShippingCost($shippingCost)
    {
        $this->shippingCost = $shippingCost;
    }

}
