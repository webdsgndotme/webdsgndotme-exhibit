<?php

/**
 * Provides an exhibition post type and related funcionality to display a gallery.
 *
 * Plugin Name: webdsgndotme - Exhibit
 * Plugin URI:  http://webdsgn.me
 * Description: A Wordpress plugin to create exhibition galleries and venues.
 * Author:  webdsgndotme
 * Author URI: http://webdsgn.me
 * Version: 0.0.1
 */

$lib = 'webdsgndotme.inc.php';
// Let's find our base class on plugins dir first, load it from inc dir otherwise.
if (file_exists (WP_PLUGIN_DIR . '/' . $lib)) {
  include_once (WP_PLUGIN_DIR . '/' . $lib);
} else {
  include_once dirname (__FILE__) . '/inc/' . $lib;
}

class webdsgndotme_exhibit extends webdsgndotme_plugin {

  const VERSION = '0.0.1';

  protected function __construct() {
    $name = dirname(plugin_basename(__FILE__));
    parent::__construct($name, self::VERSION);
  }

  protected function set_options() {
    if ($options = parent::set_options()) {
      return $options;
    }

    $options = new stdClass;

    // Default slug for place posts
    $options->exhibit_slug = 'exhibit';
    // Prepend slug to places permalinks?
    $options->exhibit_with_front = false;

    // min width/height to base the grid on
    $options->cell_size = 150;
    // both right and bottom
    $options->gutter = 32;
    // item width/height = cell_size * r + 2 * padding
    $options->padding = 5;
    // 1 ... max_ratio
    $options->boundary = 4;

    update_option($this->domain, $options);

    $this->options = $options;
    return $this->options;
  }

  public function init() {
    require_once self::plugin_path() . '/post-type.php';
    webdsgndotme_exhibit_content::get()->register();
  }

}


$instance = webdsgndotme_exhibit::get();
register_activation_hook(__FILE__, array($instance, 'install'));
register_deactivation_hook(__FILE__, array($instance, 'uninstall'));
