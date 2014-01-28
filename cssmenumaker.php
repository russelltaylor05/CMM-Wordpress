<?php
/**
 * Plugin Name: CSS MenuMaker
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: A brief description of the Plugin.
 * Version: The Plugin's Version Number, e.g.: 1.0
 * Author: Name Of The Plugin Author
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: A "Slug" license name e.g. GPL2
 */


/* Include Files */
add_action( 'plugins_loaded', 'cssmenumaker_menu_load');
function cssmenumaker_menu_load()
{  
  require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cssmenumaker_widget.php');
  require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'walker.php');  
}



add_action('admin_head', 'cssmenumaker_admin_css');
function cssmenumaker_admin_css() 
{
  print '<link rel="stylesheet" type="text/css" href="'.plugins_url().'/cssmenumaker/css/styles.css">';  
}


add_action( "init", "cssmenumaker_create_menu_post_type");
function cssmenumaker_create_menu_post_type() 
{
  $labels = array(
    'name'               => 'CSS MenuMaker',
    'singular_name'      => 'CSS MenuMaker',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Menu',
    'edit_item'          => 'Edit Menu',
    'new_item'           => 'New Menu',
    'all_items'          => 'All Menus',
    'view_item'          => 'View Menu',
    'search_items'       => 'Search Books',
    'not_found'          => 'No menus found',
    'not_found_in_trash' => 'No menus found in Trash',
    'parent_item_colon'  => '',
    'menu_name'          => 'CSS MenuMaker'
  );
  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'cssmenu'),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => null,
    'supports'           => array( 'title')
  );

  register_post_type('cssmenu', $args);
}


add_action( 'admin_init', 'cssmenumaker_admin_init' );
function cssmenumaker_admin_init() 
{
  add_meta_box('cssmenumaker_menu_options',
               'Menu Options',
               'cssmenumaker_admin_menu_options',
               'cssmenu', 'normal', 'high' );
  add_meta_box('cssmenumaker_menu_css',
               'Menu CSS',
               'cssmenumaker_admin_menu_css',
               'cssmenu', 'normal', 'high' );
  //add_meta_box('ch5_cfu_upload_file', 'Upload File', 'ch5_cfu_upload_meta_box', 'cssmenu', 'normal' );                 
}


/* Display Menu Options */
function cssmenumaker_admin_menu_options( $cssmenu ) 
{  
  
  // Retrieve current author and rating based on review ID
  $cssmenu_structure = esc_html( get_post_meta( $cssmenu->ID, 'cssmenu_structure', true ) );
  $cssmenu_location = intval( get_post_meta( $cssmenu->ID, 'cssmenu_location', true ) );
  $wordpress_menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
  
  print '<label>Menu Structure</label>';
  print '<select style="width: 200px" name="cssmenu_structure">';
  foreach($wordpress_menus as $id => $menu) {
    print '<option value="'.$menu->slug.'"';
    print selected( $menu->slug, $cssmenu_structure ).'>';
    print $menu->name;
    print "</option>";
  }  
  print " </select>";
  print "<p class='help'>Pick a menu you would like to use as the structure</p>";

  print '<label>Menu Location</label>';
  print '<select style="width: 100px" name="cssmenu_location">';
  // Generate all items of drop-down list
  for ( $rating = 5; $rating >= 1; $rating -- ) {
    print '<option value="'.$rating.'"';
    print selected( $rating, $cssmenu_location ).'>';
    print  $rating.' stars';
  }
  print " </select>";
  print "<p class='help'>Pick a location for your menu. Leave blank if you plan on using this menu as a widget.</p>";  
}

/* Display Menu CSS */
function cssmenumaker_admin_menu_css( $cssmenu ) 
{  
  $cssmenu_css = esc_html(get_post_meta( $cssmenu->ID, 'cssmenu_css', true ) );
  print '<p>This is where the menu CSS will go</p>';
  print '<textarea name="cssmenu_css" id="cssmenu_css" style="width: 100%; height: 200px;">';
  print $cssmenu_css;
  print "</textarea>";
}



/* Save Menu Options */
add_action( 'save_post','ch4_br_add_book_review_fields', 10, 2 );
function ch4_br_add_book_review_fields( $cssmenu_id, $cssmenu ) {
  if ( $cssmenu->post_type == 'cssmenu' ) {    
    if ( isset( $_POST['cssmenu_structure'] ) && $_POST['cssmenu_structure'] != '' ) {
      update_post_meta($cssmenu_id, 'cssmenu_structure', $_POST['cssmenu_structure'] );
    }
    if ( isset( $_POST['cssmenu_location'] ) && $_POST['cssmenu_location'] != '' ) {
      update_post_meta($cssmenu_id, 'cssmenu_location', $_POST['cssmenu_location'] );
    }
    if ( isset( $_POST['cssmenu_css'] ) && $_POST['cssmenu_css'] != '' ) {
      update_post_meta($cssmenu_id, 'cssmenu_css', $_POST['cssmenu_css'] );
    }    
  }
}





?>