<?php
function getBetween($content,$start,$end){
    $r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}
function getMetadata($url, $nftAddresses) {
    $data = json_encode([
        'mintAccounts' => $nftAddresses,
        'includeOffChain' => true,
        'disableCache' => false
    ]);

    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => $data
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        // Handle error
        echo "Error accessing API";
    } else {
        // Decode the JSON response
        return $result; // Return decoded JSON
    }
}

// Function to convert multidimensional array to string
function arrayToString($array) {
    $string = '';
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $string .= arrayToString($value);
        } else {
            $string .= $value . ', ';
        }
    }
    return $string;
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

$members=$tweets=$website=$difference_days=$tg_sub=$tgnamei=$tw=$tgname=$websiteURL="";



$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crypto_bulls";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



//GET DATA

$sql = "SELECT * FROM tokens WHERE fatto_social !='1' AND fatto_social <7 LIMIT 99";

$result = mysqli_query($conn, $sql);
$c=1;
$toklist = [];
if (mysqli_num_rows($result) > 0) {
while($row = mysqli_fetch_assoc($result)) {
$members=$tweets=$website=$difference_days=$tg_sub=$tgnamei=$tw=$tgname=$websiteURL="";
	
$tokname=$row["token_id"];
$id=$row["id"];
	
	$sqlxx2="UPDATE `tokens` SET `fatto_social`=fatto_social+2,`runner`= 2 WHERE `id`='$id';";
$resultxx2 = mysqli_query($conn, $sqlxx2);
	

	
	





 $toklist[] = $tokname;





// Monkes




	
}
	
}



$url = "https://api.helius.xyz/v0/token-metadata?api-key=0956194c-f8af-4b88-aef8-57a254b66082";
$nftAddresses = $toklist ;

//$nftAddresses = [ "DR8M1e5JdgWvoDshrBg8CQNYfPKvyfXviik55kjjqihS","Fa5mFK8ihEJkySJ5aNPAZYPJwghwA39TfbnKTfjGY3xo" ];


var_dump($nftAddresses);

// Call getMetadata function
$contentx = getMetadata($url, $nftAddresses);











$array = json_decode($contentx, true);



$accounts = '';

