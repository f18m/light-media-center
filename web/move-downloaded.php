<?php 

  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);

  include 'inc/page-utils.php';
  include 'inc/page-header.php';

  if ( $is_authorized )
  {
    foreach ($DOWNLOADED_CONTENTS_EXT as $value) {
      exec('mv /media/extdisc/.in-download/torrents/*.' . $value . ' /media/extdisc/to_reorder/');
    }

    echo "<p>Moving all files of types: <strong>" . implode(", ", $DOWNLOADED_CONTENTS_EXT) . "</strong> from the in-download folder to the downloaded folder.</p>"; 
  }

  include 'inc/link-to-home.php';
  include 'inc/page-footer.php';
?>

