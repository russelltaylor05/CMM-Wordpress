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
      $args['menu_class'] = '';
      $args['walker'] = new CSS_Menu_Maker_Walker();

      wp_enqueue_style("dynamic-css-{$available_menu->ID}", admin_url('admin-ajax.php')."?action=dynamic_css&selected={$available_menu->ID}");
      if($menu_js) {
        wp_enqueue_script("dynamic-script-{$available_menu->ID}", admin_url('admin-ajax.php')."?action=dynamic_script&selected={$available_menu->ID}");    
      }    
  	}
  }    
	return $args;
}





?>