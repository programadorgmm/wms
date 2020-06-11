<?php

namespace Natue\Bundle\StockBundle\Grid\Datatable;

use Natue\Bundle\StockBundle\Grid;
use Natue\Bundle\CoreBundle\Datatable\Column\AggregateColumn;
use Sg\DatatablesBundle\Datatable\Column\ColumnBuilderInterface;
use Sg\DatatablesBundle\Datatable\View;

/**
 * Class PurchaseOrderGridBuilder
 * @package Natue\Bundle\StockBundle\Grid\Datatable
 */
class PurchaseOrderGridBuilder
{
    /**
     * @var PurchaseOrderGrid
     */
    private $grid;

    /**
     * @var array
     */
    private $ajaxSettings;

    /**
     * @var array
     */
    private $gridOptions;

    /**
     * PurchaseOrderGridBuilder constructor.
     * @param PurchaseOrderGrid $grid
     */
    public function __construct(PurchaseOrderGrid $grid)
    {
        $this->grid = $grid;
        $this->gridOptions = [
            'page_length'                   => 25,
            'length_menu'                   => [50, 100, -1],
            'class'                         => View\Style::BOOTSTRAP_3_STYLE,
            'individual_filtering'          => true,
            'individual_filtering_position' => 'head',
            'use_integration_options'       => true,
            'search_delay'                  => 1500,
            'order'                         => [[0, 'desc']],
        ];
    }

    /**
     * @return ItemGrid
     */
    protected function getGrid()
    {
        return $this->grid;
    }

    /**
     * @return array
     */
    protected function getAjaxSettings()
    {
        return $this->ajaxSettings;
    }

    /**
     * @return array
     */
    protected function getGridOptions()
    {
        return $this->gridOptions;
    }

    /**
     * @return ColumnBuilderInterface
     */
    protected function getGridColumnBuilder()
    {
        return $this->getGrid()->getColumnBuilder();
    }

    /**
     * @return array
     */
    protected function getDefaultTextFilterOptions()
    {
        return ['text', ['search_type' => 'like']];
    }

    /**
     * @param string $column
     * @param string $title
     * @param array $options = []
     * @param string $columnType = 'column'
     *
     * @return $this
     */
    protected function addColumn($column, $title, $options = [], $columnType = 'column')
    {
        $this->getGridColumnBuilder()->add(
            $column,
            $columnType,
            array_merge(['title' => $title], $options)
        );

        return $this;
    }

    /**
     * @param $column
     * @param $title
     * @param array $options
     *
     * @return $this;
     */
    protected function addSimpleColumn($column, $title, $options = [])
    {
        if (!$options) {
            $options = ['filter' => $this->getDefaultTextFilterOptions()];
        }

        return $this->addColumn(
            $column,
            $title,
            $options
        );
    }

    /**
     * @return $this
     */
    protected function buildAjaxSettings()
    {
        $this->getGrid()->getAjax()->set(
            $this->getAjaxSettings()
        );

        return $this;
    }

    /**
     * @return $this
     */
    protected function buildGridOptions()
    {
        $this->getGrid()->getOptions()->set(
            $this->getGridOptions()
        );

        return $this;
    }

