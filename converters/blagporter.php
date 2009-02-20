<?php

/* blagporter - a part of RNews
 * RNews - SMF news-posting interpretor
 * Written for http://revolushii.ro
 * Copyright (c) 2009 Skee, http://token.ro
 * Licensed under the BSD license - see LICENSE
 */

error_reporting(E_ALL);
include("../config.php");

$link = mysql_connect($db_host, $db_user, $db_password);
mysql_set_charset('utf8', $link);
$db = mysql_select_db($db_name, $link);

// what do we need from wp_posts?
$query = "SELECT UNIX_TIMESTAMP(post_date) AS post_timestamp,
    post_title, post_author, post_content, ID
    FROM wp_posts WHERE post_status = 'publish'";

$raw = mysql_query($query, $link);
for($i=0; $i<mysql_num_rows($raw); $i++)
{
    $blagpoasts[$i] = mysql_fetch_assoc($raw);
}


// give me existing maximum id_topic
$arr = mysql_fetch_row(mysql_query("SELECT MAX(ID_TOPIC) FROM smf_messages"));
$topicid_start = $arr[0]+1;
$cur_topicid = $topicid_start;
echo "<pre>";
echo "starting import at ID_TOPIC = $topicid_start \n";

foreach($blagpoasts as $id => $poast) {
    $topic[$id]['ID_TOPIC'] = $cur_topicid++;
    $topic[$id]['ID_BOARD'] = $board_id;
    $topic[$id]['posterTime'] = $poast['post_timestamp'];
    switch ($poast['post_author'])
    {
    case 1:
        $topic[$id]['ID_MEMBER'] = 1;
        $topic[$id]['posterName'] = 'wooptoo';
        $topic[$id]['posterEmail'] = 'wooptoo@gmail.com';
        break;
    case 2:
        $topic[$id]['ID_MEMBER'] = 7;
        $topic[$id]['posterName'] = 'yoshi';
        $topic[$id]['posterEmail'] = 'teh.yosh@gmail.com';
        break;
    case 3:
        $topic[$id]['ID_MEMBER'] = 21;
        $topic[$id]['posterName'] = 'Zander';
        $topic[$id]['posterEmail'] = 'zander.aq@gmail.com';
        break;
    case 4:
        $topic[$id]['ID_MEMBER'] = 48;
        $topic[$id]['posterName'] = 'Skee';
        $topic[$id]['posterEmail'] = 'mircea@gmail.com';
        break;
    case 6:
        $topic[$id]['ID_MEMBER'] = 12;
        $topic[$id]['posterName'] = 'Obi';
        $topic[$id]['posterEmail'] = 'nastyalex84@gmail.com';
        break;
    case 10:
        $topic[$id]['ID_MEMBER'] = 16;
        $topic[$id]['posterName'] = 'Jack the Ripper';
        $topic[$id]['posterEmail'] = 'jack@ineditmedia.com';
        break;
    case 11:
        $topic[$id]['ID_MEMBER'] = 31;
        $topic[$id]['posterName'] = 'greedz';
        $topic[$id]['posterEmail'] = 't3h.gr33dz0r@gmail.com';
        break;
    case 12:
        $topic[$id]['ID_MEMBER'] = 19;
        $topic[$id]['posterName'] = 'exeprime';
        $topic[$id]['posterEmail'] = 'onisim.ferche@gmail.com';
        break;
    case 16:
        $topic[$id]['ID_MEMBER'] = 42;
        $topic[$id]['posterName'] = 'brutalistu';
        $topic[$id]['posterEmail'] = 'brutalistu@gmail.com';
        break;
    }
    $topic[$id]['posterIP'] = "127.0.0.1";
    $topic[$id]['subject'] = mysql_real_escape_string($poast['post_title']);

    // here goes nuffin...
    $topic[$id]['body'] = mysql_real_escape_string($poast['post_content']);
}


