<?php

add_action( "init", "cssmenumaker_create_menu_post_type");
function cssmenumaker_create_menu_post_type() 
{
  $labels = array(
    'name'               => 'MenuMaker',
    'singular_name'      => 'MenuMaker',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Menu',
    'edit_item'          => 'Edit Menu',
    'new_item'           => 'New Menu',
    'all_items'          => 'All Menus',
    'view_item'          => 'View Menu',
    'search_items'       => 'Search Menus',
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
    'rewrite'            => array('slug' => 'cssmenu'),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => false,
    'supports'           => array( 'title')
  );

  register_post_type('cssmenu', $args);
}


add_action('admin_init', 'cssmenumaker_admin_init');
function cssmenumaker_admin_init()
{  
  wp_enqueue_script("cssmenu-builder-structure", plugins_url().'/cssmenumaker/scripts/structure.js');    
  wp_enqueue_script("cssmenu-builder", plugins_url().'/cssmenumaker/scripts/builder.js');
  wp_enqueue_script("cssmenu-builder-less", plugins_url().'/cssmenumaker/scripts/less.js');
  wp_enqueue_script("cssmenu-builder-spectrum", plugins_url().'/cssmenumaker/scripts/spectrum/spectrum.js'); 
  wp_enqueue_script("cssmenu-builder-magnific", plugins_url().'/cssmenumaker/scripts/magnific/jquery.magnific-popup.min.js'); 

  $data_array = array( 'plugin_url' => plugins_url(), 'root_url' => get_site_url());
  wp_localize_script( 'cssmenu-builder-structure', 'cssmenu_global', $data_array);

  wp_enqueue_style('cssmenumaker-base-styles', plugins_url().'/cssmenumaker/css/menu_styles.css');  
  wp_enqueue_style('cssmenumaker-spectrum', plugins_url().'/cssmenumaker/scripts/spectrum/spectrum.css');    
  wp_enqueue_style('cssmenumaker-builder-styles', plugins_url().'/cssmenumaker/css/styles.css');   
  wp_enqueue_style('cssmenumaker-magnific-css', plugins_url().'/cssmenumaker/scripts/magnific/magnific-popup.css');     
  
  add_meta_box('cssmenumaker_menu_options', 'Menu Options','cssmenumaker_admin_menu_options','cssmenu', 'normal', 'high' );
  add_meta_box('cssmenumaker_preview', 'Preview', 'cssmenumaker_admin_menu_preview', 'cssmenu', 'normal' );
  add_meta_box('cssmenumaker_menu_database', 'Menu Database Saves','cssmenumaker_admin_menu_database','cssmenu', 'normal');   
  
  
  add_option('cssmenumaker_premium_access', 0);
}




function cssmenumaker_admin_menu_preview($cssmenu)
{
  $cssmenu_structure = get_post_meta( $cssmenu->ID, 'cssmenu_structure', true );  
  $theme_id = esc_html( get_post_meta( $cssmenu->ID, 'cssmenu_theme_id', true));  
  if($cssmenu_structure) {
    print "<div id='menu-code'></div>";    
    wp_nav_menu(array('cssmenumaker_id' => $cssmenu->ID, 'cssmenumaker_flag' => true));
    if (!trial_vis_check($theme_id)) {
      print "<p class='preview-trial-msg'>The premium theme you have choosen requires and upgrade. Please choose one of our free themes or <a href='http://cssmenumaker.com/wordpress-menu-plugin' target='_blank'>Upgrade</a> to gain full access.</p>";
    }
  }
}





