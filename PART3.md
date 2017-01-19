# Part 3: Example implementation

Let's wrap this up and put a bow on it. There are lots of possible implementations, such as [rolling your own behavior](/docs/services/behaviors) or building completely custom experiences. In this example we'll take what we've learned and apply it to a realistic example; let's build a nested relation manager.

Download the [Formist plugin from GitHub](https://github.com/daftspunk/oc-formist-plugin) as we'll be using it as a reference. This plugin contains several model objects:

- Base Customer object (`Acme\Formist\Models\Customer`)
- Orders belonging to Customers (`Acme\Formist\Models\Order`)
- Order Items belonging to Orders (`Acme\Formist\Models\Item`)
- Products belonging to Order Items (`Acme\Formist\Models\Product`)

The form for updating a Customer can have the Orders managed using the `RelationController` behavior. The Order Items cannot be handled by the behavior because it is 2 levels deep and this is not supported. We need to implement a custom solution using our MVC skills.

Here is the controller in the current form, which implements all of the common behaviors to get the base line functionality.

    <?php namespace Acme\Formist\Controllers;

    use Backend\Classes\Controller;

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
    }

We want to add Order Items to an Order, so let's open the form field definition file at **/plugins/acme/formist/models/order/fields.yaml** and include a `partial` form field to render our field items.

    # ===================================
    #  Form Field Definitions
    # ===================================

    fields:

        #...

        items:
            label: Items
            type: partial
            path: field_items

The partial path defined as `field_items` means it will look for content inside the controller view file **/plugins/acme/formist/controllers/customers/\_field\_items.htm** so we should create that file.

    <div id="itemList">
        <?= $this->makePartial('item_list', ['items' => $model->items]) ?>
    </div>

    <p>
        <a
            href="javascript:;"
            class="btn btn-secondary oc-icon-plus"
            data-control="popup"
            data-handler="onLoadCreateItemForm"
            data-size="large">
            Add item
        </a>
    </p>

Inside this partial we display a list of items and an *Add item* button that launches a popup using the `onLoadCreateItemForm` AJAX handler. The list of items is rendered as another partial called `item_list` and is passed a variable of `items` taken from the parent `$model` object, representing the `Acme\Formist\Models\Order` model. Let's create the **\_item\_list.htm** partial along side the previous partial.

    <?php if (count($items)): ?>
        <div class="list-preview list-flush">
            <div class="control-list">
                <table class="table data" data-control="rowlink">
                    <thead>
                        <tr>
                            <th><span>Item</span></th>
                            <th><span>Price</span></th>
                            <th style="width: 10%"><span></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?= e($item->qty) ?> x
                                    <?= e($item->product ? $item->product->name : 'Unknown') ?>
                                </td>
                                <td>
                                    <?= e($item->price) ?>
                                </td>
                                <td class="nolink text-right">
                                    <a
                                        href="javascript:;"
                                        data-request="onDeleteItem"
                                        data-request-data="record_id: '<?= $item->id ?>'"
                                        data-request-confirm="Delete this item?"
                                        class="oc-icon-remove"
                                        data-toggle="tooltip"
                                        title="Remove"></a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif ?>

In the above partial we display a simple list that displays each order item, with a "Remove" link that triggers the `onDeleteItem` AJAX handler. Alternatively we could have used a `Backend\Widgets\Lists` widget here. To keep things simple, we've used the List markup found in the [User interface guide](/docs/ui/list).

This should be enough for the "Items" form field to appear on the Order form. When clicking "Add item", an error is displayed:

    AJAX handler 'onLoadCreateItemForm' was not found.

We should return to our controller to create the `onLoadCreateItemForm` handler. It is a simple handler that passes the `orderId` variable to yet another partial called `item_create_form` inside a popup. We'll be using a `Backend\Widgets\Form` widget to render the body contents of the popup, this can be stored in the `$itemFormWidget` property.

    class Customers extends Controller
    {
        // ...

        protected $itemFormWidget;

        public function onLoadCreateItemForm()
        {
            $this->vars['itemFormWidget'] = $this->itemFormWidget;

            $this->vars['orderId'] = post('manage_id');

            return $this->makePartial('item_create_form');
        }
    }

