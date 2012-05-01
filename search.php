<?php

/**
 * Number of tweets to scan
 */
$rpp = 100;

/**
 * Second level shortning for bit.ly and goo.gl also supported
 * For using, get the keys and fill the respective keys below.
 * I can't share my keys.
 */


$bitly_login = '';
$bitly_key = '';
$googl_key = '';

/**
 * Second level shortning for bit.ly and goo.gl setup
 */

$googl = false;
if($googl_key != '')
{
    require_once("library/Googl.class.php");
    $googl = new Googl($googl_key);
}

$bitly = false;
if($bitly_key != '' && $bitly_login != '')
{
    require_once("library/bitly.php");
    $bitly = new bitly($bitly_login,$bitly_key);
}


/**
 * gather hash given in cmd line
 */
$hash = $argv[1];
$url = "http://search.twitter.com/search.json?q=%23$hash&include_entities=true&rpp=$rpp";

 /**
  * Initialize the cURL session
  */
 $ch = curl_init();

 /**
  * Set the URL of the page or file to download.
  */
 curl_setopt($ch, CURLOPT_URL, $url);

 /**
  * Ask cURL to return the contents in a variable instead of simply echoing them to  the browser.
  */
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
 echo "Fetching URLs for #$hash...";

 /**
  * Execute the cURL session
  */
 $json_contents = curl_exec ($ch);
 
 echo "Done. ";

 /**
  * Close cURL session
  */
curl_close ($ch);
 
$unq_urls = array();
$rt = json_decode($json_contents);
$ctr=0;
if(!is_array($rt->results))
{
    echo "None found.\n";
    return;
}

echo count($rt->results) . " tweets found.\n";
foreach($rt->results as $result)
{
    if(!is_array($result->entities->urls))
    {
        echo "end.\n";
        return;
    }
    foreach($result->entities->urls as $url)
    {
        $ctr++;
        
        $ex_url = $url->expanded_url;
        if($bitly && strpos(strtolower($ex_url),"http://bit.ly/") === 0) //found!
        {
            $ex_url = $bitly->expand($ex_url);
        }
        else if($googl && strpos(strtolower($ex_url),"http://goo.gl/") === 0) //found!
        {
            $ex_url = $googl->expand($ex_url);
        }
        
        if(!isset($unq_urls[$ex_url]))
        {
            $unq_urls[$ex_url] = 1;
            echo "$ctr\t$ex_url\n";
        }
        
    }
}

unset($googl);
unset($bitly);
?>
