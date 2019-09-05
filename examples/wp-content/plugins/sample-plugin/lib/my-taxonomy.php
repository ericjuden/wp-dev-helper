<?php

class Sample_Taxonomy extends WDH_Taxonomy {
    function __construct(){
        // Supply the slug for the taxonomy
        $this->taxonomy = 'sample_taxonomy';

        // Which post types should this show up for?
        $this->post_types = array('sample_posttype');

        // Define your labels
        $this->labels = array(
            'name' => __('Categories'),
            'singular_name' => __('Category'),
            'all_items' => __('All Categories'),
            'edit_item' => __('Edit Category'),
            'view_item' => __('View Category'),
            'update_item' => __('Update Category'),
            'add_new_item' => __('Add New Category'),
            'new_item_name' => __('New Category'),
            'parent_item' => __('Parent Category'),
            'parent_tem_colon' => __('Parent Category:'),
            'search_items' => __('Search Categories'),
            'popular_items' => __('Popular Categories'),
            'separate_items_with_commas' => __('Separate categories with commas'),
            'add_or_remove_items' => __('Add or remove categories'),
            'choose_from_most_used' => __('Choose from the most used categories'),
            'not_found' => __('No categories found.')
        );

        // Any additional args go here
        $this->args = array(
            'hierarchical' => true,
            'rewrite' => array(
                'slug' => 'sample_taxonomy',
                'ep_mask' => EP_PERMALINK
            ),
        );

        // Inherit from WDH_Taxonomy
        parent::__construct($this->post_types, $this->args);
    }
}
?>