/* Display Menu Options */
function cssmenumaker_admin_menu_options($cssmenu) 
{    
  global $starter_themes;
  $cssmenu_structure = esc_html( get_post_meta( $cssmenu->ID, 'cssmenu_structure', true));
  $cssmenu_location = esc_html( get_post_meta( $cssmenu->ID, 'cssmenu_location', true));
  $cssmenu_theme_id = esc_html( get_post_meta( $cssmenu->ID, 'cssmenu_theme_id', true));  
  $cssmenu_step = esc_html(get_post_meta( $cssmenu->ID, 'cssmenu_step', true));    
  $wordpress_menus = get_terms('nav_menu', array( 'hide_empty' => true ) );
  $themeMenus = json_decode(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR."menus/theme_select.json"));  
  if(!$cssmenu_step) {
    $cssmenu_step = 1;
  }
  
  /* No Wordpress Menus Present */
  if(empty($wordpress_menus)) {
    print "<div id='no-wordpress-menus-msg'>";
    print "<p>Before you can start using the MenuMaker plugin you must first create a menu structure using the Wordpress Menu System.</p>";
    print "<p>To create a Wordpress Menu, follow the instructions below:</p>";
    print "<ol>";
    print "<li>Navigate to <strong>Appearance > Menus</strong></li>";
    print "<li>Enter a Menu Name.</li>";
    print "<li>Use the left sidebar to add Pages and Posts to the menu structure.</li>";
    print "<li>Click <strong>Create Menu</strong></li>";
    print "</ol>";  
    print "<p>Once you are finished, come back here and you will be able to customize and build your menu.</p>";
    print "</div>";    
    return;
  }
 
  $classes = ($cssmenu_step == 2) ? "step-2" : "step-1";
  print "<div id='options-display' class='".$classes."'>";  
  print "<ul id='option-toggle' class='clearfix'><li><a href='#theme' class='active'>Menu Options</a></li><li><a href='#menu'>Display Options</a></li></ul>";
  print "<div id='menu-options' class='option-pane clearfix'>";
  
  /* Menu Placement Display Options */
  print "<div class='panel location'>";
  print '<h4>Menu Location</h4>';
  print "<p class='help'>Select a theme location to display your menu.</p>";  
  print '<select name="cssmenu_location">';  
  print "<option>< blank ></option>";
  $registerd_locations = get_registered_nav_menus();  
  foreach ($registerd_locations as $id => $location) {
    print '<option value="'.$id.'"';
    print selected( $id, $cssmenu_location ).'>';
    print $location;
    print "</option>";
  }
  print " </select>";  
  print "</div><!-- .panel -->";  
  print "<div class='panel widget'>";
  print '<h4>Widgets</h4>';
  print "<p class='help'>Use the MenuMaker widget to display your menu in a widget region.</p>";  
  print "<a href='/wp-admin/widgets.php'>Go to Widgets</a>";
  print "</div><!-- .panel -->";  
  print "<div class='panel shortcode'>";
  print '<h4>Shortcodes</h4>';
  print "<p class='help'>Print your menu inside another post with this shortcode.</p>";  
  print "<p><input type='text' value='[cssmenumaker id=\"".$cssmenu->ID."\"]' /></p>";  
  print "</div><!-- .panel -->";  
  print "<div class='panel php'>";
  print '<h4>PHP</h4>';
  print "<p class='help'>Use PHP to display your menu in a theme file.</p>";  
  print "<p><input type='text' value='<?php print cssmenumaker_print_menu(".$cssmenu->ID."); ?>' /></p>";  
  print "</div><!-- .panel -->";
  print "</div><!-- #menu-options -->";
  

  print "<div id='theme-options' class='option-pane clearfix'>";

  /* Structure Select */
  if($cssmenu_step == 1) {
    print "<div class='panel structure'>";
    print '<h4>Structure</h4>';
    print "<p class='help'>Select a Wordpress menu you would like to customize with MenuMaker.</p>";
    print '<select name="cssmenu_structure">';
    foreach($wordpress_menus as $id => $menu) {
      print '<option value="'.$menu->slug.'"';
      print selected( $menu->slug, $cssmenu_structure ).'>';
      print $menu->name;
      print "</option>";
    }
    print " </select>";
    print "</div><!-- .panel -->";  
  }

  /* Theme Select */
  $classes = (TM) ? "trial-mode" : "";
  $classes .= (get_option('cssmenumaker_premium_access')) ? " activated" : " not-activated";

  print "<div class='panel {$classes}' id='theme-select-panel'>";
  if(TM && !get_option('cssmenumaker_premium_access') & $cssmenu_step == 1) { /* Display limited menus */

    print '<h4>Select a Menu Style</h4>';  
    print "<input type='hidden' name='cssmenu_theme_id' value='".$cssmenu_theme_id."' />";
    print "<ul id='trial-themes'>";
    print "<li><a href='#' class='trial-theme-select drop-down' data-id='301' class=''>Drop Down</a></li>";
    print "<li><a href='#' class='trial-theme-select flyout' data-id='249' class=''>Flyout</a></li>";    
    print "<li><a href='#' class='trial-theme-select accordion' data-id='314' class=''>Accordion</a></li>";
    print "</ul'>";
    
  } else { /* Display all menu themes */
    
    print '<h4>Change Theme</h4>';  
    print "<input type='hidden' name='cssmenu_theme_id' value='".$cssmenu_theme_id."' />";
    print '<a href="#theme-select-overlay" id="theme-select-trigger" class="theme-trigger clearfix"><span>';
    foreach($themeMenus as $theme) {
      if($theme->id == $cssmenu_theme_id) {
        print "<img src='".plugins_url()."/cssmenumaker/menus/".$theme->thumbpath."' />";
      }
    }
    print '</span><div class="cssmenu-arrow"></div></a>';
    print "<a href='#theme-select-overlay' id='theme-select-trigger' class='theme-trigger-initial'>Select a Theme</a>";        
  }
  print "</div><!-- .panel -->";  
  

  if($cssmenu_step == 2) {
    require(dirname(__FILE__).DIRECTORY_SEPARATOR.'builder_settings.php');
  } 
  print "</div><!-- #theme-options -->";

  print "<input type='hidden' name='cssmenu_step' value='".$cssmenu_step."' /><br>";  

  if($cssmenu_step == 2) {
    print '<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Save Menu" accesskey="p">';
  }  else {
    print '<input type="submit" name="publish" id="publish" class="button step-1 button-primary button-large" value="Next" accesskey="p">';    
  }
  print "</div><!-- #options-display -->";  
   
  /* Theme Select Overlay */
  if(TM){
    $classes = (get_option('cssmenumaker_premium_access')) ? "activated" : "trial" ;  
  } else {
    $classes = "";
  }  
  print "<div id='theme-select-overlay' class='{$classes} white-popup mfp-hide'><div class='container clearfix'>";
  print "<div id='filters'>";
  print "<h4>Menu Themes</h4>";  
  print "<ul class='main-cats cats'>";
  print "<li><a href='#' class='drop-down'>Drop Down</a></li>";
  print "<li><a href='#' class='flyout'>Flyout</a></li>";
  print "<li><a href='#' class='horizontal'>Horizontal</a></li>";
  print "<li><a href='#' class='vertical'>Vertical</a></li>";
  print "<li><a href='#' class='tabbed'>Tabbed</a></li>";  
  print "</ul>";
  print "<ul class='sub-cats cats'>";
  print "<li><a href='#' class='responsive'>Responsive</a></li>";
  print "<li><a href='#' class='accordion'>Accordion</a></li>";
  print "<li><a href='#' class='jquery'>jQuery</a></li>";
  print "<li><a href='#' class='pure-css'>Pure CSS</a></li>";
  print "</ul>";
  print "</div>";
  
  if (TM && !get_option('cssmenumaker_premium_access')) {
    print "<div id='theme-select-trial-msg'>";
    print "<h2>Upgrade Now and get 100+ Professional Menu Themes</h2>";
    print "<ul>";
    print "<li>Endless customization options.</li>";
    print "<li>Quality code tested on all major browsers.</li>";
    print "<li>Use jQuery or pure CSS.</li>";
    print "<li>Built for mobile and tablets (iPad/iPhone/Android).</li>";    
    print "</ul>";
    print "<a href='http://cssmenumaker.com/wordpress-menu-plugin' class='button step-1 button-primary button-large' target='_blank'>Upgrade Now</a>";
    print "</div>";
  }  
  
  print "<ul id='theme-thumbs'>";
  $li_output = "";
  foreach($themeMenus as $id => $menu) {
    $data = "data-id='".$menu->id."' ";
    $classes = "";
    foreach($menu->terms as $term) {
      $classes .= "{$term} ";
    }    
    $li_output .= "<li class='{$classes}'><a href='#' {$data}><div class='banner'></div><img src='".plugins_url()."/cssmenumaker/menus/".$menu->thumbpath."' /></a></li>";  

  }
  print $li_output;
  print "</ul>";
  print "</div></div><!-- /#theme-overlay -->";  
  
}

