# Part 2: Rendering Lists and Forms by hand

Now that we understand the MVC pattern used in October -- see Part 1 if not already up to speed -- let's work on rendering forms and lists.

We will loosely follow the same patterns introduced by the `FormController` and `ListController` behaviors. When implemented in a controller, these behaviors introduce page actions and AJAX handlers.

- ListController adds action: `index`.
- FormController adds actions: `create`, `update`, `preview`.

Each behavior creates a back-end widget for the functionality. This is what we will use for our form and list.

- ListController creates a `Backend\Widgets\Lists` widget
- FormController creates a `Backend\Widgets\Form` widget

Inside the `update` action, let's prepare the form widget. First we must tell the form widget what fields to render using the `$this->makeConfig` method and passing the path to our form field definition file. In this path the `$` symbol represents the base path to the plugins directory.

    public function update($id = null)
    {
        $config = $this->makeConfig('$/acme/formist/models/customer/fields.yaml');
    }

We should also tell the form widget about the associated model, passed to the newly generated config as the `model` property.

    public function update($id = null)
    {
        $config = $this->makeConfig('$/acme/formist/models/customer/fields.yaml');

        $config->model = new \Acme\Formist\Models\Customer;
    }

Finally, the widget can be created using the `$this->makeWidget` method, passing the widget class name as the first argument and config object as the second argument. We should also make the widget available to the view file by passing it to the `$this->vars` array property.

    public function update($id = null)
    {
        $config = $this->makeConfig('$/acme/formist/models/customer/fields.yaml');

        $config->model = new \Acme\Formist\Models\Customer;

        $widget = $this->makeWidget('Backend\Widgets\Form', $config);

        $this->vars['widget'] = $widget;
    }

To render the form, open the **update.htm** view file and simply call the `render()` method on the widget. The widget should also be wrapped in a HTML form so the postback data can be captured.

    <?= Form::open() ?>
        <?= $widget->render() ?>

        <button data-request="onUpdate" class="btn btn-primary">
            Write to log
        </button>
    <?= Form::close() ?>

Let's update the AJAX handler to capture the form postback data.

    public function onUpdate($id = null)
    {
        $data = post();

        // Check storage/logs/system.log
        trace_log($data);

        \Flash::success('Jobs done!');
    }

At the end of the **storage/logs/system.log** file we should see our trace log entry.

    [2017-01-14 20:29:04] dev.INFO: Array
    (
        [_session_key] => l2rGYWWZhhUiAGb49sIy9A96FHD4ZGTOn1pI1uFE
        [_token] => hNpmptZIgNKskWN0sjPVGGKeXktMYsdj0Y1KD8rx
        [company] => 
        [first_name] => 
        [last_name] => 
        [street] => 
        [house_number] => 
        [zipcode] => 
        [city] => 
        [email] => 
        [phone] => 
    )

*Wait a second, our action is called "update" and the fields are empty, shouldn't we be updating something?*

To tell the form about existing data, change the `model` in the configuration to an existing record instead. We can use the `$id` value taken from the URL and pass it to the `Customer::find` method, that's handy!

    public function update($id = null)
    {
        //...

        $config->model = Acme\Formist\Models\Customer::find($id);

        // ...
    }

Now open the URL **/backend/acme/formist/mycontroller/update/1** to find the Customer with the ID of 1. We just built a simplified version of the `FormController` behavior's `update` action!

Creating a list for the `index` action is just as easy. We pass the list column file to the `makeConfig` method, specify a new `model` instance and make the `Backend\Widgets\Lists` widget class instead.

    public function index()
    {
        $config = $this->makeConfig('$/acme/formist/models/customer/columns.yaml');

        $config->model = new \Acme\Formist\Models\Customer;

        $widget = $this->makeWidget('Backend\Widgets\Lists', $config);

        $this->vars['widget'] = $widget;
    }

Inside the corresponding view file **plugins/acme/formist/controllers/mycontroller/index.htm** we can call the `render` method.

    <?= $widget->render() ?>

Now open the URL **/backend/acme/formist/mycontroller** to see the rendered list. Notice if we try to sort the list, by clicking a column header, an error message is displayed:

    A widget with class name 'list' has not been bound to the controller

This is because widgets themselves can provide AJAX handlers for their functionality, however the controller should be informed of this. We register the widget to the controller using the `bindToController` method.

    public function index()
    {
        // ...

        $widget = $this->makeWidget('Backend\Widgets\Lists', $config);

        $widget->bindToController();

        $this->vars['widget'] = $widget;
    }

It is important to note that this binding must occur early in the page life cycle. This means `bindToController` must be called either in a page action method, inside the `__construct()` method of a controller, or inside the `init()` method of a widget.

Now let's link our list to our form by specifying a `recordUrl` in the widget configuration. Now when a record is clicked, it will take the user to the `update` action we created earlier.

    public function index()
    {
        $config = $this->makeConfig('$/acme/formist/models/customer/columns.yaml');

        $config->model = new \Acme\Formist\Models\Customer;

        $config->recordUrl = 'acme/formist/mycontroller/update/:id';

        $widget = $this->makeWidget('Backend\Widgets\Lists', $config);

        $widget->bindToController();

        $this->vars['widget'] = $widget;
    }

Now we have a simplified version of the `ListController` behavior's `index` action!

Here we have demonstrated that behaviors are useful companions to building in the back-end, they can save a lot of time. However, behaviors are not a necessity to build powerful back-end interfaces. Like opening the hood of a car, there are many components you can borrow to build your own custom hot rod!
