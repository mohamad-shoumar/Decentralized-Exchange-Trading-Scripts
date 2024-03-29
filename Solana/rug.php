<?php
function getBetween($content,$start,$end){
    $r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
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

$sql = "SELECT * FROM tokens WHERE fatto_rug !='1' LIMIT 1";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
while($row = mysqli_fetch_assoc($result)) {
	
	
$token=$row["token_id"];
$id=$row["id"];
	
	$sqlxx2="UPDATE `tokens` SET `fatto_rug`=1 WHERE `id`='$id';";
$resultxx2 = mysqli_query($conn, $sqlxx2);



/*
https://api.rugcheck.xyz/v1/tokens/8a4iePwtKZe1AwCKVmZBrHc7pPXnan6CX6t3RQQCnorn/report
%Liquidty lock         lpLockedPct":              ,
Renounced      mintAuthority":              ,
Mutable metadata               mutable":          ,
TOP HOLDER:    "pct"            ,
POINTS                         score":           ,
*/

//$token="8a4iePwtKZe1AwCKVmZBrHc7pPXnan6CX6t3RQQCnorn";





$url = "https://api.rugcheck.xyz/v1/tokens/".$token."/report";

// Initialize cURL session
$ch = curl_init();

// Set the URL
curl_setopt($ch, CURLOPT_URL, $url);

// Set user agent to simulate a real user
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36';
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

// Set to return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$content = curl_exec($ch);

// Check for errors
if(curl_errno($ch)){
    echo 'Error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);









$liqlock = get_string_between($content, 'lpLockedPct":', ',');
$liqlock = str_replace(' ', '', $liqlock);

$mintaut = get_string_between($content, 'mintAuthority":', ',');
$mintaut = str_replace(' ', '', $mintaut);

$mutable = get_string_between($content, 'mutable":', ',');
$mutable = str_replace(' ', '', $mutable);


$topholder = get_string_between($content, 'pct":', ',');
$topholder = round(str_replace(' ', '', $topholder));

$score = get_string_between($content, 'score":', ',');
$score = str_replace(' ', '', $score);



echo $liqlock.'<br >';
echo $mintaut.'<br >';
echo $mutable.'<br >';
echo $topholder.'<br >';
echo $score.'<br >';



$sqlxx2="UPDATE `tokens` SET `liqlock`='$liqlock',`mintaut`='$mintaut',`mutable`='$mutable',`topholder`='$topholder',`score`='$score' WHERE `id`='$id';";
$resultxx2 = mysqli_query($conn, $sqlxx2);




	
}
	
		
	}