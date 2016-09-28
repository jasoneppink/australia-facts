<?php

//error logging
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", "error.log");

//force an image for testing purposes in the URL
//$forceimage = $_GET["forceimage"];

require_once('twitteroauth/autoload.php');
use Abraham\TwitterOAuth\TwitterOAuth;

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3c.org/TR/html4/strict.dtd">';
echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
echo '<body>';

//get random row from Google Spreadsheet
$curl = curl_init();
curl_setopt_array($curl, array(
CURLOPT_URL => 'https://script.google.com/macros/s/ADD-YOUR-GOOGLE-SCRIPT-URL-HERE/exec',
CURLOPT_POST => true,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_POSTFIELDS => array(
    'id' => 'ADD-YOUR-SPREADSHEET-ID-HERE'
)
));
$tweet = curl_exec($curl);
curl_close($curl);

//make sure the Google App Script isn't having a problem; only proceed if we have a tweet.
if($tweet != "undefined") {

	//decide if the tweet is an image or just text
	if(rand(1,6) == 1) {
		$image_or_not = true;
	} elseif(strpos($tweet, "2") !== false) {
		$image_or_not = true;
	} elseif(strpos($tweet, "7") !== false) {
		$image_or_not = true;
	//for debugging images
	//} elseif($forceimage == true) {
	//	$image_or_not = true;
	} else {
		$image_or_not = false;
	}

	if($image_or_not == true) {
		imageify($tweet);
		$twitter_results = tweet_it($tweet, true);
		$tumblr_results = json_decode(post_to_tumblr($tweet));

		echo "Posted on <a href='" . $twitter_results->entities->media[0]->url . "'>Twitter</a> and 
		<a href='http://ADD-YOUR-TUMBLR-SUBDOMAIN-HERE.tumblr.com/" . $tumblr_results->{"response"}->{"id"} . "'>Tumblr.</a> (It's an image.)";

		//clean up unneeded image files
		exec("rm text.png background.jpg final.jpg");
    
	} else {
		$twitter_results = tweet_it($tweet, false);
		echo "Posted on <a href='https://twitter.com/ADD-YOUR-TWITTER-HANDLE-HERE/status/" . $twitter_results->id . "'>Twitter.</a> (It's not an image.)";
	}

	echo "<br /><br />" . $tweet;

//no tweet! error!
} else {
	echo "You've probably run out of facts. Oops! Check your Google Sheet.";
}

echo '</body></html>';



//
//FUNCTIONS
//

function tweet_it($text, $image){
	$consumerKey    = 'ADD-YOUR-TWITTER-CONSUMER-KEY-HERE';
	$consumerSecret = 'ADD-YOUR-TWITTER-CONSUMER-SECRET-HERE';
	$oAuthToken     = 'ADD-YOUR-TWITTER-OAUTH-TOKEN-HERE';
	$oAuthSecret    = 'ADD-YOUR-TWITTER-OAUTH-SECRET-HERE';
	$connection = new TwitterOAuth($consumerKey , $consumerSecret, $oAuthToken, $oAuthSecret);
	$content = $connection->get("account/verify_credentials");

	//turn the text upside down
	$new_tweet = upsidedownify($text);

	//create an image tweet
	if($image===true) {
		$media1 = $connection->upload('media/upload', ['media' => 'final.jpg']);
		$parameters = [
		    'status' => "",
		    'media_ids' => implode(',', [$media1->media_id_string])
		];
		$result = $connection->post('statuses/update', $parameters);
		return $result;

	//create a text tweet
	} else {
		$statuses = $connection->post("statuses/update", ["status" => $new_tweet]);
		return $statuses;
	}

}


function imageify($text){
	//escape a couple problem characters
	$text = str_replace('$', '\$', $text);
	$text = str_replace('"', '\"', $text);

	//select background image (31 to choose from)
	$background = "images/" . rand(1,31) . ".jpg";

	//turn background image upside down
	exec("convert $background -fill black -colorize 70% -rotate \"180\" background.jpg");

	//create text with alpha background
	//(current size creates automatic 50px padding on all sides)
	exec("convert -size 924x412 -background none -fill rgba\(255,255,255,0.9\) -font Helvetica-Bold -gravity West -interline-spacing 10 caption:\"$text\" -rotate \"180\" text.png");

	//add them together
	exec("composite -gravity center text.png background.jpg final.jpg");

	//add the bug (Twitter handle) on top
	exec("composite bug.png final.jpg final.jpg");
}


function upsidedownify($text){
	$replacement_table = [
	['&' , '⅋'],
	['!' , '¡'],
	['"' , '„'],
	['”' , '„'],
	["'" , ','],
	["’" , ","],
	["," , "'"],
	['(' , ')'],
	[')' , '('],
	['.' , '˙'],
	['1' , 'Ɩ'],
	['2' , 'ᘔ'],
	['3' , 'Ɛ'],
	['4' , 'ᔭ'],
	['5' , '5'],
	['6' , '9'],
	['7' , 'Ɫ'],
	['8' , '8'],
	['9' , '6'],
	['0' , '0'],
	[';' , '؛'],
	['<' , '>'],
	['>' , '<'],
	['?' , '¿'],
	['A' , '∀'],
	['B' , '𐐒'],
	['C' , 'Ↄ'],
	['D' , 'ᗡ'],
	['E' , 'Ǝ'],
	['F' , 'Ⅎ'],
	['G' , '⅁'],
	['H' , 'H'],
	['I' , 'I'],
	['J' , 'ſ'],
	['K' , '⋊'],
	['L' , '⅂'],
	['M' , 'ꟽ'],
	['N' , 'N'],
	['O' , 'O'],
	['P' , 'Ԁ'],
	['Q' , 'Ό'],
	['R' , 'ᴚ'],
	['S' , 'S'],
	['T' , '⊥'],
	['U' , '∩'],
	['V' , 'Λ'],
	['W' , 'ʍ'],
	['X' , 'X'],
	['Y' , '⅄'],
	['Z' , 'Z'],
/*	['A' , 'ɐ'],
	['B' , 'q'],
	['C' , 'ɔ'],
	['D' , 'p'],
	['E' , 'ǝ'],
	['F' , 'ɟ'],
	['G' , 'ƃ'],
	['H' , 'ɥ'],
	['I' , 'ᴉ'],
	['J' , 'ɾ'],
	['K' , 'ʞ'],
	['L' , 'l'],
	['M' , 'ɯ'],
	['N' , 'u'],
	['O' , 'o'],
	['P' , 'd'],
	['Q' , 'b'],
	['R' , 'ɹ'],
	['S' , 's'],
	['T' , 'ʇ'],
	['U' , 'n'],
	['V' , 'ʌ'],
	['W' , 'ʍ'],
	['X' , 'x'],
	['Y' , 'ʎ'],
	['Z' , 'z'],*/
	['[' , ']'],
	[']' , '['],
	['_' , '‾'],
	['a' , 'ɐ'],
	['b' , 'q'],
	['c' , 'ɔ'],
	['d' , 'p'],
	['e' , 'ǝ'],
	['f' , 'ɟ'],
	['g' , 'ƃ'],
	['h' , 'ɥ'],
	['i' , 'ᴉ'],
	['j' , 'ɾ'],
	['k' , 'ʞ'],
	['l' , 'l'],
	['m' , 'ɯ'],
	['n' , 'u'],
	['o' , 'o'],
	['p' , 'd'],
	['q' , 'b'],
	['r' , 'ɹ'],
	['s' , 's'],
	['t' , 'ʇ'],
	['u' , 'n'],
	['v' , 'ʌ'],
	['w' , 'ʍ'],
	['x' , 'x'],
	['y' , 'ʎ'],
	['z' , 'z'],
	['{' , '}'],
	['}' , '{'],
	['-' , '-'],
	['/' , '/'],
	['%' , '%'],
	[' ' , ' '],
	[':' , ':'],
	['°' , '°']
	];

	//reverse text first
	$text = utf8_strrev($text);

	//reset string for final upside-down text
	$final_text = "";

	//iterate through each character of $text
	//when a match is found in $replacement_text, store the replacement character in $final_text
	for ($i=0; $i<strlen($text); $i++){
		foreach ($replacement_table as $row) {
			if($text[$i] == $row[0]){
				$final_text .= $row[1];
			}
		}
	}

	return $final_text;
}

function post_to_tumblr($tweet){
	define("CONSUMER_KEY", "ADD-YOUR-TUMBLR-CONSUMER-KEY-HERE");
	define("CONSUMER_SECRET", "ADD-YOUR-TUMBLR-CONSUMER-SECRET-HERE");
	define("OAUTH_TOKEN", "ADD-YOUR-TUMBLR-OAUTH-TOKEN-HERE");
	define("OAUTH_SECRET", "ADD-YOUR-TUMBLR-OAUTH-SECRET-HERE");
	function oauth_gen($method, $url, $iparams, &$headers) {

	    $iparams['oauth_consumer_key'] = CONSUMER_KEY;
	    $iparams['oauth_nonce'] = strval(time());
	    $iparams['oauth_signature_method'] = 'HMAC-SHA1';
	    $iparams['oauth_timestamp'] = strval(time());
	    $iparams['oauth_token'] = OAUTH_TOKEN;
	    $iparams['oauth_version'] = '1.0';
	    $iparams['oauth_signature'] = oauth_sig($method, $url, $iparams);
	    //print $iparams['oauth_signature'];  
	    $oauth_header = array();
	    foreach($iparams as $key => $value) {
	        if (strpos($key, "oauth") !== false) { 
	           $oauth_header []= $key ."=".$value;
	        }
	    }
	    $oauth_header = "OAuth ". implode(",", $oauth_header);
	    $headers["Authorization"] = $oauth_header;
	}
	function oauth_sig($method, $uri, $params) {

	    $parts []= $method;
	    $parts []= rawurlencode($uri);

	    $iparams = array();
	    ksort($params);
	    foreach($params as $key => $data) {
	            if(is_array($data)) {
	                $count = 0;
	                foreach($data as $val) {
	                    $n = $key . "[". $count . "]";
	                    $iparams []= $n . "=" . rawurlencode($val);
	                    $count++;
	                }
	            } else {
	                $iparams[]= rawurlencode($key) . "=" .rawurlencode($data);
	            }
	    }
	    $parts []= rawurlencode(implode("&", $iparams));
	    $sig = implode("&", $parts);
	    return base64_encode(hash_hmac('sha1', $sig, CONSUMER_SECRET."&". OAUTH_SECRET, true));
	}
	$headers = array("Host" => "http://api.tumblr.com/", "Content-type" => "application/x-www-form-urlencoded", "Expect" => "");
	$params = array("data" => array(file_get_contents("final.jpg")),
					//"caption" => $tweet,
					"type" => "photo",
					"tags" => "ADD,YOUR,COMMA,SEPARATED,TAGS,HERE");
	$blogname = "ADD-YOUR-TUMBLR-SUBDOMAIN-HERE.tumblr.com";
	oauth_gen("POST", "http://api.tumblr.com/v2/blog/$blogname/post", $params, $headers);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, "PHP Uploader Tumblr v1.0");
	curl_setopt($ch, CURLOPT_URL, "http://api.tumblr.com/v2/blog/$blogname/post");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    "Authorization: " . $headers['Authorization'],
	    "Content-type: " . $headers["Content-type"],
	    "Expect: ")
	);
	$params = http_build_query($params);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	$response = curl_exec($ch);
	return $response;
}

//reverses text character order
function utf8_strrev($str){
    preg_match_all('/./us', $str, $ar);
    return join('',array_reverse($ar[0]));
}

?>
