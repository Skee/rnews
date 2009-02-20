<?php

/* RNews - SMF news-posting interpretor
 * Written for http://revolushii.ro
 * Copyright (c) 2009 Skee, http://token.ro
 * Licensed under the BSD license - see LICENSE
 */

// config
include("config.php");
$version = "0.5";

// Let's do some LIMIT-based paging
$page = $_GET['p'] + 0;
if (!is_numeric($page) || $page < 0 || $page > 999)
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
$modSettings['autoLinkUrls'] = true;
include($forum_path . '/Sources/Subs.php');

// connect to database
$link = mysql_connect($db_host, $db_user, $db_password);
mysql_set_charset('utf8', $link);
$db = mysql_select_db($db_name, $link);
$raw = mysql_query($n_query, $link) or die(mysql_error());
$num_returned = mysql_num_rows($raw);

// insert header template
include($template_header);

// parse database content, item by item
while($item = mysql_fetch_assoc($raw))
{
    // get first attachment (second attachment is thumbnail)
    $attach_query = "select id_attach from smf_attachments where id_msg = " .
        $item['ID_MSG'] . " limit 1";
    $att_res = mysql_query($attach_query) or die(mysql_error());
    $attach_id = 0;
    if(mysql_num_rows($att_res) > 0)
    {
        $attach_arr = mysql_fetch_row($att_res);
        $attach_id = $attach_arr[0];
    }
    // do html magic hyah
    echo '<div class="post">';
    echo '<h2 class="storytitle">
        <a rel="bookmark" href="' . $forum_url . '/index.php/topic,' .
        $item['ID_TOPIC'] . '.0.html">' . $item['subject'] . '</a>
        </h2>';
    echo '<p class="meta">Posted by <em> <a href="' . $forum_url .
        '/index.php?action=profile;u=' . $item['ID_MEMBER'] . '">' .
        $item['posterName'] . '</a></em> @ ' . date('H:i, D, d M Y',
        $item['posterTime']) . "</p>\n";
    echo '<div class="storycontent">';
    // if any attachment was found, display it left-aligned
    if($attach_id) echo '<img src="' . $forum_url .
        '/index.php?action=dlattach;topic=' . $item['ID_TOPIC'] . '.0;attach='
        . $attach_id . ';image" alt="thumbnail" class="L" /> ';
    echo parse_bbc($item['body']) . '<br />';
    echo "</div>\n";
    echo '<p class="feedback">
        <a href="' . $forum_url . '/index.php/topic,' .
        $item['ID_TOPIC'] . '.0.html">' . $item['num_comments'] . ' comments</a>
        </p>';
    echo '</div>';
}

// paging
// addition: if num_returned_results < items_per page => last page
if ($num_returned == $items_per_page)
{
    echo "<p><a href='/page/" . ($page+1) . "/'>Next Page &raquo;</a></p>\n";
}


echo '<!-- begin sidebar -->';
echo '<hr />
<div id="menu">';

echo '
<p id="btnz">
	<a id="forum" href="' . $forum_url . '/" title="Discussion Forum">forum</a>
	<a id="feed" href="' . $site_url . '/feed/" rel="alternate"
	type="application/atom+xml" title="Syndicate this site">Feed</a>
</p>
';

echo '
<form action="' . $forum_url . '/index.php?action=search2" method="post"
	accept-charset="UTF-8">
<p>
	<input name="search" type="text" />
	<input name="submit" value="Search" type="submit" />
	<input name="brd[' . $board_id . ']" value="' . $board_id . '"
	type="hidden" />
</p>
</form>
';

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

    echo '<a href="' . $forum_url . '/index.php/topic,360.msg' .
        $item['ID_MSG'] .  '.html#msg' . $item['ID_MSG'] . '">' . $title .
        "</a><br />\n";
}
echo "</p>\n";
echo '</div>';
echo '<!-- end sidebar -->';

// insert footer template
include($template_footer);

?>

