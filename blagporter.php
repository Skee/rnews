<?php

/* blagporter - a part of RNews
 * RNews - SMF news-posting interpretor
 * Written for http://revolushii.ro
 * Copyright (c) 2009 Skee, http://token.ro
 * Licensed under the BSD license - see LICENSE
 */

error_reporting(E_ALL);
include("config.php");

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
echo "<pre>";
echo "starting import at ID_TOPIC = $topicid_start \n";

foreach($blagpoasts as $id => $poast) {
    $topic[$id]['ID_TOPIC'] = $topicid_start++;
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
    echo "Importing ". $arr['subject'] . " by " . $arr['posterName'] . "\n";
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


echo "ALL DONE";




?>
