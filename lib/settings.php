<?php 

require_once(WDH_LIB_DIR . '/control.php');

class WDH_Settings_Manager {
	var $theme_slug;
	var $theme_options_name;
	
	/**
	 * Tabs for settings page
	 * @var array
	 */
	var $tabs = array();
	
	/**
	 * Sections for settings page or tab
	 * @var array
	 */
	var $sections = array();
	
	/**
	 * Options
	 * @var array
	 */
	var $options = array();
	
	/**
	 * Controls on settings page
	 * @var array
	 */
	var $controls = array();
	
	function __construct($theme_slug, $theme_options_name){
		$this->theme_slug = $theme_slug;
		$this->theme_options_name = $theme_options_name;
	}
	
	/**
	 * Add a settings tab
	 * @param string $id A specific ID of the tab
	 * @param array $args Tab arguments
	 */
	function add_tab($id, $args = array()){
		$this->tabs[$id] = new WDH_Settings_Tab($this, $id, $args);
	}
	
	/**
	 * Retrieve a tab.
	 * @param string $id A specific ID of the tab
	 * @return WDH_Settings_Tab The tab object
	 */
	function get_tab($id){
		if(isset($this->tabs[$id])){
			return $this->tabs[$id];
		}
	}
	
	/**
	 * Remove a settings tab
	 * @param string $id A specific ID of the tab
	 */
	function remove_tab($id){
		unset($this->tabs[$id]);
	}
	
	/**
	 * Add a settings section
	 * @param string $id A specific ID of the section
	 * @param array $args Tab arguments
	 */
	function add_section($id, $args = array()){
		$this->sections[$id] = new WDH_Settings_Section($this, $id, $args);
	}
	
	/**
	 * Retrieve a settings section.
	 * @param string $id A specific ID of the section
	 * @return WDH_Settings_Section The section object
	 */
	function get_section($id){
		if(isset($this->sections[$id])){
			return $this->sections[$id];
		}
	}
	
	/**
	 * Remove a settings section
	 * @param string $id A specific ID of the section
	 */
	function remove_section($id){
		unset($this->sections[$id]);
	}
	
	/**
	 * Add a settings control
	 * @param string $id A specific ID of the control
	 * @param array $args Tab arguments
	 */
	function add_control($id, $args = array()){
		if(is_a( $id, 'WDH_Control' )){
			$this->controls[$id->id] = $id;
		} else {
			$this->controls[$id] = new WDH_Control($this, $id, $args);
		}
	}
	
	/**
	 * Retrieve a settings control.
	 * @param string $id A specific ID of the control
	 * @return WDH_Control The control object
	 */
	function get_control($id){
		if(isset($this->controls[$id])){
			return $this->controls[$id];
		}
	}
	
	/**
	 * Remove a settings control
	 * @param string $id A specific ID of the control
	 */
	function remove_control($id){
		unset($this->controls[$id]);
	}
	
	/**
	 * Helper function to compare two objects by priority. Stole from WP_Customize_Manager
	 * @param unknown_type $a
	 * @param unknown_type $b
	 */
	protected final function _cmp_priority($a, $b){
		$ap = $a->priority;
		$bp = $b->priority;
		
		if($ap == $bp){
			return 0;
		} else {
			return ($ap > $bp) ? 1 : -1;
		}
	}
	
