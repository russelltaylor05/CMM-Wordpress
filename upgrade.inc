
<?php
  //update_option("cssmenumaker_premium_access", 0);
  $needle = "f74ataRH";
  $access_granted =  get_option("cssmenumaker_premium_access");
  $user_code = (isset($_POST['activation_code'])) ? $_POST['activation_code'] : "";
  $step = (isset($_POST['step'])) ? 1 : 0;
  $error_code = 0;
  
  if ($step) {
    if($user_code == $needle) {
      update_option("cssmenumaker_premium_access", 1);
      $access_granted = 1;
    } else {
      $error_code = 1;
    }    
  }
?>

<div id="upgrade-page">

  <?php if(!$access_granted) { ?>
  <h1>Activate Plugin</h1>
  <p>If you have purchased an upgrade, please enter your activation code below:</p>
  <form action="" method="post">
    <input type="text" id="activation-code" name="activation_code"  value="<?php print $user_code; ?>"/>
    <input type="hidden" name="step" value="1">
    <input class="button-primary" type="submit" value="Activate" />
  </form>
  <?php if($error_code == 1) { ?>
    <p class='error'>The activation code you have entered is incorrect. Please try again or <a target='_blank' href='http://cssmenumaker.com/support'>contact us</a> if you are having problems.</p>
  <?php } ?>
  </br></br>
  
  <!-- <h1>Upgrade to MenuMaker Pro</h1>
  <p>In order to access all of our premium templates and features you need to purchase an upgrade. The upgraded MenuMaker Wordpress Plugin give you full access to the plugin and all of its professionally design menu templates.</p>
  <p><a href="http://cssmenumaker.com/wordpress-menu-plugin" target="_blank" class="button-primary">Upgrade Now</a></p>
  </br></br> -->

  <h1>Why Upgrade?</h1>
  <ul>
    <li>Access to all 100+ premium menu templates</li>
    <li>Excellent support from our dedicated designers</li>
    <li>Tested to work on iPhone, iPad, iPod, and Android devices</li>
    <li>Integrates with your existing Wordpress menu system</li>
    <li>Support for horizontal and vertical menus</li>
    <li>Support for up to 3 levels of submenus</li>
    <li>Use pure CSS or jQuery enhancements</li>
    <li>Menus integrate with Wordpress widget system</li>
    <li>Wordpress shortcodes supported</li>
    <li>Every menu theme is easily customizable</li>
    <li>Easily change colors, menu width, font sizes, alignments, and text options</li>
  </ul>
  <p><a href="http://cssmenumaker.com/wordpress-menu-plugin" target="_blank" class="button-primary">Learn More</a></p>
  
  <?php } else {  ?>    
    <h1>Thank you for purchasing MenuMaker Wordpress</h2>
  <?php } ?>
</div>