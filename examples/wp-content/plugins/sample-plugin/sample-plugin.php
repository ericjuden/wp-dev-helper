<?php
/*
Plugin Name: Sample Plugin
Plugin URI: http://pluginwebsite.com
Description: Super Awesome Description
Author: Author Name
Version: 1.0
Author URI: http://authorwebsite.com
*/

// Setup a couple of constants for quicker access to folder path
define('SP_PLUGIN_DIR', WP_PLUGIN_DIR . '/sample-plugin');
define('SP_PLUGIN_URL', plugins_url($path = '/sample-plugin'));

// Require wp-dev-helper plugin class
require_once(WP_PLUGIN_DIR . '/wp-dev-helper/plugin.php');

// Require any classes of your own
require_once(SP_PLUGIN_DIR . '/lib/my-custom-post-type.php');
require_once(SP_PLUGIN_DIR . '/lib/my-taxonomy.php');

class My_Sample_Plugin extends WDH_Plugin {
    var $my_post_type;
    var $my_taxonomy;

    function __construct(){
        $this->my_post_type = new Sample_Custom_Post_Type();
        $this->my_taxonomy = new Sample_Taxonomy();

        // Inherit from WDH_Plugin class
        // Param 1 = plugin slug
        // Param 2 = plugin name
        // Param 3 = options key stored in wp_options table
        parent::__construct('sample-plugin', __('Sample Plugin'), 'sample-plugin-options');

        // Add WP actions and filters here
        add_action('init', array($this, 'action_init'));
    }

    function action_init(){
        // DO WP init stuff here
    }

    function define_plugin_settings()
    {
        // Define sections for settings page
        $this->options->general = new WDH_Options($this->options->option_name . '_general');

        // Set default value for options
        if(!isset($this->options->general->my_setting1)){
            $this->options->general->my_setting1 = 'blue';
        }

        if(!isset($this->options->general->my_setting2)){
            $this->options->general->my_setting2 = 'Hello World!';
        }

        // Save defaults
        $this->options->save();

        $this->settings_manager->add_tab('general', array(
           'title' => __('General'),
            'priority' => 10
        ));

        $this->settings_manager->add_section('sample-plugin', array(
           'title' => __('Sample Plugin Options'),
            'priority' => 10,
            'tab' => 'general'
        ));

        $this->settings_manager->add_control('my_setting1', array(
            'title' => __('Your favorite color'),
            'type' => 'select',
            'priority' => 10,
            'section' => 'sample-plugin',
            'choices' => array('red' => __('Red'), 'green' => __('Green'), 'blue' => __('Blue')),
            'value' => $this->options->general->my_setting1
        ));

        $this->settings_manager->add_control('my_setting2', array(
            'title' => __('Test Textbox'),
            'type' => 'textbox',
            'priority' => 20,
            'section' => 'sample-plugin',
            'value' => $this->options->general->my_setting2
        ));
    }
}
$my_sample_plugin = new My_Sample_Plugin();
?>