<?php

namespace Natue\Bundle\StockBundle\Grid\Datatable;

use Natue\Bundle\CoreBundle\Datatable\Column\AggregateColumn;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\StockBundle\Grid;
use Sg\DatatablesBundle\Datatable\Column\ColumnBuilderInterface;
use Sg\DatatablesBundle\Datatable\View;

/**
 * Class ItemGridBuilder
 * @package Natue\Bundle\StockBundle\Grid\Datatable
 */
class ItemGridBuilder
{
    /**
     * @var ItemGrid
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
     * ItemGridBuilder constructor.
     * @param ItemGrid $grid
     */
    public function __construct(ItemGrid $grid)
    {
        $this->grid = $grid;
        $this->gridOptions = [
            'length_menu'                   => [50, 100, -1],
            'class'                         => View\Style::BOOTSTRAP_3_STYLE,
            'individual_filtering'          => true,
            'individual_filtering_position' => 'head',
            'use_integration_options'       => true,
            'search_delay'                  => 1500,
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
        return ['text', [
            'search_type' => 'like',
            'cancel_button' => true
        ]];
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
                'pre_init' => ['template' => 'NatueStockBundle:Item/gridScripts:initializeDatatable.js.twig']
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
     * @param string $title = 'SKU'
     * @param array $options = []
     *
     * @return $this
     */
    public function addSkuColumn($title = 'SKU', $options = [])
    {
        return $this->addSimpleColumn('zedProduct.sku', $title, $options);
    }

    /**
     * @param string $title = 'SKU'
     * @param array $options = []
     *
     * @return $this
     */
    public function addQtdColumn($title = 'QTD', $options = [])
    {
        $options = array_merge($options, [
            'aggregate_expr' => 'count(zedProduct.id)',
            'searchable'     => false,
            'orderable'      => false
        ]);

        return $this->addColumn('qtd', $title, $options, new AggregateColumn());
    }

    /**
     * @param string $title = 'Name'
     * @param array $options = []
     *
     * @return $this
     */
    public function addNameColumn($title = 'Name', $options = [])
    {
        return $this->addSimpleColumn('zedProduct.name', $title, $options);
    }

    /**
     * @param string $title = 'Barcode'
     * @param array $options
     *
     * @return $this
     */
    public function addBarcodeColumn($title = 'Barcode', $options = [])
    {
        return $this->addSimpleColumn('barcode', $title, $options);
    }

    /**
     * @param string $title = 'Expiration'
     * @param array $options
     *
     * @return $this
     */
    public function addExpirationColumn($title = 'Expiration', $options = [])
    {
        if (!$options) {
            $options = [
                'date_format' => 'DD[/]MM[/]YYYY',
                'filter'      => ['daterange', []]
            ];
        }

        return $this->addColumn('dateExpiration', $title, $options, 'datetime');
    }

    /**
     * @param string $title = 'Status'
     * @param array $options
     *
     * @return $this
     */
    public function addStatusColumn($title = 'Status', $options = [])
    {
        $statuses = EnumStockItemStatusType::$values;
        $statuses = array_combine(
            $statuses,
            array_map('ucwords', $statuses)
        );

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

        return $this->addSimpleColumn('status', $title, $options);
    }

    /**
     * @param string $title = 'Positions'
     * @param array $options
     *
     * @return $this
     */
    public function addPositionColumn($title = 'Position', $options = [])
    {
        return $this->addSimpleColumn('stockPosition.name', $title, $options);
    }

    /**
     * @param string $title = 'Pickable'
     * @param array $options
     *
     * @return $this
     */
    public function addPickableColumn($title = 'Pickable', $options = [])
    {
        if (!$options) {
            $options = [
                'true_label'  => 'Yes',
                'false_label' => 'No',
                'filter'      => [
                    'select', [
                        'search_type'    => 'eq',
                        'select_options' => ['' => 'All', '1' => 'Yes', '0' => 'No']
                    ]
                ]
            ];
        }

        return $this->addColumn('stockPosition.pickable', $title, $options, 'boolean');
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
                'actions' => [
                    [
                        'route'            => 'stock_item_update',
                        'route_parameters' => [
                            'sku'            => 'zedProduct.sku',
                            'positionId'     => 'stockPosition.id',
                            'status'         => 'status',
                            'barcode'        => 'barcode',
                            'dateExpiration' => "dateExpiration.timestamp"
                        ],
                        'icon'             => 'glyphicon glyphicon-pencil',
                        'attributes'       => [
                            'class'    => 'btn btn-default btn-xs',
                            'data-sku' => 'sku'
                        ],
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
     * @return ItemGrid
     */
    public function build()
    {
        if (!$this->getAjaxSettings()) {
            throw new \Exception('Missing Ajax Settings');
        }

        return $this
            ->setRowCallback('NatueStockBundle:Item/gridScripts:row.js.twig')
            ->disableFilterOnKeyUp()
            ->buildGridOptions()
            ->buildAjaxSettings()
            ->getGrid();
    }
}