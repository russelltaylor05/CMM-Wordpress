<?php
/**
 * Plugin Name: CSS MenuMaker
 * Plugin URI: http://cssmenumaker.com/wordpress-menu-plugin
 * Description: CSS MenuMaker
 * Version: 1.1.1
 * Author: CSS MenuMaker
 * Author URI: http://cssmenumaker.com/wordpress-menu-plugin
 * License: A "Slug" license name e.g. GPL2
 */

define("TRIAL", 0);

/* Include Files */
add_action('plugins_loaded', 'cssmenumaker_menu_load');
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
 * This filter modifies the HTML output of our menus before printing to the screen
 * 
 */
add_filter('wp_nav_menu_args', 'cssmenumaker_modify_nav_menu_args', 5000);
function cssmenumaker_modify_nav_menu_args($args)
{
  
  /* Pass cssmenumaker_flag & cssmenumaker_id to the wp_nav_menu() and this filter kicks in */
  if(isset($args['cssmenumaker_flag']) && isset($args['cssmenumaker_id'])) {

    $id = $args['cssmenumaker_id'];
    $menu_settings = json_decode(get_post_meta($id, 'cssmenu_settings', true));     
    $wordpress_menu = get_post_meta($id, "cssmenu_structure", true);

    $args['menu'] = $wordpress_menu;
    $args['container'] = "div";
		$args['container_id'] = "cssmenu-{$id}";
		$args['container_class'] = "cssmenumaker-menu";
    $args['menu_class'] = '';
    $args['menu_id'] = '';  
    $args['depth'] = $menu_settings->depth;            
    $args['walker'] = new CSS_Menu_Maker_Walker();
    
  } 

  /* We are changing Args for a menu displayed in a theme location */
  $available_menus = get_posts(array("post_type" => "cssmenu"));    
  foreach($available_menus as $id => $available_menu) {
    $cssmenu_location = get_post_meta( $available_menu->ID, 'cssmenu_location', true );
    $cssmenu_structure = get_post_meta( $available_menu->ID, 'cssmenu_structure', true );    
    $menu_css = get_post_meta($available_menu->ID, "cssmenu_css", true);
    $menu_js = get_post_meta($available_menu->ID, "cssmenu_js", true);    
    $menu_settings = json_decode(get_post_meta( $available_menu->ID, 'cssmenu_settings', true)); 
    
    if ($cssmenu_location == $args['theme_location']) {
      if(!demo_check()) {
        
        $args['menu'] = $cssmenu_structure;
    		$args['container'] = "div";
        $args['container_id'] = "cssmenu-{$available_menu->ID}";
    		$args['container_class'] = "cssmenumaker-menu";
        $args['menu_class'] = '';
        $args['menu_id'] = '';  
        $args['depth'] = $menu_settings->depth;            
        $args['walker'] = new CSS_Menu_Maker_Walker();

        wp_enqueue_style( 'cssmenumaker-base-styles', plugins_url().'/cssmenumaker/css/menu_styles.css');
        wp_enqueue_style("dynamic-css-{$available_menu->ID}", admin_url('admin-ajax.php')."?action=dynamic_css&selected={$available_menu->ID}");
        if($menu_js) {
          wp_enqueue_script("dynamic-script-{$available_menu->ID}", admin_url('admin-ajax.php')."?action=dynamic_script&selected={$available_menu->ID}");    
        }            
      } else {
        $args['echo'] = false;
        print "Your menu will not be displayed while MenuMaker is in demo mode. Please <a href='http://cssmenumaker.com/wordpress-menu-plugin'>purchase a key</a> to unlock the full version.";    
      }
  	}
  }

	return $args;
}


/* 
 * Generic Print Menu
 * $menu_id  = the post ID
 */

function cssmenumaker_print_menu($menu_id = 0)
{
  /* Did we get a valid menu ID? */
  $post = get_post(intval($menu_id));
  if(!$post || $post->post_type != 'cssmenu') {
    print "The ID you provided is not a valid MenuMaker menu.";
    return;
  }
  
  if(!demo_check()) {

    wp_nav_menu(array(
      'cssmenumaker_flag' => true,
      'cssmenumaker_id' => $menu_id
    ));

    $menu_css = get_post_meta($menu_id, "cssmenu_css", true);
    $menu_js = get_post_meta($menu_id, "cssmenu_js", true);    
    if($menu_css) {
      wp_enqueue_style('cssmenumaker-base-styles', plugins_url().'/cssmenumaker/css/menu_styles.css');
      wp_enqueue_style("dynamic-css-{$menu_id}", admin_url('admin-ajax.php')."?action=dynamic_css&selected={$menu_id}");      
    }
    if($menu_js) {
      wp_enqueue_script("dynamic-script-{$menu_id}", admin_url('admin-ajax.php')."?action=dynamic_script&selected={$menu_id}");    
    }    

  } else {
    print "Your menu will not be displayed while MenuMaker is in demo mode. Please <a href='http://cssmenumaker.com/wordpress-menu-plugin'>purchase a key</a> to unlock the full version.";    
  }
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
		'title' => __( 'Menus' ),
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
 * Admin Messages
 */

/*
add_action('admin_notices', 'beta_notice' );
function beta_notice() {
  $screen = get_current_screen();
  if($screen->id == 'edit-cssmenu') {
    print "<div class='error'>";
    print "<h3>Beta Testing</h3>";
    print "<p>This plugin is currently in beta testing. Please take the time to <a style='text-decoration: underline;' href='http://cssmenumaker.com/wordpress-plugin-support'>let us know</a> if you run into any issues or have any questions.</p>";
    print "</div>";    
  }
}


add_action('admin_notices', 'demo_notice' );
function demo_notice() {
  $screen = get_current_screen();
  if($screen->id == 'edit-cssmenu' && demo_check()) {
    print "<div class='error'>";
    print "<h3>Demo</h3>";
    print "<p>You are currently using the demo version of MenuMaker. You will be able to create and customize menus, but displaying them in your theme is disabled. Please <a style='text-decoration: underline;' href='http://cssmenumaker.com/wordpress-plugin-support'>purchase a key</a> to unlock the full version.</p>";
    print "</div>";
  }
}
*/

/* 
 * Returns true if software should be in demo mode
 */
function demo_check() {
  return TRIAL;
}







?>