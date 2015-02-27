<?php 

class WDH_Taxonomy {
	public $taxonomy = '';
	public $labels = array();
	public $post_types = array();
	public $args = array();
	
	function __construct($post_types, $args = array()){
		$this->post_types = $post_types;
	    
	    $defaults = array(
	    	'labels' => $this->labels,
	    	'public' => true,
	    	'show_ui' => true,
	        'show_admin_column' => true,
	        'hierarchical' => true,
	    );
	    
	    $r = wp_parse_args($args, $defaults);
		$this->args = $r;
		
		add_action('init', array($this, 'init'));
	}
	
	function create($term){
		return wp_create_term($term, $this->taxonomy);
	}
	
	function get($term_id){
		return get_term($term_id, $this->taxonomy, OBJECT);
	}
	
	function get_by_slug($slug, $filter = 'raw'){
	    global $wpdb;
	    
	    $sql = "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '$this->taxonomy' AND t.slug = '$slug' LIMIT 1";
	    $_term = $wpdb->get_row($wpdb->prepare($sql));
	    if (!$_term){
		    return $null;
	    }
	    
	    $_term = apply_filters('get_term', $_term, $taxonomy);
    	$_term = apply_filters("get_$this->taxonomy", $_term, $taxonomy);
    	$_term = sanitize_term($_term, $this->taxonomy, $filter);
    	
    	return $_term;
	}
	
	function get_multi($args = ''){
		return get_terms(array($this->taxonomy), $args);
	}
	
	function init(){
		register_taxonomy($this->taxonomy, $this->post_types, $this->args);
	}
	
	function list_terms($post_id, $separator = ', '){
	    return get_the_term_list($post_id, $this->taxonomy, '', $separator, '');
	}
	
	function __get($key){
		return ${$key};
	}
	
	function __set($key, $value){
		${key} = $value;
	}
}
?>