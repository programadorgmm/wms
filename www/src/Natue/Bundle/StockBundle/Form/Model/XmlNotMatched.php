<?php
namespace Natue\Bundle\StockBundle\Form\Model;

class XmlNotMatched
{
    /**
     * @var XmlNotMatchedItem[]
     */
    protected $items;

    /**
     * @return XmlNotMatchedItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param XmlNotMatchedItem[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

}