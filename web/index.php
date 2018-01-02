<?php 
  include 'inc/page-utils.php';
  
  if ( $is_authorized )
  {
  
    // eventually get a PHP action to perform:
    // IMPORTANT: actions can be passed only by means of POST variables to avoid double-performing an action by mistake!
    // see  https://en.wikipedia.org/wiki/Post/Redirect/Get    to understand the scheme we implement!
    if (isset($_POST["php_action"]))
      $action = $_POST["php_action"];
    else
      $action = "none";
      
    // eventually get a PHP action status code to show:
    // IMPORTANT: action status code can be passed only by means of GET variables
    if (isset($_GET["php_action_status"]))
      $action_status = $_GET["php_action_status"];
    else
      $action_status = "none";
      
      
      
    // PHP action handling:
    
    if (strcasecmp($action, "none") == 0 && 
         strcasecmp($action_status, "none") == 0)
    {
    
       include 'inc/page-header.php';
?> 
      <div class="one-third column">
          <?php write_link("check-system.php", "Check System Status"); ?>
          <p>Check if everything is running fine</p>
      </div>      
      <div class="one-third column">
          <?php write_link("/webui-aria2", "Aria2 Interface"); ?>
          <p>Access Aria2 torrent web interface</p>
      </div>
<?php 
    if ($ENABLE_MLDONKEY)   { 
?>
      <div class="one-third column">
          <?php write_link("http://frm-bt.no-ip.org:4080", "MLdonkey Interface"); ?>
          <p>Access eMule web interface</p>
      </div>  
<?php 
    }
?>   
      <div class="one-third column">
          <?php write_link("/extdiscMAIN", "Downloaded Contents"); ?>
          <p>Access MAIN disc partition contents via web interface</p>
      </div>
      <div class="one-third column">
          <?php write_link("/extdiscTORRENTS", "In-download Contents"); ?>
          <p>Access TORRENTS disc partition contents via web interface</p>
      </div>
      <div class="one-third column">
          <?php write_link("move-downloaded.php", "Move Downloaded Contents"); ?>
          <p>Move downloaded contents to the &quot;to_order&quot; folder</p>
      </div>
      <div class="one-third column">
          <?php write_link("torrent-dump.php", "Show Past Torrents"); ?>
          <p>Get a list of in-download and past torrent contents</p>
      </div>
      <div class="one-third column">
          <?php write_link("force-check.php", "Force HDD Check"); ?>
          <p>Force a check of external disc(s)</p>
      </div>
      <div class="one-third column">
          <?php write_link("dlna-rescan.php", "Rescan DLNA Database"); ?>
          <p>Force MiniDLNA restart</p>
      </div>
<?php 
    if ($ENABLE_DOMOTIC_PANEL)   { 
?>
	    <!-- this is useful if you have some sort of domotic system: -->
      <div class="one-third column">
          <?php write_link("irrigation-start.php", "Domotic System Panel"); ?>
          <p>Start/Stop domotic appliances</p>
      </div>
<?php 
    }
?>      
      <!-- for some actions, it is important to avoid having a dedicated page otherwise if the user
           reloads that page, it will trigger again an action that is often not wanted.
           E.g., if you open the reboot page, a reboot will be issued; by clicking the browser "refresh" button another
           reboot would be issued!!
      -->
      
      <div class="one-third column">
          <?php write_php_action_with_confirm("do_shutdown", "Shutdown", "Are you really sure to shutdown the " . $PORTAL_NAME . "?"); ?>
      </div>
      <div class="one-third column">
          <?php write_php_action_with_confirm("do_reboot", "Reboot", "Are you really sure to reboot the " . $PORTAL_NAME . "?"); ?>
      </div>
<?php

      include 'inc/page-footer.php';
 
    }
    else if (strcasecmp($action, "do_reboot") == 0)
    {
        // IMPORTANT: our shutdown script takes a while to execute... call it asynchronous to allow the webserver to end serving the page to the browser
        // IMPORTANT2: to be really asynchronous, the redirection of stdout/stderr are very important!
        exec('sudo /usr/local/bin/btsafe_shutdown_services.sh reboot >/dev/null 2>&1 &');

        // Redirect to this page; see https://en.wikipedia.org/wiki/Post/Redirect/Get for the reason why we do this
        header("Location: " . $_SERVER['REQUEST_URI'] . "&php_action_status=reboot_success");
        exit();
    }
    else if (strcasecmp($action, "do_shutdown") == 0)
    {
        // IMPORTANT: our shutdown script takes a while to execute... call it asynchronous to allow the webserver to end serving the page to the browser
        exec('sudo /usr/local/bin/btsafe_shutdown_services.sh halt &'); 
        
        // Redirect to this page; see https://en.wikipedia.org/wiki/Post/Redirect/Get for the reason why we do this
        header("Location: " . $_SERVER['REQUEST_URI'] . "&php_action_status=halt_success");
        exit();
    }
    
    
    // PHP action statuses 
        
    if (strcasecmp($action_status, "reboot_success") == 0)
    {
        include 'inc/page-header.php';    
        echo "<p>$PORTAL_NAME is rebooting... it will take about 30sec to return online. Please wait.</p>";
        write_auto_refresh_after_sec(4000);   // this autorefresh will remove the action_status GET variable so we show that only once
        include 'inc/page-footer.php';
    }
    else if (strcasecmp($action_status, "halt_success") == 0)
    {
        include 'inc/page-header.php';    
        echo "<p>$PORTAL_NAME is now shutting down... Please wait until LED turns off before detaching the external disc(s).</p>";
        write_auto_refresh_after_sec(4000);   // this autorefresh will remove the action_status GET variable so we show that only once
        include 'inc/page-footer.php';
    }
  }

?>

