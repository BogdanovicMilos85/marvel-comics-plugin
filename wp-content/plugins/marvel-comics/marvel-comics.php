<?php
/**
 * Plugin Name:       Marvel Comics
 * Description:       Marvel Comics is a custom plugin which allows you to search the Marvel comics
 * Version:           1.0
 * Author:            Milos Bogdanovic
 * Author URI:        https://milosbogdanovic.com
 * Text Domain:       marvel-comics
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/BogdanovicMilos85/marvel-comics-plugin
 */

if ( ! function_exists( 'add_action' ) ) {
	die( "Hi there! I'm just a plugin, not much I can do when called directly." );
	exit;
}

 // Includes
include( 'inc/activate.php' );
include( 'inc/init.php' );

 // Hooks
 register_activation_hook( __FILE__, 'marvel_comics_activate_plugin' );
 add_action( 'init', 'marvel_comics_init');

// Add Shortcode for Comics Search
add_shortcode('search-marvel-comics', 'marvel_comics_plugin_shortcode');

function marvel_comics_plugin_shortcode() {
  // Adding styles and scripts
  wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
  wp_enqueue_style('marvel-comics-plugin-styles', plugins_url('assets/css/style.css', __FILE__) );
  wp_enqueue_script('marvel-comics-plugin-script', plugins_url('assets/js/scripts.js', __FILE__), array('jquery'), '1.0.0', true );
  wp_localize_script("marvel-comics-plugin-script", "marvelData", array(
    "root_url" => get_site_url(),
    "nonce" => wp_create_nonce("wp_rest")
  ));

  ob_start();
	
	if( post_type_exists('comics') ) :
		include( 'inc/search-form.php' );
		
	endif;
	
	return ob_get_clean();
}

 // Setup API call snd cron job
if ( ! wp_next_scheduled( 'start_getting_comics_from_api' ) ) { 
  wp_schedule_event( time(), 'hourly', 'start_getting_comics_from_api' );
}

function start_getting_comics_from_api() {

  if( post_type_exists('comics') ) {
    get_comics_from_api();
  }
  
}
add_action('the_post', 'start_getting_comics_from_api');

function get_comics_from_api() {
  $ts = time();
  $public_key = '099736801919555940ca6d07b6bb444c';
  $private_key = '993e6f1936115d09ee54c906f9648702c9c5ca7a';
  $hash = md5($ts . $private_key . $public_key);
  $marvel_url = 'https://gateway.marvel.com/v1/public/comics?';

  $comics = [];

  $results = wp_remote_retrieve_body( wp_remote_get( $marvel_url . sprintf('ts=%s&hash=%s&apikey=%s', $ts, $hash, $public_key)));

  $results = json_decode( $results);

  $comics = $results->data->results;

  if( $results->status == 'Ok' && ! empty( $results ) ) {
    foreach( $comics as $comic) {

      $inserted_comic = wp_insert_post([
        'post_title'  => $comic->title,
        'post_type'   => 'comics',
        'post_status' => 'publish'
      ]);
  
      if( is_wp_error( $inserted_comic ) ) {
        continue;
      }
      
      // Fields in Comics CPT - Added by Advanced Custom Fields plugin
      $labels = [
        'field_622a43ac65b16' => 'title',
        'field_622a43e365b17' => 'description',
        'field_622a441f65b18' => 'isbn',         
        'field_622a445665b19' => 'pageCount',
        'field_622a449665b1a' => 'resourceURI',
      ];
      
      // Adding the data to the fields in Comics CPT
      foreach( $labels as $key => $name) {
        update_field( $key, $comic->{$name}, $inserted_comic);
      }
    }
  } else {
    return false;
  }
}

register_deactivation_hook( __FILE__, 'cron_deactivate' ); 
 
function cron_deactivate() {
    $timestamp = wp_next_scheduled( 'start_getting_comics_from_api' );
    wp_unschedule_event( $timestamp, 'start_getting_comics_from_api' );
}

add_filter( 'widget_text', 'do_shortcode' );
do_action('wp_ajax_nopriv_get_comics_from_api');
do_action('wp_ajax_get_comics_from_api');