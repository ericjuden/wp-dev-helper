<?php 
/**
 * Base plugin
 * @package WDH
 * @subpackage Plugin
 *
 */

require_once(ABSPATH . 'wp-admin/includes/plugin.php');    // Needed for is_plugin_active_for_network()
require_once(dirname(__FILE__) . '/constants.php');
require_once(WDH_LIB_DIR . '/settings.php');
require_once(WDH_LIB_DIR . '/options.php');
require_once(WDH_LIB_DIR . '/post-type.php');
require_once(WDH_LIB_DIR . '/taxonomy.php');
require_once(WDH_LIB_DIR . '/db-table.php');
require_once(WDH_LIB_DIR . '/list-table.php');

/**
 * Base plugin to inherit froms
 * @author ericjuden <eric@ericjuden.com>
 *
 */
class WDH_Plugin {
    var $is_network;
    var $plugin_slug = 'wdh';	// Set a default
    var $plugin_name = 'Plugin';
	var $options;
	var $settings_manager;
	var $tables = array();
	
	/**
	 * Constructor
	 */
    function __construct($plugin_slug, $plugin_name, $plugin_options_name = ''){
		$this->plugin_slug = $plugin_slug;
		$this->plugin_name = $plugin_name;
		
		$this->settings_manager = new WDH_Settings_Manager($this->plugin_slug, $plugin_options_name);
		if($plugin_options_name != ''){
			$this->options = new WDH_Options_Manager($plugin_options_name, $this->settings_manager);
		}
		
		$this->define_plugin_settings();
		
		add_action('admin_init', array($this, 'admin_init'));
		add_action(($this->is_network ? 'network_admin_menu' : 'admin_menu'), array($this, 'admin_menu'));
		if($this->is_network){
		    add_action('network_admin_edit_' . $this->options->option_name, array($this, 'save_network_options'));
		}

		if(!empty($this->tables)){
			if(!isset($this->options->created_tables)){
				$this->create_tables();

				$this->options->created_tables = true;
				$this->options->save();
			}
		}
	}

	function create_tables(){
		global $wpdb;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		foreach($this->tables as $table){
			$table_name = $wpdb->prefix . $table->name;
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){    // Does table already exist

				$sql = "CREATE TABLE " . $table_name . "(";

				$i = 1;
				$column_count = count($table->columns);
				foreach($table->columns as $column){
					$sql .= $column->name . " " . $column->data_type;    // example: column_name VARCHAR(100)
					if($column->options != ''){
						$sql .= " " . $column->options;                        // example: NOT NULL
					}
					if($column->auto_increment){
						$sql .= " AUTO_INCREMENT";
					}

					if($i <= $column_count && !empty($table->keys)){
						$sql .= ",";
					}

					$i++;
				}

				$i = 1;
				$key_count = count($table->keys);
				foreach($table->keys as $key){
					if($key->is_primary){
						$sql .= "PRIMARY KEY (" . $key->columns . ")";
					} elseif($key->is_unique){
						$sql .= "UNIQUE KEY " . $key->name . " (" . $key->columns . ")";
					}
					if($i < $key_count){
						$sql .= ",";
					}

					$i++;
				}
				$sql .= ");";

				$wpdb->query($sql);
			}
		}
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
				if(isset($this->options) && isset($this->options->option_name) && $this->options->option_name != '') {
					register_setting($this->options->option_name . '_' . $tab->id, $this->options->option_name . '_' . $tab->id);//, array($tab, 'sanitize_options'));
				}
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
			if(isset($this->options) && isset($this->options->option_name) && $this->options->option_name != ''){
				register_setting($this->options->option_name, $this->options->option_name);
			}
		}
	}
	
    function admin_init(){		
		$this->add_settings_data();
	}
	
	function admin_menu(){
		if(!empty($this->settings_manager->sections)){
    	    if($this->is_network){
    			add_submenu_page('settings.php', $this->plugin_name . __(' Options'), $this->plugin_name, 'manage_network_options', $this->options->option_name, array($this, 'plugin_options'));
    		} else {
    			add_options_page($this->plugin_name . __(' Options'), $this->plugin_name, 'edit_plugins', $this->options->option_name, array($this, 'plugin_options'));
    		}
		}
	}
	
	function define_plugin_settings(){
	    // override me
	}
	
	function plugin_options(){
	    // Security checkpoint
		if(!current_user_can('edit_theme_options')){
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		// Display options
?>
		<div class="wrap">
			<div id="icon-plugins" class="icon32"></div>
			<h2><?php echo $this->plugin_name . __(' Options'); ?></h2>
			
			<?php settings_errors(); ?>
			<?php if(array_key_exists('saved', $_REQUEST) && $_REQUEST['saved']){ ?><div id="message" class="updated fade"><p><strong><?php _e('Settings saved.'); ?></strong></p></div><?php } ?>
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
				
					<?php if($current_tab){ ?>
    					<?php if(!$this->options->{$current_tab->id}->is_site_option){ ?>
    					<form method="post" action="options.php">
    					<?php } else { ?>
    					<form method="post" action="<?php echo network_admin_url('edit.php'); ?>?action=<?php echo $this->options->option_name; ?>&tab=<?php echo $current_tab->id; ?>">
    					<?php } ?>
        			<?php } else { ?>
        				<?php if(!$this->options->is_site_option){ ?>
            			<form method="post" action="options.php">
            			<?php } else { ?>
            			<form method="post" action="<?php echo network_admin_url('edit.php'); ?>?action=<?php echo $this->options->option_name; ?>">
            			<?php } ?>
        			<?php } ?>

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
	
	function save_network_options(){
	    $current_tab = false;
	    $current_tab_id = '';
	    if(isset($_GET['tab'])){
	        $current_tab_id = $_GET['tab'];
	    }

	    $i = 0;
		foreach($this->settings_manager->tabs as $tab){
			if($current_tab_id == '' && $i == 0){
				$current_tab = $tab;
				$current_tab_id = $tab->id;
			 } elseif($current_tab_id == $tab->id){ 
				$current_tab = $tab;
				$current_tab_id = $tab->id;
			}
			$i++;
		}
	    
		// Are we saving a tab?
		if($current_tab != false){
		    foreach($current_tab->sections as $section){
		        foreach($section->controls as $control){
                    if(isset($_POST[$this->options->{$current_tab_id}->option_name][$control->id])){
                        $this->options->{$current_tab_id}->{$control->id} = $_POST[$this->options->{$current_tab_id}->option_name][$control->id];
                    }
		        }
		    }
		} else {
    		foreach($this->settings_manager->controls as $name=>$control){
    	        if(isset($_POST[$this->options->option_name][$name])){
    	            $this->options->{$name} = $_POST[$this->options->option_name][$name];
    	        }
    	    }    
		}
		
	    $this->options->save();
	    
	    $query_args = array(
	        'page' => $this->options->option_name,
	        'updated' => 'true'
	    );
	    if($current_tab_id != ''){
	        $query_args['tab'] = $current_tab_id;
	    }
	    
	    wp_redirect(add_query_arg($query_args, (is_multisite() ? network_admin_url('settings.php') : admin_url('options-general.php'))));
	    exit();
	}
}
?>