<?php 

  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);

  include 'inc/page-header.php';

  if ( $is_authorized )
  {

  /*
     service command is specifically for Ubuntu... 
      do not use   exec('sudo service minidlna force-reload'); 

      /etc/init.d/ is more cross-platform & Debian compatible:
  */

  // IMPORTANT: make sure that the www-data user is enabled to elevate to root permissions;
  //            this is very unsecure but it is quick to setup; in /etc/sudoers write:
  //                  sudo visudo
  //                  www-data ALL=(ALL) NOPASSWD: ALL
  exec('sudo /etc/init.d/minidlna force-reload');


?>

    <p>Mini DLNA server is rescanning contents. Please wait.</p>

<?php 
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

