<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

	<!-- Basic Page Needs
  ================================================== -->
	<meta charset="utf-8">
	<title><?php echo $PORTAL_NAME; ?> Control Panel</title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Mobile Specific Metas
  ================================================== -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- CSS
  ================================================== -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="stylesheets/normalize.css">
	<link rel="stylesheet" href="stylesheets/skeleton.css">
	<link rel="stylesheet" href="stylesheets/my.css">


	<!-- Javascript
  ================================================== -->
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
 
  <script src="inc/utils.js"></script>
  
  <?php 
  if ($this_page_needs_websocket_updates) { echo '<script src="inc/lime2node.js"></script>'; }
  ?>

	<!-- Favicons
	================================================== -->
	<link rel="shortcut icon" href="images/favicon.ico">
	<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">

</head>


<body>
	<!-- Primary Page Layout
	================================================== -->

	<div class="container">
		<div class="columns">
      <img src="images/apple-touch-icon-72x72.png" style="float:left; margin-top: 40px"/>
			<h1 class="remove-bottom" style="margin-top: 40px"><?php echo $PORTAL_NAME; ?> Control Panel</h1>
			<h5 style="text-align: right">Version <?php echo $VERSION; ?></h5>
			<hr />
		</div>

<?php 
if (! $is_authorized ) {
?>

              <p>You are not an authorized user.</p>

<?php 
} else {

   // this works around an error of type 
   //   Allowed memory size of 33554432 bytes exhausted (tried to allocate 43148176 bytes) in php
   // that may happen on resource-constrained platforms, when using the PHP file() constructor on large
   // log files (we do that only temporarily)
   // TODO: best way to solve may be to find a smarter way to reduce peak memory usage

   ini_set('memory_limit', '-1');
}
?>

