<?php namespace Acme\Formist\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Customers Back-end Controller
 */
class Customers extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    protected $itemFormWidget;

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Acme.Formist', 'formist', 'customers');

        $this->createOrderItemFormWidget();
    }

    public function onCreateItemForm()
    {
        $this->vars['orderId'] = post('manage_id');

        return $this->makePartial('item_create_form');
    }

    public function onCreateItem()
    {
        $data = $this->itemFormWidget->getSaveData();

        $model = new \Acme\Formist\Models\Item;

        $model->fill($data);

        $model->save();

        $order = $this->getOrderModel();

        $order->items()->add($model, $this->itemFormWidget->getSessionKey());

        return $this->refreshOrderItemList();
    }

    public function onDeleteItem()
    {
        $recordId = post('record_id');

        $model = \Acme\Formist\Models\Item::find($recordId);

        $order = $this->getOrderModel();

        $order->items()->remove($model, $this->itemFormWidget->getSessionKey());

        return $this->refreshOrderItemList();
    }

    protected function getOrderModel()
    {
        $manageId = post('manage_id');

        $order = $manageId
            ? \Acme\Formist\Models\Order::find($manageId)
            : new \Acme\Formist\Models\Order;

        return $order;
    }

    protected function refreshOrderItemList()
    {
        $items = $this->getOrderModel()
            ->items()
            ->withDeferred($this->itemFormWidget->getSessionKey())
            ->get()
        ;

        $this->vars['items'] = $items;

        return ['#itemList' => $this->makePartial('item_list')];
    }

    protected function createOrderItemFormWidget()
    {
        $config = $this->makeConfig('$/acme/formist/models/item/fields.yaml');

        $config->alias = 'itemForm';

        $config->arrayName = 'Item';

        $config->model = new \Acme\Formist\Models\Item;

        $widget = $this->makeWidget('Backend\Widgets\Form', $config);

        $widget->bindToController();

        $this->itemFormWidget = $this->vars['itemFormWidget'] = $widget;

        return $widget;
    }

}
