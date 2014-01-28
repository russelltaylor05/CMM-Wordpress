<?php
header('Content-type: text/css');  

$selected_menu = $_GET['selected'];

$cssmenu_css = get_post_meta($selected_menu, "cssmenu_css", true);

$images[0] = get_post_meta($selected_menu, "cssmenumaker_images_0", true);
$images[1] = get_post_meta($selected_menu, "cssmenumaker_images_1", true);
$images[2] = get_post_meta($selected_menu, "cssmenumaker_images_2", true);
$images[3] = get_post_meta($selected_menu, "cssmenumaker_images_3", true);
$images[4] = get_post_meta($selected_menu, "cssmenumaker_images_4", true);

$cssmenu_css = preg_replace("/#cssmenu/si", "#cssmenu-{$selected_menu}", $cssmenu_css);

/* Replace image urls */
for($cnt = 0; $cnt < IMAGE_CNT; $cnt++) {
  if(isset($images[$cnt]['url'])) {
    $file_name = substr($images[$cnt]['file'], strrpos($images[$cnt]['file'], "/") + 1, strlen($images[$cnt]['file']));
    $cssmenu_css = preg_replace("/\('$file_name'\)/si", "('".$images[$cnt]['url']."')", $cssmenu_css);
  }
}


print $cssmenu_css;
?>
