<?php 

require_once(WDH_LIB_DIR . '/list-table.php');

class WDH_Post_Type {
    public $post_type = '';
    public $list_columns = array();
    public $labels = array();
    
    function __construct(){
        add_action('init', array($this, '_init'));
		add_action('manage_' . $this->post_type . '_posts_custom_column', array($this, 'manage_custom_columns'));
		add_action('request', array($this, 'request'), 1);
		add_action('restrict_manage_posts', array($this, 'restrict_manage_posts'));
		add_action('save_post', array($this, 'metabox_save'));
		add_filter('manage_edit-' . $this->post_type . '_columns', array($this, 'edit_columns'));
		add_filter('manage_edit-' . $this->post_type . '_sortable_columns', array($this, 'sortable_columns'));
    }    
    
    function _init(){ 
	    // Setup initial columns
	    $this->list_columns['cb'] = new WDH_List_Table_Column('cb', '<input type="checkbox" />', false, 10);

		if(post_type_supports($this->post_type, 'title')){
	    	$this->list_columns['title'] = new WDH_List_Table_Column('title', __('Title'), true, 20);
	 	}
	    
	    if(post_type_supports($this->post_type, 'author')){
	        $this->list_columns['author'] = new WDH_List_Table_Column('author', __('Author'), true, 30);
	    }
	    
	    $this->list_columns['date'] = new WDH_List_Table_Column('date', __('Date'), true, 40);
	    
	    // Call to add other columns and reorganize
	    $this->build_columns();
	}
	
	/**
	 * 
	 * Build list table columns and sort by display_order
	 * 
	 * parent::build_columns() must be called from inherited class to sort
	 * 
	 */
    function build_columns(){	    
	    // Sort by display_order
	    usort($this->list_columns, array($this, 'usort_reorder'));
	}
	
	/**
	 * 
	 * Add a new post
	 * 
	 * @param string $title
	 * @param string $content
	 * @param string $slug
	 * @param string $status
	 */
	function create($title, $content, $slug, $status = 'publish'){
	    global $user;
	    
	    return wp_insert_post(array(
	        'post_title' => $title,
	        'post_content' => $content,
	    	'post_status' => $status,
	        'post_author' => $user->ID,
	        'post_type' => $this->post_type,
	        'post_name' => $slug	    
	    ));
	}
	
	/**
	 * 
	 * Edit list of columns for list table
	 * 
	 * @param mixed $columns
	 */
	function edit_columns($columns){
	    // Override in child class
	    $columns = array();
	    foreach($this->list_columns as $col){
	        $columns[$col->name] = $col->label;
	    }
	    return $columns;
	}
	
	/**
	 * 
	 * Retrieve a post by the id
	 * 
	 * @param int $post_id
	 */
	function get($post_id){
		return get_post($post_id, OBJECT);
	}
	
	/**
	 * 
	 * Retrieve a post by the slug
	 * 
	 * @param string $slug
	 * @param string $filter
	 */
	function get_by_slug($slug, $filter = 'raw'){
	    global $wpdb;
	    
	    $sql = "SELECT * FROM $wpdb->posts WHERE post_name = '$slug' LIMIT 1";
	    $_post = $wpdb->get_row($wpdb->prepare($sql));
	    if (!$_post){
		    return null;
	    }
	    
	    if ($filter != 'raw'){
		    $_post = sanitize_post($_post, $filter);
	    }
    	
    	return $_post;
	}
	
	/**
	 * 
	 * Retrieve multiple posts
	 * 
	 * @param array $args
	 */
	function get_multi($args = array()){
		$defaults = array(
			'post_type' => $this->post_type,
			'post_status' => 'publish',
			'orderby' => 'title',
			'order' => 'ASC',
			'post_per_page' => -1,
		    'numberposts' => -1
		);
		
		$r = wp_parse_args($args, $defaults);
		return get_posts($r);
	}
	
	/**
	 * 
	 * Retrieve multiple posts by a taxonomy slug
	 * 
	 * @param string $slug
	 */
    function get_multi_by_taxonomy($slug){
	    global $wpdb;
	    
	    $sql = "SELECT DISTINCT p.* FROM $wpdb->posts p INNER JOIN $wpdb->term_relationships tr ON tr.object_id = p.ID INNER JOIN $wpdb->term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id INNER JOIN $wpdb->terms t ON t.term_id = tt.term_id WHERE p.post_type = '$this->post_type' AND p.post_status IN ('publish') AND t.slug = '$slug' ORDER BY post_title ASC";
		return $wpdb->get_results($sql, OBJECT);
	}
	
