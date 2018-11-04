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
    // Execute the real command via websocket operations; see lime2node.js. In this way we can immediately return to the client
    // a complete webpage. We will then read the log file produced by the PHP utility and update via a WebSocket this page in realtime.

    // simply create a DIV that will be updated through a WebSocket by our Javascript code:
?>    
    <p>Micro-irrigation system control:</p>
    
    <input class="green" id="irrigationTurnOn" type="button" value="Start irrigation and stop after 5minutes" onclick="ws_send_cmd('TURNON_WITH_TIMER', '5');" />
    <input class="green" id="irrigationTurnOn" type="button" value="Start irrigation and stop after 10minutes" onclick="ws_send_cmd('TURNON_WITH_TIMER', '10');" />
    <input class="green" id="irrigationTurnOn" type="button" value="Start irrigation and stop after 15minutes" onclick="ws_send_cmd('TURNON_WITH_TIMER', '15');" />
    <div style="height: 20px"></div>
    
    <p>Manual control of each irrigation circuit:</p>
    
    <input class="green" id="irrigationTurnOn" type="button" value="Start irrigation circuit #1" onclick="ws_send_cmd('TURNON', '1');" />
    <input class="red" id="irrigationTurnOff" type="button" value="Force stop irrigation circuit #1" onclick="ws_send_cmd('TURNOFF', '1');" />
    
    <input class="green" id="irrigationTurnOn" type="button" value="Start irrigation circuit #2" onclick="ws_send_cmd('TURNON', '2');" />
    <input class="red" id="irrigationTurnOff" type="button" value="Force stop irrigation circuit #2" onclick="ws_send_cmd('TURNOFF', '2');" />
    
    <div style="height: 20px"></div>
    <p>Battery level:</p>
    <div class="progress" id="battery">
      <!-- initial battery life is zero -->
      <div class="bar" style="width: 0%;"></div>
    </div>
    
    <!--
    <input class="green" id="lightsTurnOn" type="button" value="Turn on Christmas lights" onclick="ws_send_turnon2();" />
    <input class="red" id="lightsTurnOff" type="button" value="Turn off Christmas lights" onclick="ws_send_turnoff2();" />
    -->
    
    <div style="height: 20px"></div>
    
    
    <p>Log file:</p>
    <textarea id="js_updated" class="system_status" cols="90" readonly></textarea>

<?php
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

