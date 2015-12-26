<?php 

  include 'inc/page-header.php';

  if ( $is_authorized )
  {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);

    exec('sudo /etc/init.d/samba restart'); 
?>

    <p>BeagleTorrent SAMBA server just restarted.</p>

<?php 
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