foreach($topic as $id => $onetopic)
{
    post_message($onetopic);
    // get posts's comments
    $this_posts_dbid = $blagpoasts[$id]['ID'];
    $query = "SELECT comment_author, UNIX_TIMESTAMP(comment_date) AS
        comment_date, comment_author_email, comment_content FROM
        wp_comments WHERE comment_post_id = $this_posts_dbid AND
        comment_approved = 1 ORDER BY comment_date";
    $raw = mysql_query($query);
    while($com = mysql_fetch_assoc($raw))
    {
        $pcomment['ID_TOPIC'] = $onetopic['ID_TOPIC'];
        $pcomment['ID_BOARD'] = $board_id;
        $pcomment['posterTime'] = $com['comment_date'];
        $pcomment['ID_MEMBER'] = 0;
        $pcomment['posterName'] =
            mysql_real_escape_string($com['comment_author']);
        $pcomment['posterEmail'] =
            mysql_real_escape_string($com['comment_author_email']);
        $pcomment['posterIP'] = "127.0.0.1";
        $pcomment['subject'] = "Re: " .
            mysql_real_escape_string($onetopic['subject']);
        $pcomment['body'] = mysql_real_escape_string($com['comment_content']);

        //poast to db
        post_message($pcomment);
    }
}

function post_message($arr)
{
    global $link;
    echo "Importing ". $arr['subject'] . " by " . $arr['posterName'] . " into
        topic " . $arr['ID_TOPIC'] ."\n";
    $query = "INSERT INTO smf_messages (`ID_TOPIC`, `ID_BOARD`, `posterTime`,
        `ID_MEMBER`, `posterName`, `posterEmail`, `posterIP`, `subject`,
        `body`)
        VALUES (";
    foreach($arr as $key => $content)
    {
        ($key=='body') ? $query .= '"' . $content . '"' : $query .= '"' .
            $content . '", ';
    }
    $query .= ");";

    mysql_query($query, $link);
    //print htmlspecialchars($query . "\n");
}

function fix_topics()
{
    global $topicid_start, $cur_topicid, $link, $board_id;

    // coloane in smf_topics: ID_TOPIC, isSticky, ID_BOARD, ID_FIRST_MSG,
    // ID_LAST_MSG, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, ID_POLL, numReplies,
    // numViews, locked
    for($i=$topicid_start;$i<$cur_topicid;$i++)
    {
        // get first and last posts and number of replies
        $r = mysql_fetch_array(mysql_query("SELECT MIN(ID_MSG), MAX(ID_MSG), COUNT(ID_MSG) FROM smf_messages WHERE ID_TOPIC = $i"));
        $first_msg = $r[0];
        $last_msg = $r[1];
        $numReplies = $r[2]-1;

        // get first poster's id
        $r = mysql_fetch_array(mysql_query("SELECT ID_MEMBER FROM smf_messages WHERE ID_TOPIC = $i GROUP BY ID_TOPIC"));
        $id_member_started = $r[0];

        // update in sql
        echo "Fixing $i: $board_id, $first_msg, $last_msg, $id_member_started, $numReplies\n";
        mysql_query("INSERT INTO smf_topics (`ID_TOPIC`, `ID_BOARD`, `ID_FIRST_MSG`, `ID_LAST_MSG`, `ID_MEMBER_STARTED`, `numReplies`,
            `numViews`, `locked`) VALUES ( $i, $board_id, $first_msg, $last_msg, $id_member_started, $numReplies, 100, 0)");
    }


}

fix_topics();

// SMF breaks if using single quotes in HTML posts near url
// try to replace these with double quotes
// manually run these queries since i'm too lazy to properly escape them
// replace 665 with your $topicid_start
//
// update smf_messages set body = replace(body, "='", '="') where id_topic >= 665
//
// update smf_messages set body = replace(body, "' class", '" class') where id_topic >= 665
//
// update smf_messages set body = replace(body, "'/>", '"/>') where id_topic >= 665
//
// update smf_messages set body = replace(body, "'>", '">') where id_topic >= 665
//
// update smf_messages set body = replace(body, "' />", '" />') where id_topic >= 665
//
// update smf_messages set body = replace(body, "' alt", '" alt') where id_topic >= 665
//
// update smf_messages set body = replace(body, "' title", '" title') where id_topic >= 665

echo "ALL DONE";




?>
