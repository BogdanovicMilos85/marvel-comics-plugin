<?php
function marvel_comics_activate_plugin() {
  if( version_compare( get_bloginfo('version'), '4.9', '<') ) {
    wp_die( __('You must update WordPress to use this plugin', 'marvel-comics') );
  }
}

