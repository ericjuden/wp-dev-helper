<?php 

class WDH_Control {
	public $manager;
	public $id;
	public $settings;
	public $setting;
	public $priority = 10;
	public $section = '';
	public $title = '';
	public $choices = array();
	public $type = 'text';
	public $class = '';
	public $value = '';
	public $capability = 'edit_theme_options';
	
	function __construct($manager, $id, $args = array()){
		$keys = array_keys(get_class_vars(__CLASS__));
		foreach($keys as $key){
			if(isset($args[$key])){
				$this->{$key} = $args[$key];
			}
		}
		$this->manager = $manager;
		$this->id = $id;
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
	
	public function get_control_name(){
		$control_name = $this->manager->theme_options_name;
		if(!empty($this->manager->tabs)){
			$control_name .= '_' . $this->manager->sections[$this->section]->tab;
		}
		
		$control_name .= '[' . $this->id . ']';
		
		return apply_filters('wdh_control_name', $control_name, $this);
	}
	
	function render_class(){
		if($this->get_class() != ''){
			echo ' class="'. $this->get_class() .'"';
		}
	}
	
	/**
	 * Stub function to render content to screen
	 * @return void
	 */
	function render_content($args){	
		switch($this->type){
			case 'text':
				?>
				<input type="text" name="<?php echo $this->get_control_name(); ?>" id="<?php echo $this->id; ?>" value="<?php echo esc_attr($this->value); ?>"<?php $this->render_class(); ?> />
				<?php 
				break;
				
			case 'checkbox':
				?>
				<input type="checkbox" name="<?php echo $this->get_control_name(); ?>" id="<?php echo $this->id; ?>" value="<?php echo esc_attr($this->value); ?>" <?php checked($this->value); ?> />
				<?php
				break;
				
			case 'radio':
				if(empty($this->choices)){
					return;
				}
				
				foreach($this->choices as $value => $label){
				?>
					<label>
						<input type="radio" name="<?php echo $this->get_control_name(); ?>" <?php checked($this->value, $value); ?> />
						<?php echo esc_html($label); ?><br />
					</label>
				<?php
				}
				break;
				
			case 'select':
				if(empty($this->choices)){
					return;
				}
				?>
				<label>
					<select name="<?php echo $this->get_control_name(); ?>" id="<?php echo $this->id; ?>">
					<?php foreach($this->choices as $value => $label){ ?>
						<option value="<?php echo esc_attr($value); ?>" <?php selected($this->value, $value, true); ?>><?php echo esc_html($label); ?></option>
					<?php } ?>
					</select>
				</label>
				<?php 
				break;
		}
	}
	
	/**
	 * Sanitization callback when form is saved
	 * @param array $values
	 * @return array
	 */
	function sanitize_input($input){
		$output = $input;
		$output[$this->id] = strip_tags(stripslashes($output[$this->id]));
		return apply_filters('wdh_control_sanitize', $output, $input, $this);
	}
	
	function get_class(){
		$this->class = apply_filters('wdh_control_class', $this->id, $this->class);
	}
}
?>