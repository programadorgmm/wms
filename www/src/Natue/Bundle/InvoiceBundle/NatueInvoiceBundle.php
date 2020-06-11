<?php

namespace Natue\Bundle\InvoiceBundle;

use Natue\Bundle\CoreBundle\AbstractBundle;
use Natue\Bundle\InvoiceBundle\Entity\ColumnType\EnumInvoiceStatusType;

/**
 * Class NatueInvoiceBundle
 * @package Natue\Bundle\InvoiceBundle
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class NatueInvoiceBundle extends AbstractBundle
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->loadCustomTypes([
            EnumInvoiceStatusType::class => 'EnumInvoiceStatusType',
        ]);

        parent::boot();
    }
}
