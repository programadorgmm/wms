<?php

namespace Natue\Bundle\StockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Natue\Bundle\StockBundle\Form\Model\OrderSelection;
use Natue\Bundle\StockBundle\Form\Type\OrderSelection as OrderSelectionForm;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;
use Natue\Bundle\ShippingBundle\Service\PickingManager;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\StockBundle\Form\Model\ProductToPosition;
use Natue\Bundle\StockBundle\Form\Type\ProductToPosition as ProductToPositionForm;
use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\StockBundle\Entity\StockItem;

/**
 * OrderReturn controller
 *
 * @Route("/order-return")
 */
class OrderReturnController extends Controller
{
    /**
     * Select Order for return
     *
     * @Route("/select", name="stock_order_return_select")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_ORDER_RETURN")
     *
     * @param Request $request
     *
     * @throws \Exception
     * @return array
     */
    public function selectAction(Request $request)
    {
        $orderSelection = new OrderSelection();
        $form           = $this->createForm(new OrderSelectionForm(), $orderSelection);

        if ($request->isMethod('post')) {

            $form->submit($request);

            try {
                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                /** @var PickingManager $pickingManager */
                $pickingManager = $this->get('natue.shipping.picking_manager');

                /** @var ZedOrder $order */
                $order = $pickingManager->findOrderByIncrementId($orderSelection->getIncrementId());

                if (!$order) {
                    throw new \Exception('Order not found.');
                }

                $redirectUrl = $this->generateUrl(
                    'stock_order_return_items_list',
                    [
                        'orderId' => $order->getId()
                    ]
                );

                return $this->redirect($redirectUrl);
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Display list of items within order
     *
     * @Route("/items-list/{orderId}", name="stock_order_return_items_list")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_ORDER_RETURN")
     *
     * @param Request $request
     * @param int     $orderId
     *
     * @throws \Exception
     * @return array
     */
    public function itemsListAction(Request $request, $orderId)
    {
        /** @var StockItemRepository $stockItemRepository */
        $stockItemRepository = $this->getDoctrine()->getRepository('NatueStockBundle:StockItem');
        $queryBuilder = $stockItemRepository->getOrderStockItemsQuery($orderId);

        /** @var \Natue\Bundle\StockBundle\Grid\OrderStockItemsGrid $grid */
        $grid = $this->get('pedroteixeira.grid')->createGrid('\Natue\Bundle\StockBundle\Grid\OrderStockItemsGrid');
        $grid->setUrl($this->generateUrl('stock_order_return_items_list', ['orderId' => $orderId]));
        $grid->setQueryBuilder($queryBuilder);

        if ($grid->isResponseAnswer()) {
            return $grid->render();
        }

        return [
            'grid'    => $grid->render(),
            'orderId' => $orderId,
        ];
    }

    /**
     * Move StockItem to StockPosition
     *
     * @Route("/move-product-to-position/{orderId}/{stockItemId}", name="stock_order_return_move_product_to_position")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_ORDER_RETURN")
     *
     * @param Request $request
     * @param int     $orderId
     * @param int     $stockItemId
     *
     * @throws \Exception
     * @return array
     */
    public function moveProductToPositionAction(Request $request, $orderId, $stockItemId)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        /** @var StockItemManager $stockItemManager */
        $stockItemManager = $this->get('natue.stock.item.manager');

        /** @var StockItem $stockItem */
        $stockItem = $stockItemManager->findStockItem($stockItemId);

        $productToPosition = new ProductToPosition();
        $productToPosition->setStockItemBarcode($stockItem->getBarcode());
        $form              = $this->createForm(new ProductToPositionForm(), $productToPosition);

        if ($request->isMethod('post')) {

            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $stockItemManager->tryReturningProductToPositionAndUpdateStatus(
                    $stockItem,
                    $productToPosition->getPosition(),
                    $productToPosition->getTransition()
                );

                $this->get('session')->getFlashBag()->add('success', 'StockItem was successfully moved');
                $redirectUrl = $this->generateUrl('stock_order_return_items_list', compact('orderId'));

                $entityManager->getConnection()->commit();

                return $this->redirect($redirectUrl);
            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'form'        => $form->createView(),
            'orderId'     => $orderId,
            'stockItemId' => $stockItemId,
        ];
    }

    /**
     * Confirm return order
     *
     * @Route("/confirm", name="stock_order_return_confirm")
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_ORDER_RETURN")
     *
     * @param Request $request
     *
     * @throws \Exception
     * @return RedirectResponse
     */
    public function confirmAction(Request $request)
    {
        $parameters = $request->request->get('natue_stockbundle_hidden_parameters');
        $orderId = $parameters['order_id'];

        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        /** @var StockItemManager $stockItemManager */
        $stockItemManager = $this->get('natue.stock.item.manager');

        try {
            $entityManager->getConnection()->beginTransaction();

            $stockItemManager->tryOrderReturnConfirmation($orderId);

            $this->get('session')->getFlashBag()->add('success', 'OrderReturn was successfully confirmed');

            $entityManager->getConnection()->commit();

            return $this->redirect($this->generateUrl('homepage'));
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();

            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            $redirectUrl = $this->generateUrl('stock_order_return_items_list', compact('orderId'));

            return $this->redirect($redirectUrl);
        }
    }
}
