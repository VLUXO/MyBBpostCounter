<?php
if(!$_GET)die("Please use a id with parameter ?uid=youruserid");
if(!$_GET['uid'])die("Please enter a user id");
if(!IS_NUMERIC($_GET['uid']))die("Please use numeric id");

include 'simple_html_dom.php';

$nopointsforums = array(
        'Introduction' => true,
		'Test/Junk' => true,
);
/**
 * Reconstructed by @TrK for http://post4vps.com
 * Source is available on http://github.com/DarkPowerInvador/MyBBpostcounter
 * Based on the Script created by Transfusion http://github.com/Transfusion/myBBpostcounter
 * Contributions: Dynamo and Fantom(my local friend)
 * See http://php.net/manual/en/function.imagettftext.php to know about text on images using TTF/OTF Fonts.
 */


$userpage = file_get_html('https://post4vps.com/user-'.$_GET["uid"].'.html');
$username = $userpage->find('span[class="largetext"]', 0)->plaintext; // find the username from the user profile page.
$username = rtrim($username, " ");
if($username == "")die("User not found");
//refer to http://simplehtmldom.sourceforge.net/manual.htm

$url="https://post4vps.com/search.php?action=finduser&uid=".$_GET["uid"];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$a = curl_exec($ch); // $a will contain all headers

$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL
// Uncomment to see all headers
/*
echo "<pre>";
print_r($a);echo"<br>";
echo "</pre>";
*/

//echo $url; // Voila
/* the above code was taken from http://stackoverflow.com/questions/20233115/how-to-get-destination-url-from-redirection-url-in-php
when clicking on "Find more posts" it appears the resulting search page that only returns the user's posts is dynamically generated*/

$numberofpoststhismonth = 0;
$uncountedposts = 0;
$numberofpostsnotmadeinthismonth = 0;
$pagenumber = 1;
$firstrun = True;
while (($numberofpostsnotmadeinthismonth === 0 and $numberofpoststhismonth != 0) or $firstrun === True) {
        if ($pagenumber === 1) {
                $firstrun = False;
        }
        $html = file_get_html($url.'&sortby=dateline&order=desc&uid=&page='.$pagenumber);
        $rows = 0;
        foreach($html->find('table[class="tborder"] tr[class="inline_row"]') as $element)
        {

		// Ignore first two rows.
                
                $post = $element->find('*[style="white-space: nowrap; text-align: center;"]',0);
                $forum = $element->find('a', 3)->innertext;
				
		// Forum doesn't give points.
				if (isset($nopointsforums[$forum])) {
                                        $uncountedposts++;
					continue;
				}
				if (!is_string($post->plaintext)) {
					continue;
				}
                
				if (substr($post->plaintext, 0, 3) === 'Yes' or substr($post->plaintext, 0, 3) === 'Tod' or substr($post->plaintext, 0, 3) === 'Les' or substr($post->plaintext, 3, 3) === 'min' or substr($post->plaintext, 2, 3) === 'min' or substr($post->plaintext, 2, 3) === 'hou' or substr($post->plaintext, 3, 3) === 'hou' or substr($post->plaintext, 0, 3) === date("m-")) {
					$numberofpoststhismonth++;

/* http://transfusion.cf/tf-content/uploads/2013/12/screenshot_134.png We are crawling the webpage for the timestamps of the posts - if they begin with YESterday or TODay or the current month.- 
If all the posts on the first search page are made this month, $pagenumber++ */
         }
				else {
					$numberofpostsnotmadeinthismonth++;
				}
	}
	$totalposts = $uncountedposts+$numberofpoststhismonth;
	if (($totalposts %= 20) != 0) {
                $numberofpostsnotmadeinthismonth++;
        }
        $pagenumber++;
}

header('Pragma: public');
header('Cache-Control: max-age=240');
header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 240));
if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){ 
  // if the browser has a cached version of this image, send 304 
  header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304); 
  exit; 
} 
// Force the browser to cache the image for 4 minutes. Crawling webpages like that is not exactly good etiquette.
header("Content-Type: image/png");
$uspos = $username."'s posts: ";
$topos = "/20";
$nopos = $numberofpoststhismonth;
if($nopos <= 9) { $pos = "165";}
elseif($nopos >= 10) { $pos = "160";}
elseif($nopos >= 100) { $pos = "155";}
$font = 'roung.otf';
$img = imagecreatefrompng("bgn.png"); // the background image.
$font_color = imagecolorallocate($img, 255, 255, 255);

if ($numberofpoststhismonth == 0) {
	$posts_color = imagecolorallocate($img, 225, 102, 0);} else {$posts_color = imagecolorallocate($img, 221, 255, 0);}
if ($numberofpoststhismonth >= 40) {
        $message="Crazy poster!";
        $message_color = imagecolorallocate($img, 111, 255, 0);
		$posts_color = imagecolorallocate($img, 0, 255, 0);
} elseif ($numberofpoststhismonth >= 30) {
        $message="Sociable Seal!";
        $message_color = imagecolorallocate($img, 0, 255, 222);
		$posts_color = imagecolorallocate($img, 0, 255, 0);
} elseif ($numberofpoststhismonth >= 20) {
        $message="Milestone Reached!";
        $message_color = imagecolorallocate($img, 0, 200, 0);
		$posts_color = imagecolorallocate($img, 0, 255, 0);
} elseif ($numberofpoststhismonth >= 17) {
        $message="need some more posts";
        $message_color = imagecolorallocate($img, 255, 100, 0);
} elseif ($numberofpoststhismonth >=10) {
        $message="WOAH, HALFWAY THERE";
        $message_color = imagecolorallocate($img, 255, 255, 0);
} elseif ($numberofpoststhismonth >= 0) {
        $message="Dry your tears n Post";
        $message_color = imagecolorallocate($img, 255, 102, 0);
}
imagettftext($img, 10, 0, 15, 32, $font_color, $font, $uspos);
imagettftext($img, 10, 0, $pos, 32, $posts_color, $font, $nopos);
imagettftext($img, 10, 0, 173, 32, $font_color, $font, $topos);
imagettftext($img, 11, 0, 15, 46, $message_color, $font, $message);
imagepng($img);
imagedestroy($img);
// free memory associated with this image - delete it from the PHP cache
//echo $nopos;
//echo date("m-d");
?>
