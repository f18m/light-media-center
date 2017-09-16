<?php 

  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);

  include 'inc/page-header.php';

  if ( $is_authorized )
  {
    echo "<p>Micro-irrigation start:</p>"
	echo "<tt>"
	$command = escapeshellcmd('/opt/microirrigation-control/bin/lime2_valve_start.py');
	$output = shell_exec($command);
	echo $output;
    echo "</tt>"
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

