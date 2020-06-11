<?php

namespace Natue\Bundle\InvoiceBundle\Taxes\Entities;

/**
 * Class Icms
 * @package Natue\Bundle\InvoiceBundle\Taxes\Entities
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class Icms extends AbstractTax
{
    const NAME = 'icms';

    /**
     * @var string
     */
    protected $origin;

    /**
     * @var int
     */
    protected $modality;

    /**
     * @var float
     */
    protected $aliquotForDonation;

    /**
     * @var float
     */
    protected $aliquotForDestination;

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return int
     */
    public function getModality()
    {
        return $this->modality;
    }

    /**
     * @param int $modality
     */
    public function setModality($modality)
    {
        $this->modality = $modality;
    }

    /**
     * @return float
     */
    public function getAliquotForDonation()
    {
        return $this->aliquotForDonation;
    }

    /**
     * @param float $aliquotForDonation
     */
    public function setAliquotForDonation($aliquotForDonation)
    {
        $this->aliquotForDonation = $aliquotForDonation;
    }

    /**
     * @return float
     */
    public function getAliquotForDestination()
    {
        return $this->aliquotForDestination;
    }

    /**
     * @param float $aliquotForDestination
     */
    public function setAliquotForDestination($aliquotForDestination)
    {
        $this->aliquotForDestination = $aliquotForDestination;
    }
}
