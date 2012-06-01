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
      'rewrite' => array('slug' => $exhibit_slug, 'with_front' => $exhibit_with_front),
      'query_var' => true,
      'supports' => array('title', 'editor', 'excerpt', 'thumbnail'), // TODO: Add comments
      'taxonomies' => array('category'),
      'register_meta_box_cb' => array($this, 'add_exhibit_metaboxes'),
      'has_archive' => $exhibit_slug,
      'show_in_nav_menus' => false,
    );

    // we handle our posts into this custom post type
    register_post_type('webdsgdotme_exhibit', apply_filters('webdsgndotme_exhibit:exhibit_post_type', $exhibit_params));

    // register an input into the media uploader edit media's form so the user can set a custom
    // exhibit thumb size value for this attachment
    add_filter('attachment_fields_to_edit', array($this, 'add_attachment_fields'), 10, 2);
    add_filter('attachment_fields_to_save', array($this, 'save_attachment_fields'), 10, 2);
    // we need some field widths to be tweaked
    add_action('admin_print_styles-media-upload-popup', array($this, 'print_media_popup_styles'));
  }

  function add_exhibit_metaboxes() {
    global $post;
    if ($post && $post->ID && $post->post_type == 'webdsgdotme_exhibit') {
      add_meta_box('exhibit-gallery', __('Gallery', 'webdsgndotme_exhibit'), array($this, 'render_gallery_box'), 'webdsgdotme_exhibit', 'normal', 'default');
    }
  }

  function add_attachment_fields($fields, $post) {
    if (!isset($_REQUEST['post_id']))
      return $fields;

    $post_id = intval($_REQUEST['post_id']);
    if (get_post_type($post_id) != 'webdsgdotme_exhibit')
      return $fields;

    $meta = get_post_meta($post->ID, 'webdsgndotme_exhibit:exhibit_' . $post_id . '_size', true);
    $value = $meta ? explode('x', $meta) : array();
    // TODO: default by options
    $fields['exhibit_size'] = array(
      'value' => $value,
      'label' => __('Exhibition Size', 'webdsgndotme_exhibit'),
      'input' => 'html',
      'html' => $this->get_size_input($post, $value),
      'helps' => __('Thumb size at exhibition listing, e.g. 3x4 (3 cell units of width x 4 cell units of height)', 'webdsgndotme_exhibit')
    );

    return $fields;
  }

  public function save_attachment_fields($post, $attachment) {
    if (!isset($_REQUEST['post_id']))
      return $fields;

    $post_id = intval($_REQUEST['post_id']);
    if (get_post_type($post_id) != 'webdsgdotme_exhibit')
      return $fields;


    $size = $attachment['exhibit_size'];
    $boundary = $this->get_option('boundary');
    if (!empty($size) && count($size) == 2) {
      $valid = false;
      foreach ($size as $dim)
        $valid = $dim >= 1 && $dim <= $boundary;

      if ($valid)
        update_post_meta($post['ID'], 'webdsgndotme_exhibit:exhibit_' . $post_id . '_size', implode('x', $size));
      else
        unset($attachment['exhibit_size']);
    }

    return $post;
  }

  function render_gallery_box() {
    global $post;
    $exhibit = new webdsgndotme_exhibition($post->ID);
    $images = $exhibit->get_images();
?>
<style>
  .exhibit-gallery-admin { margin: 0 -5px -5px; padding: 0; overflow: hidden; }
  .exhibit-gallery-admin li { margin: 5px 5px 0 0; float: left; }
</style>
<div class="inside">
  <?php if (count($images)): ?>
    <ul class="exhibit-gallery-admin">
      <?php foreach ($images as $image): ?>
        <?php $tab_url = 'media-upload.php?post_id=' . $post->ID . '&amp;tab=gallery&amp;TB_iframe=1&amp;width=640&amp;height=437&amp;#media-item-' . $image->id; ?>
        <li><a href="<?php print $tab_url; ?>" class="thickbox add_media"><?php print wp_get_attachment_image($image->id, array(64, 64)); ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p><?php print sprintf(__('Start <a class="thickbox add_media" href="%s" title="Media Uploader">uploading images</a> to feature on this exhibition.', 'webdsgndotme-exhibit'), 'media-upload.php?post_id=' . $post->ID . '&amp;TB_iframe=1&amp;width=640&amp;height=437'); ?></p>
  <?php endif; ?>
</div>
<?php
  }

  function print_media_popup_styles() {
?>
    <style>
      .media-item .describe .exhibit-size { line-height: 23px; }
      .media-item .describe .exhibit-size input[type="text"] { width: 60px; text-align: center; }
    </style>
<?php
  }


  // helpers
  protected function get_size_input($post, $value) {
    $width_label = __('width');
    $height_label = __('height');

    $value = empty($value) ? array('', '') : $value;
    $inputs = array();
    $inputs[] = "<input type='text' placeholder='$width_label' class='text exhibit-size-field' name='attachments[$post->ID][exhibit_size][0]' value='" . esc_attr($value[0]) . "' />";
    $inputs[] = "<input type='text' placeholder='$height_label' class='text exhibit-size-field' name='attachments[$post->ID][exhibit_size][1]' value='" . esc_attr($value[1]) . "' />";
    return $output = "<div class='exhibit-size'>" . implode($inputs, ' x ') . "</div>";
  }

}


