<?php 

  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);

  include 'inc/page-header.php';

  if ( $is_authorized )
  {
    exec('sudo /etc/init.d/minidlna stop ; minidlna -R'); 
?>
    <p>Mini DLNA server is rescanning contents. Please wait.</p>
<?php 
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

