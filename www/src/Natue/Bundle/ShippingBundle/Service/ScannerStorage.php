<?php

namespace Natue\Bundle\ShippingBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @package Natue\Bundle\ShippingBundle\Service
 */
class ScannerStorage
{
    const SESSION_STORAGE_PREFIX = 'order_check_scanned_items';

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     * @return ScannerStorage
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param int $orderId
     * @return mixed
     */
    public function getScannedItems($orderId)
    {
        $items = $this->session->get($this->getOrderSessionKey($orderId));

        return ($items) ?: [];
    }

    /**
     * @param integer $orderId
     * @return int
     */
    public function getTotalScannedItems($orderId)
    {
        return array_sum($this->getScannedItems($orderId));
    }

    /**
     * @param integer $orderId
     */
    public function setPickingObservationReadStatus($orderId)
    {
        $this->session->set("{$orderId}_picking_observation", true);
    }

    /**
     * @param integer $orderId
     * @return bool
     */
    public function getPickingObservationReadStatus($orderId)
    {
        return $this->session->get("{$orderId}_picking_observation");
    }

    /**
     * @param int $orderId
     * @param $data
     * @return void
     */
    public function setScannedItems($orderId, $data)
    {
        $key = $this->getOrderSessionKey($orderId);

        $this->session->set($key, $data);
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function clearScannedItems($orderId)
    {
        $key = $this->getOrderSessionKey($orderId);

        $this->session->remove($key);
        $this->session->remove("{$orderId}_picking_observation");
    }

    /**
     * @param int    $orderId
     * @param string $barcode
     *
     * @return void
     */
    public function addItemBarcode($orderId, $barcode)
    {
        $scannedItems = $this->getScannedItems($orderId);

        if (!isset($scannedItems[$barcode])) {
            $scannedItems[$barcode] = 0;
        }
        $scannedItems[$barcode]++;

        $this->setScannedItems($orderId, $scannedItems);
    }

    /**
     * @param int    $orderId
     * @param string $barcode
     * @param int $barcodeTotal
     * @return boolean
     */
    public function canReceiveBarcode($orderId, $barcode, $barcodeTotal)
    {
        if (!$this->hasBarcode($orderId, $barcode)) {
            return true;
        }

        return $barcodeTotal > $this->getScannedItems($orderId)[$barcode];
    }

    /**
     * @param int    $orderId
     * @param string $barcode
     * @return boolean
     */
    public function hasBarcode($orderId, $barcode)
    {
        return array_key_exists($barcode, $this->getScannedItems($orderId));
    }

    /**
     * @param int $orderId
     * @return string
     */
    private function getOrderSessionKey($orderId)
    {
        return self::SESSION_STORAGE_PREFIX . '-' . $orderId;
    }
}
