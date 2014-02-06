<?php
/**
 * Plugin Name: CSS MenuMaker
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: CSS MenuMaker
 * Version: 1.0
 * Author: CSS MenuMaker
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: A "Slug" license name e.g. GPL2
 */

define("IMAGE_CNT", 5);


/* Include Files */
add_action( 'plugins_loaded', 'cssmenumaker_menu_load');
function cssmenumaker_menu_load()
{  
  require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cssmenumaker_post_type.php');  
  require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cssmenumaker_widget.php');
  require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'walker.php');  
}




/* 
 * Ajax callback for Dynamic CSS and jQuery 
 */
add_action('wp_ajax_dynamic_css', 'dynaminc_css');
add_action('wp_ajax_nopriv_dynamic_css', 'dynaminc_css');                     
function dynaminc_css() {
  require(dirname(__FILE__).DIRECTORY_SEPARATOR.'css/dynamic.css.php');
  exit;
}  
add_action('wp_ajax_dynamic_script', 'dynamic_script');
add_action('wp_ajax_nopriv_dynamic_script', 'dynamic_script');
function dynamic_script() {
  require(dirname(__FILE__).DIRECTORY_SEPARATOR.'scripts/dynamic.js.php');
  exit;
}


add_action('wp_ajax_get_menu_json', 'ajax_get_menu_json');
add_action('wp_ajax_nopriv_get_menu_json', 'ajax_get_menu_json');
function ajax_get_menu_json() {
  $theme_id = $_GET['theme_id'];
  print file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR."menus/{$theme_id}/menu.json");
  die();
}  



/* 
 * Theme Location Overide for our menus
 */
add_filter('wp_nav_menu_args', 'cssmenumaker_modify_nav_menu_args');
function cssmenumaker_modify_nav_menu_args($args)
{
  $available_menus = get_posts(array("post_type" => "cssmenu"));  
  $registerd_locations = get_registered_nav_menus();
  
  foreach($available_menus as $id => $available_menu) {
    $cssmenu_location = get_post_meta( $available_menu->ID, 'cssmenu_location', true );
    $cssmenu_strucutre = get_post_meta( $available_menu->ID, 'cssmenu_structure', true );    
    $menu_css = get_post_meta($available_menu->ID, "cssmenu_css", true);
    $menu_js = get_post_meta($available_menu->ID, "cssmenu_js", true);    
    
  	if ($cssmenu_location == $args['theme_location']) {      
      $args['menu'] = $cssmenu_strucutre;
  		$args['container_id'] = "cssmenu-{$available_menu->ID}";
  		$args['container_class'] = "cssmenumaker-menu";
      $args['menu_class'] = '';
      $args['menu_id'] = '';      
      $args['walker'] = new CSS_Menu_Maker_Walker();

      wp_enqueue_style( 'cssmenumaker-base-styles', plugins_url().'/cssmenumaker/css/menu_styles.css');
      wp_enqueue_style("dynamic-css-{$available_menu->ID}", admin_url('admin-ajax.php')."?action=dynamic_css&selected={$available_menu->ID}");
      if($menu_js) {
        wp_enqueue_script("dynamic-script-{$available_menu->ID}", admin_url('admin-ajax.php')."?action=dynamic_script&selected={$available_menu->ID}");    
      }    
  	}
  }    
	return $args;
}



/* 
 * Shortcodes
 */

add_shortcode('cssmenumaker', 'cssmenumaker_shortcode');
function cssmenumaker_shortcode($atts) 
{
  extract(shortcode_atts(array('id' => 0), $atts));  
  return cssmenumaker_print_menu($atts['id']);
}

add_filter('manage_edit-cssmenu_columns', 'cssmenumaker_edit_columns') ;
function cssmenumaker_edit_columns($columns) 
{
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Accordion Menu' ),
		'shortcode' => __( 'Shortcode' ),
		'date' => __( 'Date' )
	);
	return $columns;
}

add_action('manage_cssmenu_posts_custom_column', 'cssmenumenumaker_manage_columns', 10, 2);
function cssmenumenumaker_manage_columns($column, $post_id) 
{
	global $post;
	switch( $column ) {
		case 'shortcode' :
				print '[cssmenumaker id="'.$post_id.'"]';
			break;
		default :
			break;
	}
}




/* 
 * Generic Print Menu
 * $menu_id  = the post ID
 */

function cssmenumaker_print_menu($menu_id = 0)
{
  if($menu_id) {
    $wordpress_menu = get_post_meta($menu_id, "cssmenu_structure", true);
    $menu_css = get_post_meta($menu_id, "cssmenu_css", true);
    $menu_js = get_post_meta($menu_id, "cssmenu_js", true);    

    wp_nav_menu(array(
      'menu' => $wordpress_menu,
      'container_id' => "cssmenu-{$menu_id}", 
      'container_class' => 'cssmenumaker-menu',
      'walker' => new CSS_Menu_Maker_Walker(),
      'menu_class' => '',
      'menu_id' => '',      
    ));

    wp_enqueue_style('cssmenumaker-base-styles', plugins_url().'/cssmenumaker/css/menu_styles.css');
    wp_enqueue_style("dynamic-css-{$menu_id}", admin_url('admin-ajax.php')."?action=dynamic_css&selected={$menu_id}");
    if($menu_js) {
      wp_enqueue_script("dynamic-script-{$menu_id}", admin_url('admin-ajax.php')."?action=dynamic_script&selected={$menu_id}");    
    }    
  }    
}










?>