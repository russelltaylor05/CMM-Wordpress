<?php
  

add_action( 'widgets_init', 'cssmenumaker_create_widgets' );  
function cssmenumaker_create_widgets() {
  register_widget('CSS_MenuMaker');
}  


class CSS_MenuMaker extends WP_Widget {
  // Construction function
  function __construct ()  {  
    parent::__construct( 'cssmenu_widget', 'CSS MenuMaker', array( 'description' => 'Display a Menu built with CSS MenuMaker' ));            
  }
  
  function form($instance) {
    $selected_menu = ( !empty( $instance['selected_menu'] ) ? $instance['selected_menu'] : NULL );
    $available_menus = get_posts(array("post_type" => "cssmenu"));
    
    print "<label>Please select a Menu to display</label>";    
    print '<select name="'.$this->get_field_name( 'selected_menu').'" id="'.$this->get_field_id( 'selected_menu').'">';
    foreach($available_menus as $id => $available_menu) {
      print '<option value="'.$available_menu->ID.'"';
      print selected($available_menu->ID, $selected_menu ).'>';
      print $available_menu->post_title;
      print "</option>";
    }  
    print " </select>";
  }
  
  function widget($args, $instance) {
    
    $selected_menu = $instance['selected_menu'];
    $post = get_post($selected_menu);
    
    $wordpress_menu = get_post_meta($selected_menu, "cssmenu_structure", true);
    $menu_css = get_post_meta($selected_menu, "cssmenu_css", true);
    $menu_js = get_post_meta($selected_menu, "cssmenu_js", true);    

    wp_nav_menu(array(
      'menu' => $wordpress_menu,
      'container_id' => "cssmenu-{$selected_menu}", 
      'container_class' => 'cssmenumaker-menu',
      'walker' => new CSS_Menu_Maker_Walker(),
      'menu_class' => '',
      'menu_id' => '',      
    ));
   
    wp_enqueue_style('cssmenumaker-base-styles', plugins_url().'/cssmenumaker/css/menu_styles.css');
    wp_enqueue_style("dynamic-css-{$selected_menu}", admin_url('admin-ajax.php')."?action=dynamic_css&selected={$selected_menu}");
    if($menu_js) {
      wp_enqueue_script("dynamic-script-{$selected_menu}", admin_url('admin-ajax.php')."?action=dynamic_script&selected={$selected_menu}");    
    }
    
  }  
}


  
?>