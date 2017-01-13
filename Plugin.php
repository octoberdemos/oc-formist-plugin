<?php namespace Acme\Formist;

use Backend;
use System\Classes\PluginBase;

/**
 * Formist Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Formist',
            'description' => 'Implementing Forms & Lists without behaviors',
            'author'      => 'Acme',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'formist' => [
                'label'       => 'Formist',
                'url'         => Backend::url('acme/formist/customers'),
                'icon'        => 'icon-leaf',
                'order'       => 500,
                'sideMenu' => [
                    'customers' => [
                        'label' => 'Customers',
                        'url' => Backend::url('acme/formist/customers'),
                        'icon' => 'icon-cubes'
                    ],
                    'mycontroller' => [
                        'label' => 'My Controller',
                        'url' => Backend::url('acme/formist/mycontroller'),
                        'icon' => 'icon-diamond'
                    ]
                ]
            ],
        ];
    }
}