	public function prepare(){
		// Prepare controls
		$this->controls = array_reverse($this->controls);
		$controls = array();
		
		foreach($this->controls as $id => $control){
			if(!isset($this->sections[$control->section]) || !$control->check_capabilities()){
				continue;
			}
			
			$this->sections[$control->section]->controls[] = $control;
			$controls[$id] = $control;
		}
		$this->controls = $controls;
		
		// Prepare sections
		$this->sections = array_reverse($this->sections);
		uasort($this->sections, array($this, '_cmp_priority'));
		$sections = array();
		
		foreach($this->sections as $section){
			if(!$section->check_capabilities() || !$section->controls){
				continue;
			}
			
			if(!empty($this->tabs)){
				if(!isset($this->tabs[$section->tab])){
					continue;
				}
				
				$this->tabs[$section->tab]->sections[$section->id] = $section;
			}
			
			usort($section->controls, array($this, '_cmp_priority'));
			$sections[$section->id] = $section;
		}
		$this->sections = $sections;
		
		// Prepare tabs
		if(!empty($this->tabs)){
			$this->tabs = array_reverse($this->tabs);
			uasort($this->tabs, array($this, '_cmp_priority'));
			$tabs = array();
			
			foreach($this->tabs as $tab){
				if(!$tab->check_capabilities() || !$tab->sections){
					continue;
				}
				
				// Add option for each tab
				/*if(!get_option($this->theme_slug . '_' . $tab->id)){
					add_option($this->theme_slug . '_' . $tab->id, $tab->options);
				}*/
				
				usort($tab->sections, array($this, '_cmp_priority'));
				$tabs[] = $tab;
			}
			$this->tabs = $tabs;
		}
		
		/*
		if(!empty($this->tabs)){
			// Add option for each tab
			if(!get_option($this->theme_slug)){
				add_option($this->theme_slug, $this->options);
			}
		}*/
	}
}

class WDH_Settings_Tab {
	public $manager;
	public $id;
	public $title = '';
	public $priority = 10;
	public $capability = 'edit_theme_options';
	public $sections;
	public $options = array();
	
	/**
	 * Constructor
	 * @param WDH_Settings_Manager $manager
	 * @param string $id
	 * @param array $args
	 */
	function __construct($manager, $id, $args){
		$keys = array_keys(get_class_vars(__CLASS__));
		foreach($keys as $key){
			if(isset($args[$key])){
				$this->{$key} = $args[$key]; 
			}
		}
		
		$this->manager = $manager;
		$this->id = $id;
		
		$this->sections = array();	// Users cannot customize the $sections array.
		return $this;
	}
	
	/**
	 * Check if the user has access to this tab
	 * 
	 * @return bool False if user doesn't have access to this tab
	 */
	public final function check_capabilities(){
		if($this->capability && !call_user_func_array('current_user_can', (array) $this->capability)){
			return false;
		}
		
		return true;
	}
	
	/**
	 * Checks capabilities and renders the tab.
	 */
	public final function maybe_render(){
		if(!$this->check_capabilities()){
			return;
		}
		
		do_action('customize_render_wdh_tab', $this);
		do_action('customize_render_wdh_tab_' . $this->id);
		
		$this->render();
	}
	
	/**
	 * Render the tab sections.
	 */
	protected function render(){
		foreach($this->sections as $section){
			$section->maybe_render();
		}
	}
	
	public function sanitize_options($input){
		foreach($this->sections as $section){
			$section->sanitize_options($input);
		}
	}
}

class WDH_Settings_Section {
	public $manager;
	public $id;
	public $title = '';
	public $description = '';
	public $priority = 10;
	public $capability = 'edit_theme_options';
	public $controls;
	public $tab = '';
	
	function __construct($manager, $id, $args){
		$keys = array_keys(get_class_vars(__CLASS__));
		foreach($keys as $key){
			if(isset($args[$key])){
				$this->{$key} = $args[$key];
			}
		}
	
		$this->manager = $manager;
		$this->id = $id;
	
		$this->controls = array();	// Users cannot customize the $controls array.
		return $this;
	}
	
	/**
	 * Check if the user has access to this section
	 *
	 * @return bool False if user doesn't have access to this tab
	 */
	public final function check_capabilities(){
		if($this->capability && !call_user_func_array('current_user_can', (array) $this->capability)){
			return false;
		}
	
		return true;
	}
	
	/**
	 * Checks capabilities and renders the section.
	 */
	public final function maybe_render(){
		if(!$this->check_capabilities()){
			return;
		}
	
		do_action('customize_render_wdh_section', $this);
		do_action('customize_render_wdh_section_' . $this->id);
	
		$this->render();
	}
	
	/**
	 * Render the section controls.
	 */
	protected function render(){
		if($this->description != ''){
	?>
		<em><?php echo $this->description; ?></em><br /><br />
	<?php 
		}
	}
	
	public function sanitize_options($input){
		foreach($this->controls as $control){
			$control->sanitize_input($input);
		}
	}
}
?>