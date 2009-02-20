<?php

// map old wp links to new SMF threads!
error_reporting(E_ALL);
include('../config.php');
include('../wp/wp-includes/formatting.php');

$link = mysql_connect($db_host, $db_user, $db_password);
mysql_set_charset('utf8', $link);
$db = mysql_select_db($db_name, $link);

$get_titles_query = "select id_topic, subject from smf_messages where id_topic
    >= 665 and left(subject,3) != 'Re:'";
$raw = mysql_query($get_titles_query);

echo "<pre>";

while ($item = mysql_fetch_assoc($raw))
{
    $title = sanitize_title_with_dashes($item['subject']);
    $url = "/forum/index.php/topic," . $item['id_topic'] . ".0.html";
    echo "RewriteRule ^$title\/ $url [L,R=301]\n";
}

?>
