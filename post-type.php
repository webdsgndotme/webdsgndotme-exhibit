<?php

class webdsgndotme_exhibit_content extends webdsgndotme_singleton {

  public function get_option($name, $default = '') {
    return webdsgndotme_exhibit::get()->get_option($name, $default);
  }

  public function register() {

    $exhibit_slug = $this->get_option('exhibit_slug');
    $exhibit_with_front = $this->get_option('exhibit_with_front');

    $exhibit_params = array(
      'labels' => array(
        'name' => __('Exhibitions', 'webdsgndotme_exhibit'),
        'singular_name' => __('Exhibition', 'webdsgndotme_exhibit'),
        'all_items' => __('All Exhibitions', 'webdsgndotme_exhibit'),
        'add_new' => __('Add Exhibition', 'webdsgndotme_exhibit'),
        'add_new_item' => __('Add New Exhibition', 'webdsgndotme_exhibit'),
        'edit' => __('Edit', 'webdsgndotme_exhibit'),
        'edit_item' => __('Edit Exhibition', 'webdsgndotme_exhibit'),
        'new_item' => __('New Exhibition', 'webdsgndotme_exhibit'),
        'view' => __('View Exhibition', 'webdsgndotme_exhibit'),
        'view_item' => __('View Exhibition', 'webdsgndotme_exhibit'),
        'search_items' => __('Search Exhibitions', 'webdsgndotme_exhibit'),
        'not_found' => __('No Exhibitions found', 'webdsgndotme_exhibit'),
        'not_found_in_trash' => __('No Exhibitionss found in trash', 'webdsgndotme_exhibit'),
        'parent' => __('Parent Exhibition', 'webdsgndotme_exhibit')
      ),
      'description' => __('You can add new exhibitions using this feature.', 'webdsgndotme_exhibit'),
      'public' => true,
      'show_ui' => true,
      'capability_type' => 'post',
      'publicly_queryable' => true,
      'exclude_from_search' => false,
      'hierarchical' => false, // it could be, later
      'rewrite' => array('slug' => $exibit_slug, 'with_front' => $exhibit_with_front),
      'query_var' => true,
      'supports' => array('title', 'editor', 'excerpt', 'thumbnail'), // TODO: Add comments
      'taxonomies' => array('category'),
      'register_meta_box_cb' => array($this, 'add_exhibit_metaboxes'),
      'has_archive' => $place_slug,
      'show_in_nav_menus' => false,
    );

    register_post_type('webdsgdotme_exhibit', apply_filters('webdsgndotme_exhibit:exhibit_post_type', $exhibit_params));
  }

  function add_exhibit_metaboxes() {
    error_log('called!');
  }

}


class webdsgndotme_exhibition extends webdsgndotme_base {
}