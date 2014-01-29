<?php

  header('Content-Type: application/javascript');

  $selected_menu = $_GET['selected'];
  $cssmenu_js = get_post_meta($selected_menu, "cssmenu_js", true);
  $cssmenu_js = preg_replace("/#cssmenu/si", "#cssmenu-{$selected_menu}", $cssmenu_js);

  print "(function ($) {\n";
    if(!strpos($cssmenu_js,"$(document).ready(function(){"))   {
    print "$(document).ready(function(){\n";
  }
  
  print $cssmenu_js;

  if(!strpos($cssmenu_js,"$(document).ready(function(){"))   {
    print "\n});";
  }
  print "\n}(jQuery));";
  
?>