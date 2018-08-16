<?php 

  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);

  include 'inc/page-utils.php';
  include 'inc/page-header.php';

  //if ( $is_authorized )
  {
    $in_download_directory = '/media/extdisc/.in-download/torrents/';
    $downloaded_directory = '/media/extdisc/to_reorder/';
    /*
    foreach ($DOWNLOADED_CONTENTS_EXT as $value) {
      exec('mv ' . $in_download_directory . '*.' . $value . ' ' . $downloaded_directory);
    }*/

    echo "<p>Moving all files of types: <strong>" . implode(", ", $DOWNLOADED_CONTENTS_EXT) . "</strong> from the in-download folder to the downloaded folder.</p>\n";
    echo "<ul>\n"; 
    
    //$scanned_directory = array_diff(scandir($in_download_directory), array('..', '.'));
    $scanned_directory = scandir($in_download_directory);
    $moved=0;
    foreach ($scanned_directory as $value) {
      $entry = $in_download_directory . $value;
      if ($value === '.' || $value === '..') {
        // just skip these
        continue;
      }
      else if (is_dir( $entry )) {
    
        // move all directories
        
        echo "<li>Directory: " . $entry . "</li>\n"; 
        
        $dir_name_only = basename( $entry );
        rename($entry, $downloaded_directory . $dir_name_only);
        $moved++;
      }
      else if (is_file( $entry )) {

        // move files matching some pre-defined extensions
        
        $file_ext = pathinfo($entry, PATHINFO_EXTENSION);
        if (in_array($file_ext, $DOWNLOADED_CONTENTS_EXT)) {

          echo "<li>File: " . $entry . "</li>\n"; 
          $file_name_only = basename( $entry );
          rename($entry, $downloaded_directory . $file_name_only);
          $moved++;
        }
      }
      else
      {
        print("skipping $entry\n");
      }
    }
    
    echo "</ul>\n";
    
    
    if ($moved == 0) {
      echo "<p>No files/directories to move were found.</p>";
    }
     
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