/* Display Menu CSS */
function cssmenumaker_admin_menu_database($cssmenu) 
{  
  $cssmenu_css = esc_html(get_post_meta( $cssmenu->ID, 'cssmenu_css', true ) );
  $cssmenu_js = esc_html(get_post_meta( $cssmenu->ID, 'cssmenu_js', true ) );
  $cssmenu_settings = esc_html(get_post_meta( $cssmenu->ID, 'cssmenu_settings', true ) );
  $cssmenu_custom_css = esc_html(get_post_meta( $cssmenu->ID, 'cssmenu_custom_css', true ) );  

  
  print "<label>CSS</label>";
  print '<textarea name="cssmenu_css" id="cssmenu_css">'.$cssmenu_css."</textarea>";
  print "<label>JS</label>";
  print '<textarea name="cssmenu_js" id="cssmenu_js">'.$cssmenu_js."</textarea>";
  print "<label>Settings</label>";
  print '<textarea name="cssmenu_settings" id="cssmenu_settings">'.$cssmenu_settings."</textarea>";  
  print "<div id='custom-css-overlay'><div>";
  print "<h2>Custom CSS</h2>";
  print "<p>If you would like to create your own CSS styles for this menu, this is the place to do it. The ID for this menu is <span class='code'>#cssmenu-{$cssmenu->ID}</span>. Use this ID in your code below to apply custom CSS styles.</p>";
  print '<textarea name="cssmenu_custom_css" id="cssmenu_custom_css">'.$cssmenu_custom_css."</textarea>";  
  print "<a href='#' class='button-primary submit'>Save</a>";
  print "<a href='#' class='cancel'>Cancel</a>";  
  print "</div></div>";

  
}


