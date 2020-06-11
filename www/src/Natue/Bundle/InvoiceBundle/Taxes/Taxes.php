<?php

namespace Natue\Bundle\InvoiceBundle\Taxes;

/**
 * Class Taxes
 * @package Natue\Bundle\InvoiceBundle\Taxes
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class Taxes
{
    /**
     * @var \Natue\Bundle\InvoiceBundle\Taxes\Entities\Cofins
     */
    protected $cofins;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Taxes\Entities\Icms
     */
    protected $icms;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Taxes\Entities\Pis
     */
    protected $pis;

    /**
     * @return Entities\Cofins
     */
    public function getCofins()
    {
        return $this->cofins;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxes\Entities\Cofins $cofins
     */
    public function setCofins(Entities\Cofins $cofins)
    {
        $this->cofins = $cofins;
    }

    /**
     * @return \Natue\Bundle\InvoiceBundle\Taxes\Entities\Icms
     */
    public function getIcms()
    {
        return $this->icms;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxes\Entities\Icms $icms
     */
    public function setIcms(Entities\Icms $icms)
    {
        $this->icms = $icms;
    }

    /**
     * @return Entities\Pis
     */
    public function getPis()
    {
        return $this->pis;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxes\Entities\Pis $pis
     */
    public function setPis(Entities\Pis $pis)
    {
        $this->pis = $pis;
    }
}
