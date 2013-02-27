<?php 
/**
 * Base theme
 * @package WDH
 * @subpackage Theme
 *
 */

require_once(dirname(__FILE__) . '/constants.php');
require_once(WDH_LIB_DIR . '/settings.php');
require_once(WDH_LIB_DIR . '/options.php');

/**
 * Base theme to inherit from
 * 
 * @author ericjuden <eric@ericjuden.com>
 */
class WDH_Theme {
	var $theme_slug = 'wdh';	// Set a default
	var $options;
	var $network_theme_options;
	var $settings_manager;
	
	/**
	 * Constructor
	 */
	function __construct($theme_slug, $theme_options_name = ''){
		$this->theme_slug = $theme_slug;
		
		$this->settings_manager = new WDH_Settings_Manager($this->theme_slug, $theme_options_name);
		if($theme_options_name != ''){
			$this->options = new WDH_Options_Manager($theme_options_name, $this->settings_manager);
		}
		
		$this->define_theme_settings();
		
		add_action('admin_init', array($this, 'admin_init'));
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('after_setup_theme', array($this, 'after_setup_theme'));
	}
	
	function add_settings_data(){	
		$this->settings_manager->prepare();
		
		if(!empty($this->settings_manager->tabs)){
			foreach($this->settings_manager->tabs as $tab){
				// Add Settings Sections
				foreach($tab->sections as $section){
					add_settings_section($section->id, $section->title, array($section, 'maybe_render'), $this->options->option_name . '_' . $tab->id);
					
					// Add controls
					foreach($section->controls as $control){
						add_settings_field($control->id, $control->title, array($control, 'render_content'), $this->options->option_name . '_' . $tab->id, $section->id, array());
					}
				}
				
				// Register setting for the current tab
				register_setting($this->options->option_name . '_' . $tab->id, $this->options->option_name . '_' . $tab->id);//, array($tab, 'sanitize_options'));
			}
		} else {
			foreach($this->settings_manager->sections as $section){
				add_settings_section($section->id, $section->title, array($section, 'maybe_render'), $this->options->option_name);
				
				// Add controls
				foreach($section->controls as $control){
					add_settings_field($control->id, $control->title, array($control, 'render_content'), $this->options->option_name, $section->id, array());
				}
			}
			
			// Register setting for page
			register_setting($this->options->option_name, $this->options->option_name);
		}
	}
	
	function admin_init(){		
		$this->add_settings_data();
	}
	
	function admin_menu(){
		// Do we have any theme options for a page to be created?
		if(!empty($this->settings_manager->sections)){
			add_theme_page(__('Theme Settings'), __('Theme Settings'), 'edit_theme_options', $this->options->option_name, array($this, 'theme_settings'));
		}
	}
	
	function after_setup_theme(){
		
	}
	
	function define_theme_settings(){
		// override me	
	}
	
	function theme_settings(){
		// Security checkpoint
		if(!current_user_can('edit_theme_options')){
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		// Display theme options
?>
		<div class="wrap">
			<div id="icon-themes" class="icon32"></div>
			<h2><?php _e('Theme Options'); ?></h2>
			
			<?php settings_errors(); ?>
			<?php if(array_key_exists('saved', $_REQUEST) && $_REQUEST['saved']){ ?><div id="message" class="updated fade"><p><strong><?php _e('Settings saved.'); ?></strong></p></div><?php } ?>
			<form method="post" action="options.php">
				<?php if(!empty($this->settings_manager->tabs)){ ?>
				<?php 
					$current_tab = false;
					$current_tab_id = '';
					if(isset($_GET['tab'])){
						$current_tab_id = $_GET['tab'];	
					}
				?>
				<h2 class="nav-tab-wrapper">
					<?php $i = 0;?>
					<?php foreach($this->settings_manager->tabs as $tab){ ?>
						<?php if($current_tab_id == '' && $i == 0){ ?>
							<?php $current_tab = $tab; ?>
							<?php $current_tab_id = $tab->id; ?>
						<?php } elseif($current_tab_id == $tab->id){ ?> 
							<?php $current_tab = $tab; ?>
							<?php $current_tab_id = $tab->id; ?>
						<?php } ?>
						<a href="?page=<?php echo $this->options->option_name; ?>&tab=<?php echo $tab->id; ?>" class="nav-tab<?php echo ($current_tab_id == $tab->id) ? ' nav-tab-active' : ''; ?>"><?php echo $tab->title; ?></a>
						<?php $i++; ?>
					<?php } ?>
				</h2>

					<?php settings_fields($this->options->option_name . "_" . $current_tab->id); ?>
					<?php do_settings_sections($this->options->option_name . "_" . $current_tab->id); ?>
				<?php } else { ?>
					<?php settings_fields($this->options->option_name); ?>
					<?php do_settings_sections($this->options->option_name); ?>
				<?php } ?>
				
				<?php submit_button(); ?>
			</form>
		</div>	
<?php
	}
}
?>