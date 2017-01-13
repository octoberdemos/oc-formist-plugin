# Part 1: How MVC works in October CMS

Behaviors are a fantastic tool for making things easy in the back-end. They introduce configurable and reusable patterns to base classes, such as models and controllers.

There are several behaviors that ship with October:

- Form Controller for creating, viewing, updating, deleting records
- List Controller for displaying a list of many records
- Relation Controller for handling relationships between records

Example controller that implements behaviors:

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

These are all great for simple management of your data. Although sometimes you will encounter more complex situations where the demands are too much for behaviors.

Here we'll show you how to get back to basics and follow the principals of MVC to get similar results. We will implement the controller behavior's logic by hand, starting with a basic [back-end controller](/docs/backend/controllers-ajax).

    <?php namespace Acme\Formist\Controllers;

    use Backend\Classes\Controller;

    class MyController extends Controller
    {
        // Methods are defined in here
    }

In October the back-end routing follows the pattern of the controller, so the above class `Acme\Formist\Controllers\MyController` will have a URL route prefix of **/backend/acme/formist/mycontroller**.

The default entry point for a controller is defined by the `index` method, called an "action". This method can return content directly:

    public function index()
    {
        // Return a simple string
        return 'Home';
    }

Or it might like to return a redirect to another page. This example redirects to the same controller with the `helloworld` action instead.

    public function index()
    {
        // Return a redirect
        return \Backend::redirect('acme/formist/mycontroller/helloworld');
    }

Let's define the `helloworld` action method.

    public function helloworld()
    {
        // ...
    }

When an action doesn't return anything, October will look for a view file with the same name as the action. In this case the file **plugins/acme/formist/controllers/mycontroller/helloworld.htm** is used for the content.

    <h1>Hello world!</h1>

If an action takes parameters, these will be accepted as part of the URL, following the action name. This action uses the URL structure of **/backend/acme/formist/mycontroller/update/6** passing "6" as the `$id` value.

    public function update($id = null)
    {
        return 'You gave an ID of: ' . $id;
    }

Variables can be passed to the view file using the `$this->vars` property. So let's pass the ID as a variable called `myId` to the view.

    public function update($id = null)
    {
        $this->vars['myId'] = $id;
    }

Now we can access it in the matching **plugins/acme/formist/controllers/mycontroller/update.htm** file as the `$myId` variable.

    <p>You gave an ID of: <?= $myId ?></p>

You can specify as many parameters as you like, they will be added to the URL accordingly. Here we add a context parameter to make the URL **/backend/acme/formist/mycontroller/update/6/foobar** passing "6" to `$id` and "foobar" to `$context`.

    public function update($id = null, $context = null)
    {
    }

Controllers can also specify [AJAX handlers](/docs/ajax/introduction), making dynamic page updates a breeze. These methods should always be specified as **onSomething**, here we will specify a handler called `onUpdate`.

    public function onUpdate()
    {
        // ...
    }

To make an AJAX handler available only to a specific action, you may prefix it with the action name. This `onUpdate` AJAX handler can only be called from the `update` action.

    public function update_onUpdate()
    {
        // ...
    }

In either case, the AJAX method will look at the URL for parameters, just like an action would. Let's write something to the trace log file using the `trace_log()` method and show a nice [flash message](/docs/markup/tag-flash).

    public function onUpdate($id = null)
    {
        // Check storage/logs/system.log
        trace_log('onUpdate was called with ID: '. $id);

        \Flash::success('Jobs done!');
    }

Calling an AJAX handler is simple, let's do it from the **update.htm** view file.

    <p>You gave an ID of: <?= $myId ?></p>

    <button data-request="onUpdate">Write to log</button>

At the end of the **storage/logs/system.log** file we should see our trace log entry.

    [2017-01-14 20:12:56] dev.INFO:  onUpdate was called with ID: 6
