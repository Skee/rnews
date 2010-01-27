<?php

// database config
$db_host = "localhost";
$db_user = "";
$db_password = "";
$db_name = "";

// site settings
$board_id = 39; // board from which to pull news topics
$poster_id = 42; // poster from which to pull new release posts
$thread_id = 360; // thread with new release posts
$template_header = "tpl/header.php";
$template_footer = "tpl/footer.php";
$site_url = "http://revolushii.ro";
$forum_url = "http://revolushii.ro/forum";
$forum_path = ""; // location on server
$items_per_page = 10; // items per page, when paging

// caching settings
//
// cache file prefix- must be in a webserver-writable directory

$cache_file = "cache/rnews.";

// maximum cache age, in minutes
$cache_max_age = "20";
?>
