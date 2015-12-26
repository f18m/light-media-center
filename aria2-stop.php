<?php 

  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);

  include 'inc/page-header.php';

  if ( $is_authorized )
  {


  // IMPORTANT: make sure that the www-data user is enabled to elevate to root permissions;
  //            this is very unsecure but it is quick to setup; in /etc/sudoers write:
  //                  sudo visudo
  //                  www-data ALL=(ALL) NOPASSWD: ALL
  exec('sudo /etc/init.d/aria2 stop');


?>

    <p>BeagleTorrent Aria2 server is stopping... Please wait.</p>

<?php 
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

