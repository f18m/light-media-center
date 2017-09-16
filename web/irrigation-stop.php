<?php 

  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);

  include 'inc/page-header.php';

  if ( $is_authorized )
  {

?>

    <p>Micro-irrigation stop:</p>

	<tt>
<?php 
		$command = escapeshellcmd('/opt/microirrigation-control/bin/lime2_valve_stop.py');
		$output = shell_exec($command);
		echo $output;

?>
    </tt>

<?php 
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

