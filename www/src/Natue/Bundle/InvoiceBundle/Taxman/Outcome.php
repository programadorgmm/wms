<?php

namespace Natue\Bundle\InvoiceBundle\Taxman;

use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ArrayableInterface;

/**
 * Class Outcome
 * @package Natue\Bundle\InvoiceBundle\Taxman
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class Outcome implements ArrayableInterface
{
    const CREATED = 'created';
    const REJECTED = 'rejected';
    const DISPOSABLE = 'disposable';

    /**
     * @var bool
     */
    protected $success;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $nfeKey;

    /**
     * @var string
     */
    protected $nfeXml;

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param boolean $success
     * @return $this
     */
    public function withSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     * @return $this
     */
    public function withReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function withStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getNfeKey()
    {
        return $this->nfeKey;
    }

    /**
     * @param string $nfeKey
     * @return $this
     */
    public function withNfeKey($nfeKey)
    {
        $this->nfeKey = $nfeKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getNfeXml()
    {
        return $this->nfeXml;
    }

    /**
     * @param string $nfeXml
     * @return $this
     */
    public function withNfeXml($nfeXml)
    {
        $this->nfeXml = $nfeXml;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'is_success' => $this->success,
            'reason'     => $this->reason,
            'status'     => $this->status,
            'nfe_key'    => $this->nfeKey,
            'nfe_xml'    => $this->nfeXml,
        ];
    }
}
