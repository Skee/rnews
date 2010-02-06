<?php
// rnews addon - what R is watching
// scrapes youtube links from Nice Clips
include("config.php");
$query = 
    'SELECT id_msg, body
    FROM `smf_messages`
    WHERE id_topic = ' . $vids_thread_id . '
    AND body LIKE "%youtube.com/watch?v=%"
    ORDER BY posterTime DESC
    LIMIT 25';

$polish = "/youtube\.com\/watch\?v=((\w|-){11})/";


$link = mysql_connect($db_host, $db_user, $db_password);
if(!mysql_select_db($db_name, $link)) exit("Errare serverum est");

$youtube_ids = array();

$res = mysql_query($query, $link);
while($item = mysql_fetch_assoc($res)) {
    if(preg_match($polish, $item['body'], $match))
        $youtube_ids[] = $match[1];
}
mysql_close($link);
$youtube_ids = array_unique($youtube_ids);
$youtube_ids = array_slice($youtube_ids, 0, 20);

foreach($youtube_ids as $id) {
    ?>
    <div class="post" id="yt-<?=$id?>">
    <div class="storycontent">
    <object style="height: 360px; width: 640px">
    <param name="movie" value="http://www.youtube.com/v/<?=$id?>">
    <param name="allowFullScreen" value="true">
    <param name="allowScriptAccess" value="always">
    <embed src="http://www.youtube.com/v/<?=$id?>"
    type="application/x-shockwave-flash" allowfullscreen="true"
    allowScriptAccess="always" width="640" height="360"></object>
    <div class="clear"></div>
    </div></div>

<?php
}
?>
