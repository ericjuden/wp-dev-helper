<?php 
/**
 * WordPress options helper class
 * @author ericjuden <eric@ericjuden.com>
 * @package WDH;
 * @subpackage Utility;
 */

/**
 * WordPress Options Manager class
 * @author ericjuden <ericjuden@gmail.com>
 *
 */
class WDH_Options_Manager {
	/**
	 * Settings Manager
	 * @var WDH_Settings_Manager
	 */
	public $settings_manager;
	
	/**
	 * option_name in options db table
	 * @var string
	 */
	public $option_name;
	
	/**
	 * Options
	 * @var array
	 */
	public $options = array();
	
	function __construct($option_name){
		$this->option_name = $option_name;
	}
	
	/**
	 * Get an option value
	 * @param string $key
	 * @return mixed string|array
	 */
	function __get($key){
		return $this->options[$key];
	}
	
	/**
	 * Set an option value
	 * @param string $key
	 * @param mixed string|array $value
	 * @return void
	 */
	function __set($key, $value){
		$this->options[$key] = $value;
	}
	
	/**
	 * Save all options to the database
	 * @return void
	 */
	function save(){
		foreach($this->options as $key => $options){
			$options->save();
		}
	}
}

/**
 * WordPress options helper class
 * @author ericjuden <eric@ericjuden.com>
 *
 */
class WDH_Options {
	/**
	 * Variable to hold options array
	 * @var array
	 */
	public $options;
	
	/**
	 * option_name in options db table
	 * @var string
	 */
	public $option_name;
	
	/**
	 * Is this a site option (used for Multisite)
	 * @var bool
	 */
	public $is_site_option;    // Are we using Multisite and saving to global options?

	/**
	 * Constructor
	 * @param string $option_name
	 * @param bool $is_site_options = false
	 */
	function __construct($option_name, $is_site_options = false){
	    $this->option_name = $option_name;
		$this->is_site_option = $is_site_options;
		if($this->is_site_option){
			$this->options = get_site_option($this->option_name, false);
		} else {
			$this->options = get_option($this->option_name, false);
		}
		
		// Check if options are JSON
		$temp_options = json_decode($this->options);
		if(json_last_error() == JSON_ERROR_NONE && !empty($temp_options)){
			$this->options = $temp_options;
		}

		if(!is_array($this->options)){
			$this->options = array();
			if($this->is_site_option){
			    add_site_option($this->option_name, json_encode($this->options));
			} else {
			    add_option($this->option_name, json_encode($this->options));
			}
		}
	}

	/**
	 * Get an option value
	 * @param string $key
	 * @return mixed string|array
	 */
	function __get($key){
		return $this->options[$key];
	}

	/**
	 * Set an option value
	 * @param string $key
	 * @param mixed string|array $value
	 * @return void
	 */
	function __set($key, $value){
		$this->options[$key] = $value;
	}

	/**
	 * Check if the option exists
	 * @param string $key
	 * @return boolean
	 */
	function __isset($key){
		return array_key_exists($key, $this->options);
	}
	
	/**
	 * Get options as an array
	 * @return array 
	 */
	function get_multi(){
		return $this->options;
	}

	/**
	 * Save all options to the database
	 * @return void
	 */
	function save(){
		if($this->is_site_option){
			update_site_option($this->option_name, json_encode($this->options));
		} else {
			update_option($this->option_name, json_encode($this->options));
		}
	}
}
?>