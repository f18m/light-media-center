<?php 
  include 'inc/page-header.php';
  include 'inc/page-utils.php';

  if ( $is_authorized )
  {

    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);

    exec('sudo /usr/local/bin/btextdiskcheck.sh', $extdisc_check);
    $extdisc_check = implode("\n", $extdisc_check);
?>


  External discs have been checked for errors:<br/>
  <textarea class="system_status" cols="90" readonly><?php echo $extdisc_check; ?></textarea>
  
<?php 
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

