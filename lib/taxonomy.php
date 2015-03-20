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

	// Modified copy of WordPress wp_dropdown_categories method
	function dropdown($args = ''){
		$defaults = array(
			'show_option_all' => '', 
			'show_option_none' => '',
			'orderby' => 'id', 
			'order' => 'ASC',
			'show_count' => 0,
			'hide_empty' => 1, 
			'child_of' => 0,
			'exclude' => '', 
			'echo' => 1,
			'selected' => 0, 
			'hierarchical' => 0,
			'name' => 'cat', 
			'id' => '',
			'class' => 'postform', 
			'depth' => 0,
			'tab_index' => 0, 
			'taxonomy' => $this->taxonomy,
			'hide_if_empty' => false, 
			'option_none_value' => -1,
			'multiple_select' => false
		);

		$defaults['selected'] = ( is_category() ) ? get_query_var( 'cat' ) : 0;

		// Back compat.
		if ( isset( $args['type'] ) && 'link' == $args['type'] ) {
			_deprecated_argument( __FUNCTION__, '3.0', '' );
			$args['taxonomy'] = 'link_category';
		}

		$r = wp_parse_args( $args, $defaults );
		$option_none_value = $r['option_none_value'];

		if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
			$r['pad_counts'] = true;
		}

		$tab_index = $r['tab_index'];

		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 ) {
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		}
		$categories = get_terms( $r['taxonomy'], $r );
		$name = esc_attr( $r['name'] );
		$class = esc_attr( $r['class'] );
		$id = $r['id'] ? esc_attr( $r['id'] ) : $name;

		$multiple_select = $r['multiple_select'];
		$multiple_select_attribute = '';
		if($multiple_select){
			$multiple_select_attribute = " multiple=\"multiple\"";
		}

		if ( ! $r['hide_if_empty'] || ! empty( $categories ) ) {
			$output = "<select name='$name' id='$id' class='$class'$multiple_select_attribute $tab_index_attribute>\n";
		} else {
			$output = '';
		}
		if ( empty( $categories ) && ! $r['hide_if_empty'] && ! empty( $r['show_option_none'] ) ) {

			/**
			 * Filter a taxonomy drop-down display element.
			 *
			 * A variety of taxonomy drop-down display elements can be modified
			 * just prior to display via this filter. Filterable arguments include
			 * 'show_option_none', 'show_option_all', and various forms of the
			 * term name.
			 *
			 * @since 1.2.0
			 *
			 * @see wp_dropdown_categories()
			 *
			 * @param string $element Taxonomy element to list.
			 */
			$show_option_none = apply_filters( 'list_cats', $r['show_option_none'] );
			$output .= "\t<option value='" . esc_attr( $option_none_value ) . "' selected='selected'>$show_option_none</option>\n";
		}

		if ( ! empty( $categories ) ) {

			if ( $r['show_option_all'] ) {

				/** This filter is documented in wp-includes/category-template.php */
				$show_option_all = apply_filters( 'list_cats', $r['show_option_all'] );
				$selected = ( '0' === strval($r['selected']) ) ? " selected='selected'" : '';
				$output .= "\t<option value='0'$selected>$show_option_all</option>\n";
			}

			if ( $r['show_option_none'] ) {

				/** This filter is documented in wp-includes/category-template.php */
				$show_option_none = apply_filters( 'list_cats', $r['show_option_none'] );
				$selected = selected( $option_none_value, $r['selected'], false );
				$output .= "\t<option value='" . esc_attr( $option_none_value ) . "'$selected>$show_option_none</option>\n";
			}

			if ( $r['hierarchical'] ) {
				$depth = $r['depth'];  // Walk the full depth.
			} else {
				$depth = -1; // Flat.
			}
			$output .= walk_category_dropdown_tree( $categories, $depth, $r );
		}

		if ( ! $r['hide_if_empty'] || ! empty( $categories ) ) {
			$output .= "</select>\n";
		}
		/**
		 * Filter the taxonomy drop-down output.
		 *
		 * @since 2.1.0
		 *
		 * @param string $output HTML output.
		 * @param array  $r      Arguments used to build the drop-down.
		 */
		$output = apply_filters( 'wp_dropdown_cats', $output, $r );

		if ( $r['echo'] ) {
			echo $output;
		}
		return $output;
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