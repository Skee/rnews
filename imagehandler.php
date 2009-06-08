<?php
error_reporting(E_ALL);

$rfn = $_GET['rfn']; // real file name
$ofn = $_GET['ofn']; // original file name

// if incomplete info sent, just display revo logo
if (empty($rfn) || empty($ofn))
{
    $rfn = "997_revo_star_pngdaa37985ea2a9ce8dad4e2e27650530d";
    $ofn = "revo_star.png";
}

$not_chars = "/[^0-9a-zA-Z()_-]/";
$clean_rfn = preg_replace($not_chars,"_",$rfn);

// end script if somebody's trying to hack us
if ($clean_rfn != $rfn) 
{
    die();
}

// get extension of original file name for content-type
$ext = strtolower(substr($ofn, strlen($ofn) - 3, 3));
switch ($ext)
{
case "png":
    $ctype = "image/png";
    break;
case "gif":
    $ctype = "image/gif";
    break;
case "jpg":
case "peg":
default:
    $ctype = "image/jpeg";
    break;
}

// get actual image, if file exists
if(file_exists("forum/attachments/" . $clean_rfn)) 
{
    $ofile = fopen("forum/attachments/" . $clean_rfn, "rb");
}
else
{
    die();
}


// display image
header("Content-Type: " . $ctype);
header("Content-Length: " . filesize("forum/attachments/" . $clean_rfn));
fpassthru($ofile);
fclose($ofile);
?>
