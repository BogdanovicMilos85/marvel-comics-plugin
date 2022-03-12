<?php
// Register Comics CPT
function marvel_comics_init() {
  register_post_type('comics', array( 
    'has_archive'     => true,
    'rewrite'         => array('slug' => 'comics'),
    'public'          => true,
    'labels'          => array(
        'name' => 'Comics',
        'add_new_item' => 'Add New Comic',
        'edit_item' => 'Edit comic',
        'all_items' => 'All Comics',
        'singular_name' => 'Comic'
    ),
    'show_ui'               => true,
    'show_in_menu'          => true,
    'capability_type'       => 'post',
    'show_in_rest'          => true,
    'menu_icon'             => 'dashicons-awards',
  ));

  register_taxonomy( 'characters', 'comics', array(
    'label'        => __( 'Characters', 'marvel-comics' ),
    'rewrite'      => array( 'slug' => 'comics/character' )
  ));

  register_taxonomy( 'creators', 'comics', array(
    'label'        => __( 'Creators', 'marvel-comics' ),
    'rewrite'      => array( 'slug' => 'comics/creator' )
  ));
}