	/**
	 * 
	 * Retrieve multiple posts by a term id associated with them
	 *
	 * @param int $term_id
	 * @param array $args
	 */
	function get_multi_by_term($term_id, $args = array()){
	    global $wpdb;
	    $defaults = array(
	        'order_by' => 'post_title',
	        'order' => 'ASC',
			'show_private' => false,
	    );
	    $atts = wp_parse_args($args, $defaults);

		$statuses = "'publish'";
		if($atts['show_private']){
			$statuses .= ", 'private'";
		}

	    $sql = "SELECT DISTINCT p.* FROM $wpdb->posts p INNER JOIN $wpdb->term_relationships tr ON tr.object_id = p.ID INNER JOIN $wpdb->term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id INNER JOIN $wpdb->terms t ON t.term_id = tt.term_id WHERE p.post_type = '$this->post_type' AND p.post_status IN ($statuses) AND t.term_id = $term_id ORDER BY ". $atts['order_by'] . " " . $atts['order'];
		return $wpdb->get_results($sql, OBJECT);
	}
	
	function __get($key){
		return ${$key};
	}
	
	/**
	 * 
	 * Set custom values to be displayed on list table
	 * 
	 * @param mixed $column
	 */
	function manage_custom_columns($column){
	    // Override in child class
	}
	
	function metabox_info(){
		// Override in child class
	}
	
	function metabox_register(){
		// Override in child class
		add_meta_box($this->post_type, $this->labels['singular_name'] . ' ' . __('Information'), array($this, 'metabox_info'), $this->post_type, 'normal', 'high');
	}
	
	function metabox_save(){
	    // Override in child class
	}
	
	function __set($key, $value){
		${key} = $value;
	}
	
	function request($request){
	    if(is_admin() && strpos($GLOBALS['PHP_SELF'], '/wp-admin/edit.php') && isset($request['post_type']) && $request['post_type'] == $this->post_type){
	        
    	    $taxonomies = get_object_taxonomies($this->post_type, 'objects');
    	    foreach($taxonomies as $tax){
    	        $tax_slug = $tax->rewrite['slug'];
    	        if(isset($_REQUEST[$tax_slug])){
    	            $term = get_term($_REQUEST[$tax_slug], $tax->name);
    	            $request[$tax_slug] = $term->term_id;
    	            $request['term'] = $term->name; 
    	            break;
    	        }
    	    }
	    }
	    
	    
	    return $request;
	}
	
	/**
	 * 
	 * Adds ability to filter list table by custom taxonomies
	 * 
	 */
	function restrict_manage_posts(){
	    global $typenow;

	    // Make sure we are working with the correct post type
	    if($typenow == $this->post_type){
	        // Retrieve taxonomies of this post type
	        $taxonomies = get_object_taxonomies($this->post_type, 'objects');

	        foreach($taxonomies as $tax){
    	        $tax_name = $tax->labels->name;
    	        $objs = get_terms(array('taxonomy' => $tax->name));
    	        if(count($objs) > 0){
        	        echo '<select name="'. $tax->name .'" id="'. $tax->name .'" class="postform">';
        	        echo '<option value="">'. _('Show All') . ' ' . $tax_name . '</option>';
        	        foreach($objs as $term){
        	            echo '<option value="'. $term->slug . '"' . ((isset($_GET[$tax->name]) && $_GET[$tax->name] == $term->slug) ? ' selected="selected"' : '') . '>' . $term->name . ' (' . $term->count . ')</option>';
        	        }
        	        echo '</select>';
    	        }
	        }
	    }
	}
	
	/**
	 * 
	 * Make a list of the columns that are sortable for the list table
	 * 
	 * @param array $columns
	 */
	function sortable_columns($columns){
	    $sortable_columns = array();
        foreach($this->list_columns as $column){
            if($column->is_sortable){
                $sortable_columns[$column->name] = array($column->name, false);
            }
        }
        
        return $sortable_columns;
	}
	
	function usort_reorder($a, $b){
	    $result = strcmp($a->display_order, $b->display_order);
	    return $result;
	}
}
?>