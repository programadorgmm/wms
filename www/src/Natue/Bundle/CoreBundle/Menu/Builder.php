<?php

namespace Natue\Bundle\CoreBundle\Menu;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Knp\Menu\FactoryInterface;

use Natue\Bundle\CoreBundle\Menu\AbstractBuilder;

/**
 * Nav bar menu builder
 */
class Builder extends AbstractBuilder
{
    /**
     * Create main menu for nav bar
     *
     * @param FactoryInterface $factory
     * @param array            $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function mainMenu(FactoryInterface $factory, $options)
    {
        $menu = [
            'Stock'  => [
                'child' => [
                    'Items' => [
                        'route' => 'stock_item_list',
                        'roles' => ['ROLE_ADMIN', 'ROLE_STOCK_ITEM_READ']
                    ],
                    'Purchase Orders'  => [
                        'route' => 'stock_purchase_order_list',
                        'roles' => ['ROLE_ADMIN', 'ROLE_STOCK_PURCHASE_ORDER_READ']
                    ],
                    'Order Request' => [
                        'route' => 'order-request',
                        'roles' => ['ROLE_ADMIN', 'ROLE_STOCK_PURCHASE_ORDER_READ']
                    ],
                    'Receive Purchase Order'  => [
                        'route' => 'stock_purchase_order_receive_volumes',
                        'roles' => ['ROLE_ADMIN']
                    ],
                    'Positions' => [
                        'route' => 'stock_position_list',
                        'roles' => ['ROLE_ADMIN', 'ROLE_STOCK_POSITION_READ']
                    ],
                    'Inventory' => [
                        'route' => 'stock_inventory_list',
                        'roles' => ['ROLE_ADMIN', 'ROLE_STOCK_INVENTORY_LIST']
                    ],
                    'Actions'         => [
                        'dropdown-header' => true,
                        'child'           => [
                            'Move by position' => [
                                'route' => 'stock_item_move_from_position',
                                'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_STOCK_ITEM_MOVE_FROM_POSITION']
                            ],
                            'Move items from barcode' => [
                                'route' => 'stock_item_move',
                                'roles' => ['ROLE_ADMIN', 'ROLE_STOCK_ITEM_MOVE']
                            ]
                        ]
                    ],
                    'History' => [
                        'dropdown-header' => true,
                        'child'           => [
                            'Average Products Cost' => [
                                'route' => 'stock_item_average_cost',
                                'roles' => ['ROLE_ADMIN']
                            ],
                        ],
                    ],
                ],
            ],
            'Picking & Shipping' => [
                'child' => [
                    'Picking List' => [
                        'dropdown-header' => true,
                        'child'           => [
                            'Collect New Orders'        => [
                                'route' => 'shipping_picking_prepare',
                                'roles' => ['ROLE_ADMIN', 'ROLE_SHIPPING_PICKING_LIST'],
                            ],
                            'Collected Orders and Labels'           => [
                                'route' => 'shipping_picking_list',
                                'roles' => ['ROLE_ADMIN', 'ROLE_SHIPPING_PICKING_LIST'],
                            ],
                            'Conference' => [
                                'route' => 'shipping_picking_find_order_by_increment_id',
                                'roles' => ['ROLE_ADMIN', 'ROLE_SHIPPING_PICKING_LIST'],
                            ],
                        ],
                    ],
                    'Packing' => [
                        'dropdown-header' => true,
                        'child'           => [
                            'Pack Orders' => [
                                'route' => 'shipping_packing_items',
                                'roles' => ['ROLE_ADMIN'],
                            ],
                        ],
                    ],
                    'Shipping' => [
                        'dropdown-header' => true,
                        'child'           => [
                            'Expedit Orders' => [
                                'route' => 'shipping_packing_select_provider_and_package',
                                'roles' => ['ROLE_ADMIN'],
                            ],
                            'Expedit Control' => [
                                'route' => 'shipping_packing_expedition_control',
                                'roles' => ['ROLE_ADMIN'],
                            ],
                        ],
                    ],
                ],
            ],
            'Orders' => [
                'child' => [
                    'Return' => [
                        'route' => 'stock_order_return_select',
                        'roles' => ['ROLE_ADMIN', 'ROLE_STOCK_ORDER_RETURN'],
                    ],
                ],
            ],
            'System' => [
                'child' => [
                    'Groups' => [
                        'route' => 'user_group_list',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_USER_GROUP_READ']
                    ],
                    'Users'  => [
                        'route' => 'user_user_list',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_USER_USER_READ']
                    ],
                ],
            ],
        ];

        $menu = $this->buildFromArray($factory, $options, $menu);

        return $menu;
    }

    /**
     * Create right menu for nav bar
     *
     * @param FactoryInterface $factory
     * @param array            $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function rightMenu(FactoryInterface $factory, $options)
    {
        $userName = null;
        if ($this->securityContext->getToken()->getUser() != 'anon.') {
            $userName = $this->securityContext->getToken()->getUser()->getUsername();
        }

        $menu = [
            $userName => [
                'icon'  => 'user',
                'child' => [
                    'Change Password' => [
                        'route' => 'fos_user_change_password'
                    ],
                    'Logout'          => [
                        'route' => 'fos_user_security_logout'
                    ]
                ]
            ]
        ];

        $menu = $this->buildFromArray($factory, $options, $menu, 'nav navbar-nav pull-right');

        return $menu;
    }
}
