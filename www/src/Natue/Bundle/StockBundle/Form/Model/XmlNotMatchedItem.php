<?php
namespace Natue\Bundle\StockBundle\Form\Model;

class XmlNotMatchedItem
{
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Sku should not be blank"
     * )
     */
    protected $zedProduct;

    /**
     * @var int
     * @Assert\NotBlank(
     *      message    = "Quantity should not be blank"
     * )
     */
    protected $quantity;

    /**
     * @var int
     */
    protected $nfeSequential;

    /**
     * @var string
     */
    protected $xmlCode;

    /**
     * @var int
     */
    protected $xmlQuantity;

    /**
     * @var string
     */
    protected $xmlDescription;

    /**
     * @return string
     */
    public function getZedProduct()
    {
        return $this->zedProduct;
    }

    /**
     * @param string $zedProduct
     * @return XmlNotMatchedItem $this
     */
    public function setZedProduct($zedProduct)
    {
        $this->zedProduct = $zedProduct;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return XmlNotMatchedItem $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlCode()
    {
        return $this->xmlCode;
    }

    /**
     * @param string $xmlCode
     * @return XmlNotMatchedItem $this
     */
    public function setXmlCode($xmlCode)
    {
        $this->xmlCode = $xmlCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getXmlQuantity()
    {
        return $this->xmlQuantity;
    }

    /**
     * @param int $xmlQuantity
     * @return XmlNotMatchedItem $this
     */
    public function setXmlQuantity($xmlQuantity)
    {
        $this->xmlQuantity = $xmlQuantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlDescription()
    {
        return $this->xmlDescription;
    }

    /**
     * @param string $xmlDescription
     * @return XmlNotMatchedItem $this
     */
    public function setXmlDescription($xmlDescription)
    {
        $this->xmlDescription = $xmlDescription;

        return $this;
    }

    /**
     * @return int
     */
    public function getNfeSequential()
    {
        return $this->nfeSequential;
    }

    /**
     * @param int $nfeSequential
     * @return $this
     */
    public function setNfeSequential($nfeSequential)
    {
        $this->nfeSequential = $nfeSequential;

        return $this;
    }
}