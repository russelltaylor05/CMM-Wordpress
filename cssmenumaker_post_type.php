<?php

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
  add_meta_box('cssmenumaker_menu_js',
               'Menu jQuery',
               'cssmenumaker_admin_menu_js',
               'cssmenu', 'normal', 'high' );

  add_meta_box('cssmenumaker_images', 'Images', 'cssmenumaker_admin_menu_upload', 'cssmenu', 'normal' );                 
}


/* Display Menu Options */
function cssmenumaker_admin_menu_options( $cssmenu ) 
{  
  
  // Retrieve current author and rating based on review ID
  $cssmenu_structure = esc_html( get_post_meta( $cssmenu->ID, 'cssmenu_structure', true ) );
  $cssmenu_location = esc_html( get_post_meta( $cssmenu->ID, 'cssmenu_location', true ) );
  $wordpress_menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
  
  print '<label>Menu Structure</label>';
  print "<p class='help'>Select a Wordpress menu you would like to use as the structure.</p>";
  print '<select name="cssmenu_structure">';
  foreach($wordpress_menus as $id => $menu) {
    print '<option value="'.$menu->slug.'"';
    print selected( $menu->slug, $cssmenu_structure ).'>';
    print $menu->name;
    print "</option>";
  }  
  print " </select>";

  print '<label>Menu Location</label>';
  print "<p class='help'>Select a theme location to display your menu. Leave blank if you plan on using this menu as a Widget or Shortcode.</p>";  
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

}

/* Display Menu CSS */
function cssmenumaker_admin_menu_css( $cssmenu ) 
{  
  $cssmenu_css = esc_html(get_post_meta( $cssmenu->ID, 'cssmenu_css', true ) );
  print '<p>Copy and paste the CSS from the Online MenuMaker here:</p>';
  print '<textarea name="cssmenu_css" id="cssmenu_css">';
  print $cssmenu_css;
  print "</textarea>";
}

/* Display Menu JS */
function cssmenumaker_admin_menu_js( $cssmenu ) 
{  
  $cssmenu_js = esc_html(get_post_meta( $cssmenu->ID, 'cssmenu_js', true ) );
  print "<p>Copy and paste the jQuery from the Online MenuMaker here. If your menu doesn't use jQuery, leave this blank.</p>";
  print '<textarea name="cssmenu_js" id="cssmenu_js">';
  print $cssmenu_js;
  print "</textarea>";
}

/* Display Menu Image Upload */
function cssmenumaker_admin_menu_upload($cssmenu)
{
  $images[0] = get_post_meta($cssmenu->ID, 'cssmenumaker_images_0', true );
  $images[1] = get_post_meta($cssmenu->ID, 'cssmenumaker_images_1', true );
  $images[2] = get_post_meta($cssmenu->ID, 'cssmenumaker_images_2', true );  
  $images[3] = get_post_meta($cssmenu->ID, 'cssmenumaker_images_3', true );  
  $images[4] = get_post_meta($cssmenu->ID, 'cssmenumaker_images_4', true );      

  print "<p>If your menu uses images, upload them here.</p>";
  print "<ul id='cssmenumaker-upload-fields'>";
  for($cnt = 0; $cnt < IMAGE_CNT; $cnt++) {
    print "<li>";
    if (empty($images[$cnt])) {
      $numbered = $cnt + 1;
      print "<label>Image {$numbered}</label> <input name='cssmenumaker_images_{$cnt}' type='file' />";
    } else {
      $file_name = substr($images[$cnt]['file'], strrpos($images[$cnt]['file'], "/") + 1, strlen($images[$cnt]['file']));
      print "<a class='file-link' href='".$images[$cnt]['url']."'>$file_name</a>";
      print '<input type="submit" name="delete_image_'.$cnt.'" id="deleteattachment" value="Remove" />';    
    }
    print "</li>";
  }
  print "</ul>";
}


add_action( 'post_edit_form_tag', 'cssmenumaker_form_add_enctype' );
function cssmenumaker_form_add_enctype() {
  print 'enctype="multipart/form-data"';
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

    /* Save/Delete Images*/
    for($cnt = 0; $cnt < IMAGE_CNT; $cnt++) { 
      $delete_var = "delete_image_{$cnt}";
      $input_var = "cssmenumaker_images_{$cnt}";      
      if ( isset($_POST[$delete_var] ) ) {
        $attach_data = get_post_meta( $cssmenu_id, $input_var, true );
        if ( $attach_data != "" ) {
          unlink( $attach_data['file'] );
          delete_post_meta($cssmenu_id, $input_var);
        }
      } else {
        if( array_key_exists( $input_var, $_FILES ) && !$_FILES[$input_var]['error'] ) {        
          $file_type_array = wp_check_filetype( basename($_FILES[$input_var]['name'] ) );
          $file_type = strtolower( $file_type_array['ext']);
          if ($file_type != 'jpg' && $file_type != 'png' && $file_type != 'gif' && $file_type != 'jpeg') {
            wp_die( 'Only image files are allowd for upload.' );
            exit;
          } else {
            $upload_return = wp_upload_bits($_FILES[$input_var]['name'], null, file_get_contents($_FILES[$input_var]['tmp_name'] ) );
            $upload_return['file'] = str_replace( '\\', '/', $upload_return['file'] );          
            if ( isset( $upload_return['error'] ) && $upload_return['error'] != 0 ) {
              $errormsg = 'There was an error uploading your file. The error is: '.$upload_return['error'];
              wp_die($errormsg);
              exit;
            } else {
              $attach_data = get_post_meta($cssmenu_id, $input_var, true);
              if ($attach_data != '') {
                unlink( $attach_data['file'] );
              }
              update_post_meta($cssmenu_id, $input_var, $upload_return);
            }
          }
        }
      }
    } // end while loop   
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
function cssmenumake_menu_help() {
	add_submenu_page('edit.php?post_type=cssmenu', 
                    'CSS MenuMaker Help', 
                    'Help', 'manage_options', __FILE__, 
                    'cssmenumaker_help_page');
}
function cssmenumaker_help_page() 
{?>
  <div id="help-page">
    <h1>Plugin Help</h1>
    <p>This wordpress plugin is brand new so we are looking to gather feedback from our users. 
       If you are having problems using the Plugin, please take a moment to submit a ticket and 
       let us know what problems you are running into.</p>
       <p><a href="http://cssmenumaker.com/wordpress-plugin-support" target="_blank" class="button-primary">Submit Ticket</a></p>
  </div>
<?php }




?>