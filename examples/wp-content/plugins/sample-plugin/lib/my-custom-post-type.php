<?php

class Sample_Custom_Post_Type extends WDH_Post_Type {
    function __construct(){
        $this->post_type = 'sample_posttype';

        parent::__construct();

        // Add any WP actions you need to fire
        add_action('init', array($this, 'init'));
    }

    function init(){
        register_post_type(
            $this->post_type,
            array(
                'capability_type' => 'post',
                'exclude_from_search' => false,
                'hierarchical' => false,
                'labels' => array(
                    'name' => __('Sample Posts'),
                    'singular_name' => __('Sample Post'),
                    'add_new' => __('Add New'),
                    'add_new_item' => __('Add New Sample Post'),
                    'edit' => __('Edit'),
                    'edit_item' => __('Edit Sample Post'),
                    'new_item' => __('New Sample Post'),
                    'view' => __('View'),
                    'view_item' => __('View Sample Posts'),
                    'search_items' => __('Search Sample Posts'),
                    'not_found' => __('No sample posts found'),
                    'not_found_in_trash' => __('No sample posts found in Trash'),
                ),
                'map_meta_cap' => true,
                'public' => true,
                'publicly_queryable' => true,
                'query_var' => true,
                //'register_meta_box_cb' => array($this, 'metabox_register'),
                'rewrite' => array('slug' => 'samples', 'with_front' => true),
                'show_ui' => true,
                'show_in_rest' => true,
                'supports' => array('title', 'editor', 'revisions', 'thumbnail', 'excerpt', 'author'),
            )
        );
    }
}
?>