<?php

/* RNews - SMF news-posting interpretor
 * Written for http://revolushii.ro
 * Copyright (c) 2009 Skee, http://token.ro
 * Licensed under the BSD license - see LICENSE
 */

// config
include("config.php");
$version = "0.3";

// Let's do some LIMIT-based paging
$page = $_GET['p'] + 0;
if (!is_numeric($page) || $page < 0)
{
	header("Location: /");
	die();
}
$items_start = ($page * $items_per_page);

// SQL query that only grabs the first post of any thread, but counts number of
// posts in that thread and returns that too. Sorted by time desc (newest
// first), uninfluenced by replies.
$n_query = "select *,count(id_msg)-1 AS num_comments from smf_messages where
	id_board = $board_id group by id_topic order by -posterTime limit
	$items_start, $items_per_page";

// For Subs.php bbc parsing:
define("SMF", "muffins");
define("WIRELESS", "false");
$modSettings['enableBBC'] = true;
include($path_subsphp);

// connect to database
$link = mysql_connect($db_host, $db_user, $db_password);
mysql_set_charset('utf8', $link);
$db = mysql_select_db($db_name, $link);
$raw = mysql_query($n_query, $link) or die(mysql_error());

// insert header template
include($template_header);

// parse database content, item by item
while($item = mysql_fetch_assoc($raw))
{
	// do html magic hyah
	echo '<div class="post">';
	echo '<h2 class="storytitle">' . $item['subject'] . '</h2>';
	echo '<p class="meta">Posted by <em>' .
		$item['posterName'] . '</em> on ' . date('r', $item['posterTime']) .
		"</p>\n";
	echo '<div class="storycontent">';
		echo parse_bbc($item['body']) . '<br />';
	echo "</div>\n";
	echo '<p class="feedback">
		<a href="http://revolushii.ro/forum/index.php/topic,' .
		$item['ID_TOPIC'] . '.0.html">' . $item['num_comments'] . ' comments</a>
		</p>';
	echo '</div>';
}

// paging
echo "<p><a href='?p=" . ($page+1) . "'>Next Page &raquo;</a></p>\n";


echo '<!-- begin sidebar -->';
echo '<hr />
<div id="menu">';

// SQL to get 20 newset posts by brutalistu in New Releases, first line only
// and post id

echo "<h2>Latest game releases</h2>\n";

echo "<p>\n";

$nr_query = "select ID_MSG, subject as title from smf_messages where ID_TOPIC =
	$thread_id and ID_MEMBER = $poster_id and left(subject, 3) != 'Re:' order
	by -posterTime LIMIT 20";

$nr_raw = mysql_query($nr_query, $link);

while($item = mysql_fetch_assoc($nr_raw))
{
	// output line by line and link to the forum post
	// strip BBCode from title
	$title = preg_replace("/(\\[.*?\\])/", "", $item['title']);

	echo "<a href='http://revolushii.ro/forum/index.php/topic,360.msg" .
		$item['ID_MSG'] .  ".html#msg" . $item['ID_MSG'] . "'>" . $title .
		"</a><br />\n";
}
echo "</p>\n";
echo '</div>';
echo '<!-- end sidebar -->';

// insert footer template
include($template_footer);

?>
