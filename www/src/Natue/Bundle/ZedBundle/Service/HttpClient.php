<?php

namespace Natue\Bundle\ZedBundle\Service;

use Symfony\Component\HttpFoundation\Response;

class HttpClient extends HttpClientAbstract
{
    const URI_SET_TRACKING_CODE = 'sales/order-details/set-tracking-code';

    const URI_ORDER_DETAILS = 'sales/order-details/index/id/%d/';

    const URI_FIRE_PICKED_ALL_ITEMS_EVENT  = 'sales/order-details/fire-event-order/event/picked_all_items';
    const URI_FIRE_SHIPPED_ALL_ITEMS_EVENT = 'sales/order-details/fire-event-order/event/shipped';

    const URI_FIRE_PICKED_NOT_ALL_ITEMS_EVENT = 'sales/order-details/fire-event/event/picked_not_all_items';

    const URI_FIRE_IS_PICKING_ITEM_EVENT = 'sales/order-details/fire-event/event/back_to_ready_for_picking_item';

    const URI_STOCK_ITEMS_QUANTITY_UPDATE = 'stock/wms-api/items-quantity';

    const URI_SALES_CHECK_ORDER_STATUS = 'sales/wms-api/check-order-status';

    /**
     * @param $orderId
     *
     * @return string
     */
    public function getOrderDetailsUrl($orderId)
    {
        return $this->getZedHostName() . sprintf(self::URI_ORDER_DETAILS, $orderId);
    }

    /**
     * Set tracking code
     *
     * @param $code
     * @param $orderId
     *
     * @return bool
     */
    public function setTrackingCodeForOrderId($code, $orderId)
    {
        $query['query']['orderId'] = $orderId;
        $query['query']['code'] = $code;
        $request = $this->getRequest(
            self::URI_SET_TRACKING_CODE,
            $query
        );

        return $this->isSuccess($request->send());
    }

    /**
     * Check, if
     *
     * @param int    $id_sales_order
     * @param string $status
     *
     * @return bool
     */
    public function checkOrderStatus($id_sales_order, $status)
    {
        $query['query']['id_sales_order'] = $id_sales_order;
        $query['query']['status'] = $status;
        $request = $this->getRequest(
            self::URI_SALES_CHECK_ORDER_STATUS,
            compact('id_sales_order', 'status')
        );

        $response = $request->send();
        $body = json_decode($response->getBody(true));

        return ($this->isSuccess($response) && $body->success);
    }

    /**
     * ClarifyPickingFailed for SalesOrderItemId
     * Fire "picked_not_all_items" event
     *
     * @param $id_sales_order_item
     *
     * @return bool
     */
    public function clarifyPickingFailedForOrderItemId($id_sales_order_item)
    {
        $query['query']['id_sales_order_item'] = $id_sales_order_item;
        $request = $this->getRequest(
            self::URI_FIRE_PICKED_NOT_ALL_ITEMS_EVENT,
            $query
        );

        return $this->isSuccess($request->send());
    }

    /**
     * Trigger Picked All Items for Order Id
     * Fire "picked_all_items" event
     *
     * @param $id_sales_order
     *
     * @return bool
     */
    public function triggerPickedAllItemsForOrderId($id_sales_order)
    {
        $query['query']['id_sales_order'] = $id_sales_order;
        $request = $this->getRequest(
            self::URI_FIRE_PICKED_ALL_ITEMS_EVENT,
            $query
        );

        return $this->isSuccess($request->send());
    }

    /**
     * Set ReadyForInvoice for orderId
     * Fire "picked_all_items" event
     *
     * @param $id_sales_order
     *
     * @return bool
     */
    public function createInvoiceForOrderId($id_sales_order)
    {
        $uri = "/sales/order-details/fire-event-order/id_sales_order/$id_sales_order/event/create_invoice";
        $request = $this->getRequest($uri);
        return $this->isSuccess($request->send());
    }

    /**
     * Set Shipped for orderId
     * Fire "shipped" event
     *
     * @param $id_sales_order
     *
     * @return bool
     */
    public function setShippedForOrderId($id_sales_order)
    {
        $query['query']['id_sales_order'] = $id_sales_order;
        $request = $this->getRequest(
            self::URI_FIRE_SHIPPED_ALL_ITEMS_EVENT,
            $query
        );

        return $this->isSuccess($request->send());
    }

    /**
     * @param $data
     * @throws \DomainException when something is wrong
     * @return string
     */
    public function postCurrentStock($data)
    {
        $request = $this->postRequest(
            self::URI_STOCK_ITEMS_QUANTITY_UPDATE,
            $data
        );

        $response = $request->send();
        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new \DomainException((string) $response->getBody());
        }

        return (string) $response->getBody();
    }

    /**
     * isPickingForOrderItemId for SalesOrderItemId
     * Fire "picked_not_all_items" event
     *
     * @param $id_sales_order_item
     *
     * @return bool
     */
    public function isPickingForOrderItemId($id_sales_order_item)
    {
        $query['query']['id_sales_order_item'] = $id_sales_order_item;
        $request = $this->getRequest(
            self::URI_FIRE_IS_PICKING_ITEM_EVENT,
            $query
        );

        return $this->isSuccess($request->send());
    }
}
