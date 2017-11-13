<?php 

  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);

  include 'inc/page-utils.php';
  include 'inc/page-header.php';
  include 'inc/lime2node_over_spi.php';

  if ( $is_authorized )
  {
    echo "<p>Micro-irrigation starting:</p>";
  	echo "<p style='text-align:center; font-size: 200%; font-family: monospace;'><strong><tt>";
  	//$command = escapeshellcmd('/opt/microirrigation-control/bin/lime2_valve_ctrl.py open 2>&1');
  	
  	// NOTE: since the Python script will employ GPIO module, it will need ROOT permissions:
  	/*$command = 'sudo /opt/microirrigation-control/bin/lime2_valve_ctrl.py open 2>&1';
  	$output = shell_exec($command);
  	echo $output;*/
   
   
    init_lime2node_over_spi();
      
    $tid = get_last_transaction_id_and_advance();
    $ack_contents = array();
    if (send_spi_cmd($turnon_cmd, $tid, $ack_contents))
      wait_for_ack($tid);
	   
    echo "</tt></strong></p><br/><br/>";
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

