<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ro" lang="ro">
<head>
	<title>revolushii.ro | the newsblog libre</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="generator" content="RNews <?php echo $version; ?>" />
	<link rel="search" type="application/opensearchdescription+xml"
		href="<?php echo $site_url ?>/search.xml" title="revolushii" />
	<link rel="shortcut icon" type="image/vnd.microsoft.icon"
		href="/favicon.ico" />
	<link rel="alternate" type="application/rss+xml" title="revolushii.ro feed"
	href="<?php echo $site_url; ?>/feed/" />
	<link rel="stylesheet" type="text/css" media="screen" href="/style.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js"></script>
	<script type="text/javascript">
	function SwitchToVideos() {
    	$.ajax({
        	type: "GET",
        	url: "/watching.php",
        	success: function(vids) {
            	$("#content").html(vids);
        	}
    	});
	}
	</script>
	<?php include ('xmas/index.php'); ?>
</head>

<body>
<div id="rap">
<h1 id="header"><a href="<?php echo $site_url; ?>">
revolushii.ro | the newsblog libre</a></h1>
<div id="tag"></div>
<!-- end header -->
