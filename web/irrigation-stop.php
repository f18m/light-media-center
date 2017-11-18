<?php 

  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);
  
  include 'inc/page-utils.php';
  
  // do link our Javascript file that will handle the WebSocket:
  $this_page_needs_websocket_updates = true;
  include 'inc/page-header.php';
 
  // main PHP library for activating the remote node for irrigation:
  include 'inc/lime2node_comm_lib.php';

  if ( $is_authorized )
  {
    echo '<p>Micro-irrigation starting:</p>';
    
    // Execute the real command via websocket operations; see lime2node.js. In this way we can immediately return to the client
    // a complete webpage. We will then read the log file produced by the PHP utility and update via a WebSocket this page in realtime.

    // simply create a DIV that will be updated through a WebSocket by our Javascript code:
    echo '<textarea id="js_updated" class="system_status" cols="90" readonly></textarea>';
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