Now to create the **\_item\_create\_form.htm** partial along side the others. This will be used for the inner content of the popup, when clicking the "Add item" button.

    <?= Form::open() ?>

        <input type="hidden" name="manage_id" value="<?= $orderId ?>" />

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="popup">&times;</button>
            <h4 class="modal-title">Create new order item</h4>
        </div>

        <div class="modal-body">
            <?= $itemFormWidget->render() ?>
        </div>

        <div class="modal-footer">
            <button
                type="submit"
                data-request="onCreateItem"
                data-request-data="redirect:0"
                data-hotkey="ctrl+s, cmd+s"
                data-popup-load-indicator
                class="btn btn-primary">
                Create
            </button>

            <button
                type="button"
                class="btn btn-default"
                data-dismiss="popup">
                Cancel
            </button>
        </div>

    <?= Form::close() ?>

Inside this partial, the Order identifier is passed along with every request as `manage_id` via a hidden INPUT element, so we know what Order to attach the item to. The `render()` method is called on the `$itemFormWidget` variable to display the form and the `onCreateItem` AJAX handler is used to save the form.

The `$itemFormWidget` should be set to an instance of the `Backend\Widgets\Form` widget. We do this via the controller's constructor defined in a `createOrderItemFormWidget` helper method. This ensures the widget is always available for us to use.

The widget uses some configuration that might be unfamiliar, called `alias` and `arrayName`. These are used to prevent collisions with other form widgets used on the page.

    class Customers extends Controller
    {
        // ...

        protected $itemFormWidget;

        public function __construct()
        {
            parent::__construct();

            $this->itemFormWidget = $this->createOrderItemFormWidget();
        }

        // ...

        protected function createOrderItemFormWidget()
        {
            $config = $this->makeConfig('$/acme/formist/models/item/fields.yaml');

            $config->alias = 'itemForm';

            $config->arrayName = 'Item';

            $config->model = new \Acme\Formist\Models\Item;

            $widget = $this->makeWidget('Backend\Widgets\Form', $config);

            $widget->bindToController();

            return $widget;
        }
    }

Now clicking the "Add item" button will display a popup with our form displayed inside! If we click the "Create" button an error is displayed.

    AJAX handler 'onCreateItem' was not found.

This `onCreateItem` AJAX handler is where it all comes together. The data is captured from the form widget, a new Order Item is created then associated to the order. [Deferred binding is used](/docs/database/relations#deferred-binding) when associating, dissociating and displaying the item list.

Finally, the list of items is refreshed on the page dynamically using the `refreshOrderItemList` helper method. Notice the `getOrderModel` helper method, this uses the `manage_id` value we supplied in the **\_item\_create\_form.htm** partial.

    class Customers extends Controller
    {
        // ...

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

        protected function getOrderModel()
        {
            $manageId = post('manage_id');

            $order = $manageId
                ? \Acme\Formist\Models\Order::find($manageId)
                : new \Acme\Formist\Models\Order;

            return $order;
        }

        // ...
    }

Almost like magic Order Items can be added using the Order form fields! As the last step, we should allow removing of Order Items from the list.

Here is the isolated HTML markup used for the "Remove" button, contained in the **\_item\_list.htm** partial. Notice the item identifier is passed along with the request as `record_id`, this will be used to dissociate the item.

    <a
        href="javascript:;"
        data-request="onDeleteItem"
        data-request-data="record_id: '<?= $item->id ?>'"
        data-request-confirm="Delete this item?"
        class="oc-icon-remove"
        data-toggle="tooltip"
        title="Remove"></a>

The `onDeleteItem` handler is called to process the logic. It looks up the Order Item by the `record_id` value, removes it from the Order, then refreshes this list. This method borrows the same helpers as the `onCreateItem` and fundamentally operates in reverse.

    class Customers extends Controller
    {
        // ...

        public function onDeleteItem()
        {
            $recordId = post('record_id');

            $model = \Acme\Formist\Models\Item::find($recordId);

            $order = $this->getOrderModel();

            $order->items()->remove($model, $this->itemFormWidget->getSessionKey());

            return $this->refreshOrderItemList();
        }

        // ...
    }

Time to wrap up: Here we have combined the standard behaviors with our own custom MVC features added to the controller. The features demonstrate two possible approaches, using simple HTML to render the list, or using the back-end widgets to render the form. Both integrate seamlessly with the October tools and truly unleash the power of possibilities!

For an extra challenge, try converting these features to a custom behavior. This makes the features reusable and portable, so you can use them anywhere.