// Check if decoding was successful and $array is not null
if ($array !== null) {
    // Loop through each element in the array
    foreach ($array as $item) {
        // Check if the item has an "account" key
        if (isset($item['account'])) {
			
			
			
        // Add the content related to the account to the result array
        $acc=$item['account'];
		
		echo 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
		
		echo '<br/>';
		
		
		
		


$content=json_encode($item);
$content=stripslashes($content);

echo '<br />INIZZZZZZZZZZ'.$content.'FINNNNNNNNNNN<br /><br />';


//$content=stripslashes($item);







echo 'TGGGGGGGGGGGG';
$pattern = '/https?:\/\/t\.me\/\w+/i';
preg_match_all($pattern, $content, $matches);

$tgname = implode(", ", $matches[0]);


if( strpos($tgname, ',') !== false ) {
$parts = explode(',', $tgname);	
$tgname = trim($parts[0]);
}

if (substr($tgname, -5) === "https") {
    // Remove "https" from the end of $a
    $tgname = substr($tgname, 0, -5);
}


if (substr($tgname, -1) === "n") {
if (isset($contentx[0]['offChainMetadata']['metadata']['extensions']['telegram'])) {
$tgname= $contentx[0]['offChainMetadata']['metadata']['extensions']['telegram'];
}
elseif (isset($contentx[0]['offChainMetadata']['metadata']['telegram'])) {
	$tgname = $contentx[0]['offChainMetadata']['metadata']['telegram'];		
}
}



$tgnamei=$tgname;






echo 'TWWWWWWW';
$pattern = '/https?:\/\/(?:twitter\.com|x\.com)\/\w+/i';
preg_match_all($pattern, $content, $matches);
$tw = implode(", ", $matches[0]);

if( strpos($tw, ',') !== false ) {
$parts = explode(',', $tw);	
$tw = trim($parts[0]);
}
if (substr($tw, -5) === "https") {
    // Remove "https" from the end of $a
    $tw = substr($tw, 0, -5);
}


if (substr($tw, -1) === "n") {
if (isset($contentx[0]['offChainMetadata']['metadata']['extensions']['twitter'])) {
$tw= $contentx[0]['offChainMetadata']['metadata']['extensions']['twitter'];
}
elseif (isset($contentx[0]['offChainMetadata']['metadata']['twitter'])) {
	$tw = $contentx[0]['offChainMetadata']['metadata']['twitter'];		
}
}


$tw = basename($tw);



echo $tw; 
echo $tgname;






/*


var_dump($content);
exit;






*/



if (isset($contentx[0]['offChainMetadata']['metadata']['extensions']['website'])) {
$website= $contentx[0]['offChainMetadata']['metadata']['extensions']['website'];
}
elseif (isset($contentx[0]['offChainMetadata']['metadata']['website'])) {
	$website = $contentx[0]['offChainMetadata']['metadata']['website'];		
}


$website = get_string_between($content, 'website":"', '"');
$website = str_replace(' ', '', $website);





echo 'TG: '.$tgname;
echo '<br />TW: '.$tw;











if($tgnamei !=""){

//GET Telegram
sleep(2);
$content = file_get_contents($tgname);
$members = get_string_between($content, 'tgme_page_extra">', ' subscribers');
$members = str_replace(' ', '', $members);

if($members==""){
$members = get_string_between($content, 'tgme_page_extra">', ' members');
$members = str_replace(' ', '', $members);
}
$tg_sub=$members;

echo 'TELEGRAM:' .$members;
}


//GET Twitter

if($tw !=""){
	sleep(2);
$url="https://socialblade.com/twitter/user/".$tw;



// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt'); // Specify the file to read cookies from
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt'); // Specify the file to write cookies to
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36'); // Set user agent to mimic a regular user

// Execute cURL session
$content = curl_exec($ch);

// Check for errors
if(curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);





$members = get_string_between($content, 'YouTubeUserTopLight">Followers', '</div>');




$content=addslashes($content);
$members = str_replace(",", "", $members);
$members=addslashes(strip_tags($members));
$members = trim(str_replace(" ", "", $members));




$tweets = get_string_between($content, 'Tweets</span><br>', '</span>');
$tweets = str_replace(",", "", $tweets);
$tweets=addslashes(strip_tags($tweets));
$tweets = trim(str_replace(" ", "", $tweets));



$created = get_string_between($content, 'User Created</span><br>', '</span>');
$created = str_replace(",", "", $created);
$created=addslashes(strip_tags($created));
$created = trim(str_replace(" ", "", $created));


$date_to_convert = $created;
// Convert the date to a Unix timestamp
$given_date = new DateTime($date_to_convert);
$current_date = new DateTime();

// Calculate the difference between the two dates
$interval = $current_date->diff($given_date);

// Extract the number of days from the difference
$difference_days = $interval->days;


echo 'TWITTER: '.$members.'@'.$tweets.'@'.$difference_days;

}



	
$sqlxx2="UPDATE `tokens` SET `tw_sub`='$members',`tw_tweets`='$tweets',`tw_days`='$difference_days',`tg_sub`='$tg_sub',`twitter`='$tw',`telegram`='$tgnamei',`website`='$website' WHERE `token_id`='$acc';";
$resultxx2 = mysqli_query($conn, $sqlxx2);

echo $sqlxx2.'<br/>';

if($tw !="" || $tgnamei !="" ){
$sqlxx2="UPDATE `tokens` SET `fatto_social`=1 WHERE `token_id`='$acc';";
$resultxx2 = mysqli_query($conn, $sqlxx2);
}


echo $sqlxx2.'<br/>';



$content="";



		
		
    }
}
}