    /**
     * @param string $template
     *
     * @return $this
     */
    protected function setRowCallback($template)
    {
        $this->getGrid()->getCallbacks()->set(
            [
                'row_callback' => ['template' => $template]
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    protected function disableFilterOnKeyUp()
    {
        $this->getGrid()->getEvents()->set(
            [
                'pre_init' => ['template' => 'NatueStockBundle:PurchaseOrder/gridScripts:initializeDatatable.js.twig']
            ]
        );

        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this;
     */
    public function setGridOptions(array $options)
    {
        $this->gridOptions = $options;

        return $this;
    }

    /**
     * @param array $settings
     *
     * @return $this;
     */
    public function setAjaxSettings(array $settings)
    {
        $this->ajaxSettings = $settings;

        return $this;
    }

    /**
     * @param string $title = 'Id'
     * @param array $options = []
     *
     * @return $this
     */
    public function addIdColumn($title = 'ID', $options = [])
    {
        if (!$options) {
            $options = [
                'orderable'      => true,
                'filter'  => ['text', ['class' => 'small_width_td']]
            ];
        }

        return $this->addColumn('id', $title, $options);
    }

    /**
     * @param string $title = 'Status'
     * @param array $options
     *
     * @return $this
     */
    public function addStatusColumn($title = 'Status', $options = [])
    {
        $statuses = [
            'Received' => 'Received',
            'Pending'  => 'Pending'
        ];

        if (!$options) {
            $options = [
                'filter' => [
                    'select', [
                        'search_type'    => 'eq',
                        'select_options' => array_merge(['' => 'All'], $statuses)
                    ]
                ]
            ];
        }

        $options = array_merge($options, [
            'joinable_expr' => 'purchaseOrderItems.id',
            'aggregate_expr' => '
            case
                when count(purchaseOrderItems.id) = sum
                (case
                    when purchaseOrderItems.status in (\'receiving\', \'incoming\')
                    then 1
                    else 0
                end)
                then \'Received\'
                else \'Pending\'
            end',
            'searchable'     => true,
            'orderable'      => false
        ]);

        return $this->addColumn('Status', $title, $options, new AggregateColumn());
    }

    /**
     * @param string $title = 'Invoice Key'
     * @param array $options = []
     *
     * @return $this
     */
    public function addInvoiceKeyColumn($title = 'Invoice Key', $options = [])
    {
        return $this->addSimpleColumn('invoiceKey', $title, $options);
    }

    /**
     * @param string $title = 'Created At'
     * @param array $options
     *
     * @return $this
     */
    public function addCreatedAtColumn($title = 'Created At', $options = [])
    {
        if (!$options) {
            $options = [
                'date_format' => 'DD[/]MM[/]YYYY',
                'filter'      => ['daterange', ['class' => 'medium_width_td']]
            ];
        }

        return $this->addColumn('createdAt', $title, $options, 'datetime');
    }

    /**
     * @param string $title = 'Delivered At'
     * @param array $options
     *
     * @return $this
     */
    public function addDeliveredAtColumn($title = 'Delivered At', $options = [])
    {
        if (!$options) {
            $options = [
                'date_format' => 'DD[/]MM[/]YYYY',
                'filter'      => ['daterange', ['class' => 'medium_width_td']]
            ];
        }

        return $this->addColumn('dateActualDelivery', $title, $options, 'datetime');
    }

    /**
     * @param string $title = 'Supplier'
     * @param array $options = []
     *
     * @return $this
     */
    public function addSupplierColumn($title = 'Supplier', $options = [])
    {
        return $this->addSimpleColumn('zedSupplier.name', $title, $options);
    }

    /**
     * @param string $title = 'Actions'
     * @param array $options
     *
     * @return $this
     */
    public function addActionsColumn($title = 'Actions', $options = [])
    {
        if (!$options) {
            $options = [
                'title'   => $title,
                'class'   => 'large_width_td',
                'actions' => [
                    [
                        'route'             => 'stock_purchase_order_update',
                        'icon'              => 'glyphicon glyphicon-pencil',
                        'route_parameters'  => [
                            'id'          => 'id'
                        ],
                        'attributes'        => [
                            'class'       => 'btn btn-default btn-xs margin-action-th'
                        ],
                    ],
                    [
                        'route'             => 'stock_purchase_order_item_list',
                        'icon'              => 'glyphicon glyphicon-list',
                        'route_parameters'  => [
                            'id'          => 'id'
                        ],
                        'attributes'        => [
                            'class'         => 'btn btn-primary btn-xs margin-action-th',
                        ]
                    ],
                    [
                        'route'             => 'stock_purchase_order_delete',
                        'icon'              => 'glyphicon glyphicon-trash',
                        'route_parameters'  => [
                            'id'          => 'id'
                        ],
                        'attributes'        => [
                            'data-toggle' => 'modal',
                            'data-target' => "#myDeleteModal",
                            'class'       => 'btn btn-danger btn-xs delete-purchase-order margin-action-th',
                        ]
                    ]
                ]
            ];
        }

        $this->getGridColumnBuilder()->add(null, 'action', $options);

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return PurchaseOrderGrid
     */
    public function build()
    {
        if (!$this->getAjaxSettings()) {
            throw new \Exception('Missing Ajax Settings');
        }

        return $this
            ->setRowCallback('NatueStockBundle:PurchaseOrder/gridScripts:row.js.twig')
            ->disableFilterOnKeyUp()
            ->buildGridOptions()
            ->buildAjaxSettings()
            ->getGrid();
    }
}