<?php

/* RNews - SMF news-posting interpretor
 * Written for http://revolushii.ro
 * Copyright (c) 2009 Skee, http://token.ro
 * Licensed under the BSD license - see LICENSE
 */

// config
include("config.php");
$version = "0.7";

// Let's do some LIMIT-based paging
$page = $_GET['p'] + 0;
if (!is_numeric($page) || $page < 0 || $page > 999)
{
    header("Location: /");
    die();
}
$items_start = ($page * $items_per_page);

// check caching
// send cache control for client
header("Cache-control: must-revalidate");
$cache_file .= $page;
if(file_exists($cache_file))
{
    // file exists, check age
    $cache_mtime = filemtime($cache_file);
    if((time() - $cache_mtime) < ($cache_max_age * 60))
    {
        // cache file age less than max age, output it and die
        header("Last-modified: " . gmdate("D, d M Y H:i:s", $cache_mtime) .
            " GMT");
        header("Expires: " . gmdate("D, d M Y H:i:s", $cache_mtime +
            ($cache_max_age * 60)) . " GMT");
        include($cache_file);
        die();
    }
}

// no cache file or cache out of date, regenerate it then output it
// start output buffering
ob_start();

// output Last-modified = now
header("Last-modified: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", time() + ($cache_max_age * 60)) .
    " GMT");

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
$modSettings['attachmentEncryptFilenames'] = true;
include($forum_path . '/Sources/Subs.php');

// connect to database
$link = mysql_connect($db_host, $db_user, $db_password);
mysql_set_charset('utf8', $link);
$db = mysql_select_db($db_name, $link);
$raw = mysql_query($n_query, $link) or die(mysql_error());
$num_returned = mysql_num_rows($raw);

// insert header template
include($template_header);

echo('<div id="content">');

// parse database content, item by item
while($item = mysql_fetch_assoc($raw))
{
    // get first attachment (second attachment is thumbnail)
    $attach_query = "select id_attach, filename, file_hash from smf_attachments where
        id_msg = " . $item['ID_MSG'] . " limit 1";
    $att_res = mysql_query($attach_query) or die(mysql_error());
    $attach_id = 0;
    if(mysql_num_rows($att_res) > 0)
    {
        $attach_arr = mysql_fetch_row($att_res);
        $attach_id = $attach_arr[0];
        $attach_fn = $attach_arr[1];
        $attach_hash = $attach_arr[2];

        // smf 1.1.9 hack
        // attachments up to 1181 are old system
        // anything higher = new system
        if($attach_hash == "")
            $attach_realfn = getLegacyAttachmentFilename($attach_fn, $attach_id, true);
        else
            $attach_realfn = $attach_id . "_" . $attach_hash;
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
//    if($attach_id) echo '<img src="' . $forum_url .
//        '/index.php?action=dlattach;topic=' . $item['ID_TOPIC'] . '.0;attach='
//        . $attach_id . ';image" alt="' . $attach_realfn  . '" class="L" /> ';
    if($attach_id) echo '<img src="' . $forum_url . '/attachments/' .
        $attach_realfn . '" alt="' . $attach_fn  . '" class="L" /> ';
    echo parse_bbc($item['body']) . '<br />';
	echo '<div class="clear"></div>';
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
	echo '<p id="paging">
		<a href="/page/' . ($page+1) . '/">Next Page &raquo;</a>
	</p>';
}

echo('</div>');

echo '<!-- begin sidebar -->';
echo '<hr class="hidden" />
<div id="menu">';

echo '
<h2 class="hidden">Menu</h2>
<ul id="buttons">
	<li><a id="forum" href="' . $forum_url . '/" title="Forum">Forum</a></li>
	<li><a id="feed" href="' . $site_url . '/feed/" rel="alternate"
	type="application/rss+xml" title="Feed">Feed</a></li>
	<li><a id="downloads" href="' . $site_url . '/downloads/"
	title="Downloads">Downloads</a></li>
</ul>
';

echo '
<form id="search" action="' . $forum_url . '/index.php?action=search2"
	method="post" accept-charset="UTF-8">
<p>
	<input name="search" type="text" id="search_text" />
	<input name="submit" type="submit" value="Search" id="search_submit" />
	<input name="brd[' . $board_id . ']" value="' . $board_id . '"
	type="hidden" />
</p>
</form>
';

// SQL to get 20 newset posts by brutalistu in New Releases, first line only
// and post id

echo '<div id="new_releases">';
echo "<h2>Latest game releases</h2>\n";
echo '<p>';

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
echo '</p>';
echo '</div>';

echo '
<div id="ads">
	<h2 class="hidden">Ads</h2>

	<a href="' . $forum_url . '/index.php?action=register">Register!</a><br />
	klovruut wants <strong>you</strong>
</div>
';

echo '</div>';
echo '<!-- end sidebar -->
<!-- cache control: cache generated on: ' . date('r') . ' -->';

// insert footer template
include($template_footer);

// finished rendering page, save buffer to cache file and output
$pcontent = ob_get_contents();
$cfhandle = fopen($cache_file, 'ab');
if(flock($cfhandle, LOCK_EX))
{
    ftruncate($cfhandle, 0);
    fwrite($cfhandle, $pcontent);
}
fclose($cfhandle);
ob_end_flush();

?>
