<?php 

    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);

    include 'inc/page-utils.php';
    include 'inc/page-header.php';
    
    
    // configuration

    $folder = "/media/extdiscTORRENTS/torrents";

    if ( $is_authorized )
    {
        exec('dumptorrent ' . $folder . '/*.torrent', $dump);
        
        $dump = implode("\n", $dump);

?>

  <strong>Torrent dump</strong> of <?php echo $folder; ?>:<br/>
  <textarea class="system_status" cols="100" readonly><?php echo $dump; ?></textarea>
  
<?php 
    }

    include 'inc/link-to-home.php';
    include 'inc/page-footer.php';
?>

