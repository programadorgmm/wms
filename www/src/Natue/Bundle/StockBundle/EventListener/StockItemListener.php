<?php
namespace Natue\Bundle\StockBundle\EventListener;

use Natue\Bundle\CoreBundle\AppEvent;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Natue\Bundle\StockBundle\Entity\StockPosition;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StockItemListener
{
    /**
     * Symfony < 2.6 has a bug which cause ServiceCircularReferenceException
     * if only the SecurityContext is passed as argument.
     * In 2.6 this bug was solved and the getToken()->getUser() method has been changed as well.
     * When update to >= 2.6, please update the -setCurrentOperator- method
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function preUpdate(LifeCycleEventArgs $args)
    {
        $stockItem = $args->getEntity();

        if (!$stockItem instanceof StockItem) {
            return ;
        }

        $this->setCurrentOperator($stockItem);

        $stockItem->setUpdatedAt(new \DateTime());
        if ($this->shouldChangeToDamaged($stockItem)) {
            $stockItem->setStatus(EnumStockItemStatusType::STATUS_DAMAGED);
        }
    }

    public function prePersist(LifeCycleEventArgs $args)
    {
        if ($args->getEntity() instanceof StockItem) {
            $args->getEntity()->setCreatedAt(new \DateTime());
        }
    }

    public function postUpdate(LifeCycleEventArgs $args)
    {
        /**
         * @var StockItem $stockItem
         */
        $stockItem = $args->getEntity();

        if (!$stockItem instanceof StockItem) {
            return ;
        }

        if ($this->shouldChangeToDamaged($stockItem)) {
            $barcode = $stockItem->getBarcode();
            $this->container->get('event_dispatcher')
                ->dispatch('stock_item.updated', (new AppEvent([
                    'barcode'     => $barcode,
                    'zed_product' => $stockItem->getZedProduct()->getId()
                ])));
        }

    }

    private function shouldChangeToDamaged(StockItem $stockItem)
    {
        $stockPosition = $stockItem->getStockPosition();

        return
            $stockPosition instanceof StockPosition
            && !$stockPosition->getPickable()
            && $stockPosition->getId() != StockPosition::WAITING_FOR_STORAGE_POSITION_ID
        ;
    }

    private function setCurrentOperator(StockItem $stockItem)
    {
        if ($this->getSecurityContext()->getToken()) {
            $stockItem->setUser($this->getSecurityContext()->getToken()->getUser());
        }
    }

    /**
     * Isolating the SF issue
     * Remember: passing the whole container is a really bad practice.
     */
    private function getSecurityContext()
    {
        return $this->container->get('security.context');
    }
}
