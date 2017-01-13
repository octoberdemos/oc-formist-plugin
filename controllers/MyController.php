<?php namespace Acme\Formist\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;

class MyController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Acme.Formist', 'formist', 'mycontroller');
    }

    public function index()
    {
        $config = $this->makeConfig('$/acme/formist/models/customer/columns.yaml');

        $config->model = new \Acme\Formist\Models\Customer;

        $config->recordUrl = 'acme/formist/mycontroller/update/:id';

        $widget = $this->makeWidget('Backend\Widgets\Lists', $config);

        $this->vars['widget'] = $widget;
    }

    public function update($id = null)
    {
        $config = $this->makeConfig('$/acme/formist/models/customer/fields.yaml');

        $config->model = \Acme\Formist\Models\Customer::find($id);

        $widget = $this->makeWidget('Backend\Widgets\Form', $config);

        $this->vars['widget'] = $widget;
    }

    public function onUpdate($id = null)
    {
        $data = post();

        // Check storage/logs/system.log
        trace_log($data);

        \Flash::success('Jobs done!');
    }

    public function helloworld()
    {
        // ...
    }
}
