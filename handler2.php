#!/usr/bin/php
<?php
//error_reporting(E_ALL);
include('config.php');
// read from stdin
$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
    $email .= fread($fd, 1024);
}
fclose($fd);

// handle email
$lines = explode("\n", $email);

// empty vars
$from = "";
$subject = "";
$headers = "";
$message = "";
$link = "";
$splittingheaders = true;
// assign subject, from and link
for ($i=0; $i < count($lines); $i++) {
    if ($splittingheaders) {
        // this is a header
        $headers .= $lines[$i]."\n";

        // look out for special headers
        if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
            $subject = $matches[1];
        }
        if (preg_match("/^From: (.*)/", $lines[$i], $matches)) {
            $from = $matches[1];
        }
            } else {
        // not a header, but message
	if(preg_match("/http:\/\/www.vtc.com\/products\/[\w\s\-\!]+\.(htm)/", $lines[$i],$matches))
	{
		$link = $matches[0];
	}
	
    $message .= $lines[$i]."\n";
    }
    
   

    if (trim($lines[$i])=="") {
        // empty line, header section has ended
        $splittingheaders = false;
    }
}


$post = $subject . "\n" . $link;

// shorten the url and twitter
$link = urlencode($link);
$productUrl = "http://api.bit.ly/v3/shorten?login=" .$username. "&apiKey=" .$apikey. "&longUrl=" .$link. "&format=json";
$short = file_get_contents($productUrl);
$s = json_decode($short);
// Send to twitter
include_once('twitter/twitter.php');

$curTwitter = new twitter("vtc", $vtcpass);

if ($s->status_txt == 'OK') {

	$shorturl = $s->data->url;
	$twitter_status = $subject . " - " . $shorturl . " #tutorials #vtc";
	
$curTwitter->setStatus($twitter_status);
mail($to,$subject, $shorturl, 'From:release@example.com');
} else {
	$er = $s->status_txt;
	$x = $er. " : " .$link;
mail($to,$subject, $x, 'From:release@example.com');
}

?>