/* Save Menu Options */
add_action('save_post','cssmenumaker_post_save', 10, 2 );
function cssmenumaker_post_save($cssmenu_id, $cssmenu ) 
{
  if ( $cssmenu->post_type == 'cssmenu' ) {    

    if (isset( $_POST['cssmenu_structure'] ) && $_POST['cssmenu_structure'] != '' ) {
      update_post_meta($cssmenu_id, 'cssmenu_structure', $_POST['cssmenu_structure'] );
    }
    if (isset( $_POST['cssmenu_location'] ) && $_POST['cssmenu_location'] != '' ) {
      update_post_meta($cssmenu_id, 'cssmenu_location', $_POST['cssmenu_location'] );
    }
    if (isset( $_POST['cssmenu_css'])) {
      update_post_meta($cssmenu_id, 'cssmenu_css', $_POST['cssmenu_css'] );
    }    
    if (isset( $_POST['cssmenu_js'])) {
      update_post_meta($cssmenu_id, 'cssmenu_js', $_POST['cssmenu_js'] );
    }
    if (isset( $_POST['cssmenu_settings'])) {
      update_post_meta($cssmenu_id, 'cssmenu_settings', $_POST['cssmenu_settings'] );
    }
    if (isset($_POST['cssmenu_theme_id'])) {
      update_post_meta($cssmenu_id, 'cssmenu_theme_id', $_POST['cssmenu_theme_id'] );
    }
    if (isset($_POST['cssmenu_custom_css'])) {
      update_post_meta($cssmenu_id, 'cssmenu_custom_css', $_POST['cssmenu_custom_css'] );
    }
    if (isset($_POST['cssmenu_step'])) {
      update_post_meta($cssmenu_id, 'cssmenu_step', 2 );
    }


  }
}




add_filter( 'template_include','cssmenumaker_template_include', 1 );
function cssmenumaker_template_include ($template_path) 
{
  if (get_post_type() == 'cssmenu') {
    if (is_single()) {
      // checks if the file exists in the theme first,
      // otherwise serve the file from the plugin
      if ($theme_file = locate_template(array('single-cssmenu.php'))) {
        $template_path = $theme_file;
      } else {
        $template_path = plugin_dir_path( __FILE__ ).'/single-cssmenu.php';
      }
    }
  }
  return $template_path;
}




/* 
 * Help Page
 */
add_action('admin_menu', 'cssmenumake_menu_help');
function cssmenumake_menu_help()  {
	add_submenu_page('edit.php?post_type=cssmenu', 
                    'CSS MenuMaker Help', 
                    'Help', 'manage_options', 'cssmenu-help', 
                    'cssmenumaker_help_page');
}
function cssmenumaker_help_page()  {
  require(dirname(__FILE__).DIRECTORY_SEPARATOR.'help.inc');
}


add_action('admin_menu', 'cssmenumake_menu_upgrade');
function cssmenumake_menu_upgrade()  {
  if(TM) {
	  add_submenu_page('edit.php?post_type=cssmenu', 
                      'MenuMaker Upgrade', 
                      'Upgrade', 'manage_options', 'cssmenu-upgrade', 
                      'cssmenumaker_upgrade_page');
  }
}
function cssmenumaker_upgrade_page()  {
  require(dirname(__FILE__).DIRECTORY_SEPARATOR.'upgrade.inc');
}







?>