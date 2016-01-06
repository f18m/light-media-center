<?php 

    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);

    include 'inc/page-utils.php';
    include 'inc/page-header.php';
    
    
    // configuration

    $btmain_logfile_fn = "/var/log/btmain.log";
    $aria2_logfile_fn = "/var/log/aria2.log";
    $aria2hooks_logfile_fn = "/var/log/aria2hooks.log";
    $mldonkey_logfile_fn = "/home/debian/.mldonkey/mlnet.log";
    $fail2ban_logfile_fn = "/var/log/fail2ban.log";
    $minidlna_logfile_fn = "/var/log/minidlna.log";
    $logfile_linecount = 30;
    $top_linecount = 16;
    

    // helper functions
    function parse_df_output($line)
    {
        $elements = preg_split('/\s+/',$line);
        return(array(
                'filesystem' => $elements[0],
                'capacity' => $elements[1],
                'used' => $elements[2],
                'available' => $elements[3],
                'use_percent' => $elements[4],
                'mounted_on' => $elements[5]
                ));
    }
    
    function tail_logfile($logfile_fn, $logfile_linecount)
    {
        if(!file_exists($logfile_fn))
            return "";
            
        $file = file($logfile_fn);
        $file_linecount = count($file);
        
        if ($file_linecount <= $logfile_linecount)
        {
            $logcontents = file_get_contents($logfile_fn);
        }
        else
        {
            $logcontents = "";
            for ($i = $file_linecount-$logfile_linecount; $i < $file_linecount; $i++)
                $logcontents = $logcontents . $file[$i];
        }
        
        return $logcontents;
    }
    
    function decorate_with_css_class($desc, $is_ok)
    {
        if ($is_ok)
            return "<span class='ok'>" . $desc . "</span>";
            
        return "<span class='ko'>" . $desc . "</span>";
    }

    function get_df_string_for_partition($partitionName)
    {
        $df_output = exec('df -h | grep ' . $partitionName . '$');
        if (empty($df_output))
        {
            $df_status = decorate_with_css_class("NO PARTITION " . $partitionName . " MOUNTED", false);
        }
        else
        {
            $parsed = parse_df_output($df_output);
            $df_status = $parsed['used'] . '/' . $parsed['capacity'] . ' (' . $parsed['use_percent'] . ' used)';
            $df_status = decorate_with_css_class($df_status, true);
        }
        
        return $df_status;
    }
    
    function get_status_string_for_process($procname)
    {
        $pids = array();
        exec("pgrep " . $procname, $pids);
        if (empty($pids))
            return decorate_with_css_class("NOT RUNNING", false);
        else
            return decorate_with_css_class("RUNNING with PID " . implode(", ",$pids), true);
    }

    

    if ( $is_authorized )
    {
        $status_string = array(
            "rtorrent" => get_status_string_for_process("rtorrent"),
            "aria2" => get_status_string_for_process("aria2c"),
            "minidlna" => get_status_string_for_process("minidlna"),
            "mldonkey" => get_status_string_for_process("mlnet"),
            "btmain" => get_status_string_for_process("btmain"),
            "noip2" => get_status_string_for_process("noip2"),
            "smb" => get_status_string_for_process("smb"),
            "ssh" => get_status_string_for_process("ssh"),
        );

        /*$extdiscMAIN_df = get_df_string_for_partition("extdiscMAIN");
        $extdiscMAIN2_df = get_df_string_for_partition("extdiscMAIN2");
        $extdiscMAIN3_df = get_df_string_for_partition("extdiscMAIN3");
        $extdiscTORRENTS_df = get_df_string_for_partition("extdiscTORRENTS");*/
        $extdisc_df = get_df_string_for_partition("extdisc");
        
        $btmain_logfile = tail_logfile($btmain_logfile_fn, $logfile_linecount);
        $aria2_logfile = tail_logfile($aria2_logfile_fn, $logfile_linecount);
        $aria2hooks_logfile = tail_logfile($aria2hooks_logfile_fn, $logfile_linecount);
        $mldonkey_logfile = tail_logfile($mldonkey_logfile_fn, $logfile_linecount);
        $fail2ban_logfile = tail_logfile($fail2ban_logfile_fn, $logfile_linecount);
        $minidlna_logfile = tail_logfile($minidlna_logfile_fn, $logfile_linecount);

        $uptime = exec('uptime');
        exec('uprecords -a', $uprecords);
        $uprecords = implode('<br/>', $uprecords);

        $tz = 'Europe/Rome';
        $clock = exec('TZ=' . $tz . ' date');
        
        
        exec('pstree', $pstree);
        $pstree = implode("\n", $pstree);
        
        /*
        exec('top -b -n 2', $top_arr);
        // $top_ = implode("\n", $top_arr);
        $top_lc = count($top_arr);
        
        if ($top_lc > $top_linecount)
        {
            $top = "";
            for ($i = 0; $i < $top_linecount; $i++)
                $top = $top . $top_arr[$i] . "\n";
        }*/
        
        
        exec('dmesg | tail -n 50', $dmesg_logfile);
        $dmesg_logfile = implode("\n", $dmesg_logfile);
        
?>

    <p style='text-align:center'>
        Hostname:&nbsp; <strong><?php echo gethostname() ?></strong>
    </p>

    <p style='text-align:center'>
    <!--
        Free/total space on external disc MAIN partition:&nbsp; <?php echo $extdiscMAIN_df; ?><br/>
        Free/total space on external disc MAIN2 partition:&nbsp; <?php echo $extdiscMAIN2_df; ?><br/>
        Free/total space on external disc MAIN3 partition:&nbsp; <?php echo $extdiscMAIN3_df; ?><br/>
        Free/total space on external disc TORRENTS partition:&nbsp; <?php echo $extdiscTORRENTS_df; ?>
        -->
        
        Free/total space on external disc MAIN partition:&nbsp; <?php echo $extdisc_df; ?><br/>
    </p>


    <p style='text-align:center'>
         <!-- rTorrent server: <?php echo $status_string["rtorrent"]; ?> <br/> -->
         Aria2 server: <?php echo $status_string["aria2"]; ?> <br/>
         MLdonkey server: <?php echo $status_string["mldonkey"]; ?> <br/>
         miniDLNA server: <?php echo $status_string["minidlna"]; ?> <br/>
         External disc mounter: <?php echo $status_string["btmain"]; ?> <br/>
    </p>

    <p style='text-align:center'>
         SAMBA: <?php echo $status_string["smb"]; ?> <br/>
         noIP2 client: <?php echo $status_string["noip2"]; ?> <br/>
         SSH server: <?php echo $status_string["ssh"]; ?> <br/>
    </p>


  <strong>Dmesg log </strong> (last <?php echo $logfile_linecount; ?> lines):<br/>
  <textarea class="system_status" cols="90" readonly><?php echo $dmesg_logfile; ?></textarea>
  
  <strong>External disc mounter log file</strong> (last <?php echo $logfile_linecount; ?> lines):<br/>
  <textarea class="system_status" cols="90" readonly><?php echo $btmain_logfile; ?></textarea>

  <strong>Aria2 log file</strong> (last <?php echo $logfile_linecount; ?> lines):<br/>
  <textarea class="system_status" cols="90" readonly><?php echo $aria2_logfile; ?></textarea>
        
  <strong>Aria2 HOOKS log file</strong> (last <?php echo $logfile_linecount; ?> lines):<br/>
  <textarea class="system_status" cols="90" readonly><?php echo $aria2hooks_logfile; ?></textarea>
        
  <strong>MlDonkey log file</strong> (last <?php echo $logfile_linecount; ?> lines):<br/>
  <textarea class="system_status" cols="90" readonly><?php echo $mldonkey_logfile; ?></textarea>
        
  <strong>Minidlna log file</strong> (last <?php echo $logfile_linecount; ?> lines):<br/>
  <textarea class="system_status" cols="90" readonly><?php echo $minidlna_logfile; ?></textarea>
  
  <strong>Fail2ban log file</strong> (last <?php echo $logfile_linecount; ?> lines):<br/>
  <textarea class="system_status" cols="90" readonly><?php echo $fail2ban_logfile; ?></textarea>
  
  <!-- <div style="float:left"> -->
  <strong>Process tree:</strong><br/>
  <textarea class="system_status" cols="90" readonly><?php echo $pstree; ?></textarea>
  <!-- </div> -->
  
  <!-- <div style="float:right"> -->
  <!-- <strong>Top:</strong><br/>
  <textarea class="system_status" cols="90" readonly><?php echo $top; ?></textarea> -->
  <!-- </div> -->
  
    <p style='text-align:center'>
        Local clock:&nbsp; <strong><?php echo $clock . " (" . $tz . ")"; ?></strong>
    </p>

    <p style='text-align:center'>
        Uptime:&nbsp; <strong><?php echo $uptime; ?></strong>
    </p>
    <p style='text-align:center'>
        <strong>Uptime records:</strong>
    </p>
    <p style='text-align:center'>
        <tt><?php echo $uprecords; ?></tt>
    </p>

  <p>&nbsp;</p>
  
<?php 
    }

    include 'inc/link-to-home.php';
    include 'inc/page-footer.php';
?>