class webdsgndotme_exhibition extends webdsgndotme_base {

  protected $id;

  function __construct($id) {
    $this->id = $id;
  }

  public function get_id() {
    return $this->id;
  }

  public function get_thumb_id() {
    return get_post_thumbnail_id($this->get_id());
  }

  public function get_images($options = array()) {
    $args = array(
      'numberposts' => -1, //TODO: add pagination
      'orderby' => 'id',
      'order' => 'asc',
    );

    $private_args = array(
      'post_type' => 'attachment',
      'post_mime_type' => 'image',
      'post_parent' => $this->get_id()
    );

    $args = array_merge($args, $options, $private_args);
    $attachments = get_posts($args);
    if (!$attachments) {
      return null;
    }

    $images = array();
    foreach ($attachments as $attachment) {
      $p = get_post($attachment->ID);
      $image = $this->image_vo();
      $image->id = $p->ID;
      $image->title = $p->post_title;
      list ($image->url, $image->width, $image->height) = wp_get_attachment_image_src($p->ID, 'full');
      // TODO: add sized src
      //$image->size = get_post_meta($this->get_id(), '')
      $images[] = $image;
    }

    wp_reset_query();

    return $images;
  }

  protected function image_vo() {
    $image = new stdclass;
    $image->id = -1;
    $image->url = '';
    $image->title = '';
    $image->width = 0;
    $image->height = 0;
    $image->size = null;
    $image->sized_src = array();
    return $image;
  }

}





/*
Dropped, gonna use custom fields for media manager
// Forms
class webdsgndotme_exhibit_imagefield extends webdsgndotme_exhibit_fieldset {

  protected $image;
  protected $parent_post;

  protected $size;
  protected $ord;
  protected $visible;

  protected $title;
  protected $caption;
  protected $alt;
  protected $desc;

  public function __construct($options) {
    super::__construct($options);

    $post = get_post($this->image->get_id());

    $this->title = $post->post_title;
    $this->caption = $post->post_excerpt;
    $this->desc = $post->post_content;
    $this->alt = get_post_meta($this->image->get_id(), '_wp_attachment_image_alt', true);

    $meta_prefix = 'webdsgndotme_exhibit:exhibit_' . $this->parent_post;
    $this->size = get_post_meta($this->image->get_id(), $meta_prefix . '_size', true);
    $this->ord = get_post_meta($this->image->get_id(), $meta_prefix . '_ord', true);
    $this->visible = get_post_meta($this->image->get_id(), $meta_prefix . '_visible', true);
  }

  public function render($parent_name) {
    $output = '<div class="imagefield">';
     return $output;
  }
}

*/