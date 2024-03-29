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
        return json_decode($result, true); // Return decoded JSON
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
$lista="";
$sql = "SELECT * FROM tokens WHERE fatto_social >'1' AND fatto_social <21 LIMIT 29";

$result = mysqli_query($conn, $sql);

$c=1;
if (mysqli_num_rows($result) > 0) {
while($row = mysqli_fetch_assoc($result)) {
	
$tokname=$row["token_id"];
$id=$row["id"];

if($c>1){
	$lista .=',';
}
$lista .=$tokname;

$c++;
	
	echo 'dentro';
	
	
		
	}
	
	
		
	}






$url = 'https://api.dexscreener.com/latest/dex/tokens/'.$lista;

echo $url;

// Initialize cURL session
$curl = curl_init($url);

// Set cURL options
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Return response as a string
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL verification (for development)

// Execute cURL request
$response = curl_exec($curl);

// Check for errors
if(curl_errno($curl)) {
    $error_message = curl_error($curl);
    // Handle cURL error
    echo "Error: $error_message";
} else {
    // Close cURL session
    curl_close($curl);

    // Process response
    $data = json_decode($response, true);
    
    // Output response
    var_dump($data); // You can handle the response data as needed
}





// Extract and print address, telegram, website, and twitter
foreach ($data['pairs'] as $pair) {
    $address = $pair['baseToken']['address'];
    $telegram = '';
    $website = '';
    $twitter = '';
    
    foreach ($pair['info']['websites'] as $site) {
        if ($site['label'] === 'Website') {
            $website = $site['url'];
            break;
        }
    }

    foreach ($pair['info']['socials'] as $social) {
        if ($social['type'] === 'telegram') {
            $telegram = $social['url'];
        } elseif ($social['type'] === 'twitter') {
            $twitter = $social['url'];
        }
    }


echo '<br /> NEW COIN<br />';
    echo "Address: $address\n";
    echo "Telegram: $telegram\n";
    echo "Website: $website\n";
    echo "Twitter: $twitter\n\n";
	
	
//RICHIESTE
$tgnamei=$tgname=$telegram;
$tw=$twitter;

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
	
$tw=	basename($tw);
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




if($tw !="" || $tgnamei !="" ){
$sqlxx2="UPDATE `tokens` SET `tw_sub`='$members',`tw_tweets`='$tweets',`tw_days`='$difference_days',`tg_sub`='$tg_sub',`twitter`='$tw',`telegram`='$tgnamei',`website`='$website',`fatto_social`=1 WHERE `id`='$id';";
$resultxx2 = mysqli_query($conn, $sqlxx2);
}



	
$members=$tweets=$website=$difference_days=$tg_sub=$tgnamei=$tw=$tgname=$websiteURL="";	
	
}
