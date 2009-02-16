<?php

/* RNews - SMF news-posting interpretor
 * Written for http://revolushii.ro
 * Copyright (c) 2009 Skee, http://token.ro
 * Licensed under the BSD license - see LICENSE
 */

// config
include("config.php");
$board_id = 39;
$template_header = "tpl/header.php";
$template_footer = "tpl/footer.php";

//$path_subsphp = "../forum/Sources/Subs.php";
// SMF's Subs.php contains doUBBC() and parse_bbc() for parsing BBCode
// -----
$version = "0.2";
// SQL query that only grabs the first post of any thread, but counts number of
// posts in that thread and returns that too. Sorted by time desc (newest
// first), uninfluenced by replies.
$n_query = "select *,count(id_msg)-1 AS num_comments from smf_messages where
	id_board = $board_id group by id_topic order by -posterTime";

//include($path_subsphp);
//
// temporary use: simple_bb_code. Ideally, use SMF's bbc parser (as above)
include("lib/simple_bb_code.php");
$bbc = new Simple_BB_Code();

// connect to database
$link = mysql_connect($db_host, $db_user, $db_password);
mysql_set_charset('utf8', $link);
$db = mysql_select_db($db_name, $link);
$raw = mysql_query($n_query, $link);

// insert header template
include($template_header);

// parse database content, item by item
while($item = mysql_fetch_assoc($raw))
{
	// do html magic hyah
	echo "<h2>" . $item['subject'] . "</h2>";
	echo "<p>Posted by <em>" .
		$item['posterName'] . "</em> on " . date('r', $item['posterTime']) .
		"</p>\n";
	echo "<div>";
		//echo doUBBC($item['body']) . "<br />\n";
		echo $bbc->parse(str_replace("<br />", "\n", $item['body']));
		//echo $item['body'] . "<br />\n";
	echo "</div>";
	echo "<p><a href='http://revolushii.ro/forum/index.php/topic," .
		$item['ID_TOPIC'] . ".0.html'>" . $item['num_comments'] .  "
		comments</a></p>
		<hr />";
}

// SQL to get 20 newset posts by brutalistu in New Releases, first line only
// and post id

echo "<h2>Latest game releases</h2>\n";

echo "<p>\n";

$nr_query = "select ID_MSG, CONCAT(LEFT(body, LOCATE('<br />',body)-1),'[/b]')
	AS title from smf_messages where ID_TOPIC = 360 and ID_MEMBER = 42 order by
	-posterTime LIMIT 20";
// title here is game title as posted by user, NOT post title as known by SMF
// (post subject)

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

// insert footer template
include($template_footer);

?>
