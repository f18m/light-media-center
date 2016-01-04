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
      
      <!--
      <div class="one-third column">
          <?php write_link("/rutorrent", "ruTorrent Interface"); ?>
          <p>Access a full-featured rTorrent web interface (not suggested for smartphones!)</p>
      </div>
      <div class="one-third column">
          <?php write_link("/wtorrent", "wTorrent Interface"); ?>
          <p>Access rTorrent web interface smartphone-friendly</p>
      </div> -->
      
      <div class="one-third column">
          <?php write_link("/webui-aria2", "Aria2 Interface"); ?>
          <p>Access Aria2 torrent web interface</p>
      </div>
      <div class="one-third column">
          <?php write_link("http://frm-bt.no-ip.org:4080", "MLdonkey Interface"); ?>
          <p>Access eMule web interface</p>
      </div>  
      <div class="one-third column">
          <?php write_link("/extdiscMAIN", "Downloaded contents"); ?>
          <p>Access MAIN disc partition contents via web interface</p>
      </div>
      <div class="one-third column">
          <?php write_link("/extdiscTORRENTS", "In-download contents"); ?>
          <p>Access TORRENTS disc partition contents via web interface</p>
      </div>
      <div class="one-third column">
          <?php write_link("torrent-dump.php", "Show Past Torrents"); ?>
          <p>Get a list of in-download and past torrent contents</p>
      </div>
      
      <!-- now that minidlna rescan is in crontab, there's no real need to run it manually: 
      <div class="one-third column">
          <?php write_link("dlna-rescan.php", "Rescan Multimedia"); ?>
          <p>You can force a mini-DLNA server rescan</p>
      </div>
      <div class="one-third column">
          <?php write_link("samba-restart.php", "Restart SAMBA"); ?>
          <p>Force a restart of the SAMBA server</p>
      </div>
      -->
      
      <div class="one-third column">
          <?php write_link("force-check.php", "Force extdisc check"); ?>
          <p>Force a check of external disc(s)</p>
      </div>
      
      <!-- apparently BeagleBone does not detect USB disks after startup... so it must be rebooted anyway: 
      <div class="one-third column">
          <?php write_link("btmain-stop.php", "Stop extdiscs"); ?>
          <p>You can unmount external disc(s) (for safe removal)</p>
      </div>
      <div class="one-third column">
          <?php write_link("btmain-restart.php", "Restart extdiscs"); ?>
          <p>Use this after reattaching of external discs</p>
      </div>
      -->
      
      <!-- rTorrent is not used anymore:    
      
      <div class="one-third column">
          <?php write_link("rtorrent-restart.php", "(Re)start rTorrent"); ?>
          <p>You can force a restart of the rTorrent server</p>
      </div>
      <div class="one-third column">
          <?php write_link("rtorrent-stop.php", "Stop rTorrent"); ?>
          <p>You can force a stop of the rTorrent server</p>
      </div>      
      <div class="one-third column">
          <?php write_link("aria2-restart.php", "(Re)start Aria2"); ?>
          <p>You can force a restart of the Aria2 server</p>
      </div>
      <div class="one-third column">
          <?php write_link("aria2-stop.php", "Stop Aria2"); ?>
          <p>You can force a stop of the Aria2 server</p>
      </div>
      <div class="one-third column">
          <?php write_link("fix-usb.php", "Force recheck USB bus"); ?>
          <p>You can force a scan of the USB bus</p>
      </div>
      -->
      
      
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

