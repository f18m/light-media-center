<?php
  $VERSION = "1.5";
  $PORTAL_NAME = "Light Media Center";

  $is_authorized = False;
  
  // get encrypted user name either from GET vars (those encoded in the URL) or from POST
  if (isset($_GET["user"]))
      $enc_user = $_GET["user"];
  else if (isset($_POST["user"]))
      $enc_user = $_POST["user"];
  
  if (isset($enc_user))
  {
     // user names are "encrypted" with rot13
     $username = str_rot13( $enc_user );
     //echo "Username is $username";
     
     if(!@include("inc/allowed-user.php"))
     {
         // no list of authorized users available... everybody is authorized
         $is_authorized = True;
     }
     else if (strcasecmp($username, "francescom") == 0)
     {
         $is_authorized = True;
     }
  }  


  function write_link($file, $title)
  {
     global $is_authorized;
     global $enc_user;

     /*if ( $is_authorized )
        $url = $file . "?user=" . $enc_user;
     else
        $url = $file;*/
     //echo "<a href=\"$url\">$title</a>";
     //echo "<button onclick=\"location.href='$url';\">$title</button>";
     
     
     // every link is implemented as a small form passing via POST variable
     // the "user" variable to another PHP page
     
     echo "<form action='$file' method='post'>";
     echo "<input type='hidden' name='user' value='" . $enc_user . "' />"; 
     echo "<input type='submit' value='$title' />";
     echo "</form>";   
  }

/*
  function write_js_action($jscode, $title)
  {
     //echo "<a href=\"$url\">$title</a>";
     echo "<button onclick=\"$jscode\">$title</button>";
  }
  */
  
  function write_php_action($php_action_value, $title)
  {
     global $is_authorized;
     global $enc_user;

     if ( $is_authorized )
        $url = "index.php?user=" . $enc_user;
     
     // every PHP action is implemented as a small form passing via POST variable
     // the "user" and "php_action" variables to the root index.php page 
     // NOTE: the "user" is passed also as GET variable to be sure
     
     echo "<form action='$url' method='post'>";
     echo "<input type='hidden' name='php_action' value='$php_action_value' />";
     echo "<input type='hidden' name='user' value='" . $enc_user . "' />"; 
     echo "<input type='submit' value='$title' />";
     echo "</form>";   
  }
  
  
  function write_php_action_with_confirm($php_action_value, $title, $confirm_text)
  {
     global $is_authorized;
     global $enc_user;

     if ( $is_authorized )
        $url = "index.php?user=" . $enc_user;
     
     // every PHP action is implemented as a small form passing via POST variable
     // the "user" and "php_action" variables to the root index.php page 
     // NOTE: the "user" is passed also as GET variable to be sure
     
     echo "<form action='$url' method='post' onsubmit=\"return confirm('$confirm_text');\"   />";
     echo "<input type='hidden' name='php_action' value='$php_action_value' />";
     echo "<input type='hidden' name='user' value='" . $enc_user . "' />"; 
     echo "<input type='submit' value='$title' />";
     echo "</form>";   
  }
  
  function write_auto_refresh_after_sec($nmillisec)
  {
     //echo "<p onload='setTimeout(auto_closewindow, $nsec);' />";
     echo "<script type='text/javascript'>window.onload = function() { setTimeout(auto_refreshwindow, $nmillisec); }</script>";
  }
  
  function write_auto_refresh_to_reset_POST_vars()
  {
     //echo "<p onload='setTimeout(auto_closewindow, $nsec);' />";
     echo '<script type="text/javascript">location.reload();</script>';
  }
?>

