<?php

// Turn off all error reporting
error_reporting(0);

// Report simple running errors
error_reporting(E_ERROR);

//==========================================================================================

  global $Path, $cookie, $agent, $nl;

  $nl = chr(13).chr(10);
  
  $Path = realpath(dirname(__FILE__));

  if( strstr( $Path, "/" ))
      $Path = $Path."/";
  else
  if( strstr( $Path, "\\" ))
      $Path = $Path."\\";

  $cookie = 'cookie.txt';

  $agent = $_SERVER["HTTP_USER_AGENT"];   
 
  global $Referer; 
  global $EnableHttpHeaderFlag;
  global $FollowLocationFlag;
  global $CurlReferer;
  global $CustomHeader;
  global $BasicAuthenticate;
  global $NoCookieFlag;
  global $CurlRange;
    
  $EnableHttpHeaderFlag = 1;
  $FollowLocationFlag = 0;

//========================================================================================== 
function StrCut( $strhead, $strtail, &$str )
{
   $length = strlen($strhead);
   $str = strstr( $str, $strhead );
   $str = trim(substr_replace( $str , str_repeat( " ", $length ) , 0, $length ));
   
   $cut = trim(substr( $str, 0, strpos( $str, $strtail )));

   return $cut;
}
//==========================================================================================
function QuickDownloadUrl( $url, $bytecount )
{
  $file = @fopen( $url, 'r' );

  $loopcount = $bytecount / 8192;
  $remain = $bytecount % 8192;

  if( $file )
    {
      $buffer = '';

      for( $i = 0; $i < $loopcount; $i++ )
          $buffer = $buffer.fread( $file, 8192 );

      for( $i = 0; $i < $remain; $i++ )
          $buffer = $buffer.fread( $file, 8192 );
     
      fclose( $file );
      return $buffer;
    }

  return 0;
}
//==========================================================================================
function GotoUrl( $url, $postfields )
{
  global $Path, $cookie, $agent, $nl;
  global $EnableHttpHeaderFlag;
  global $FollowLocationFlag;
  global $CurlReferer;
  global $CustomHeader;
  global $BasicAuthenticate;
  global $NoCookieFlag;
  global $CurlRange;
    
	$ch = curl_init($url);

    if( $EnableHttpHeaderFlag )
        curl_setopt($ch, CURLOPT_HEADER, 1);

        curl_setopt($ch, CURLOPT_USERAGENT, $agent); 

    if( $CustomHeader )
        curl_setopt($ch, CURLOPT_HTTPHEADER, $CustomHeader );
    if( $BasicAuthenticate )
        curl_setopt($ch, CURLOPT_USERPWD, $BasicAuthenticate);  // $username . ":" . $password

    if( $FollowLocationFlag )
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    else
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

   if( !$NoCookieFlag )
     {
        curl_setopt ($ch, CURLOPT_COOKIEFILE, $Path.$cookie); 
        curl_setopt ($ch, CURLOPT_COOKIEJAR,  $Path.$cookie); 
     }

     if( $CurlReferer )
        curl_setopt($ch, CURLOPT_REFERER, $CurlReferer );

        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 8);

        if( $CurlRange )
            curl_setopt($ch,CURLOPT_RANGE, $CurlRange );
   
   if( $postfields )
     {
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields );
     }
   else
       curl_setopt( $ch, CURLOPT_HTTPGET, 1 );

	$buffer = curl_exec($ch);

   if( strstr( $buffer, '<html><body>You are being <a href="' ) && strstr( $buffer, 'redirected' ))
     {
       $url = StrCut( '<html><body>You are being <a href="', '"', $buffer );
       curl_setopt( $ch, CURLOPT_URL, $url );
       $buffer = curl_exec($ch);
     } 
        curl_close($ch);
 
  //$buffer = RemoveJavaScript( $buffer );
  return $buffer;
}
//==========================================================================================
function GotoUrlReferer( $url, $postfields, $referer )
{
  global $Path, $cookie, $agent;
  global $EnableHttpHeaderFlag;
  global $FollowLocationFlag;
  global $CustomHeader;
  global $BasicAuthenticate;
  global $NoCookieFlag;
  global $CurlRange;
    
	$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    if( $EnableHttpHeaderFlag )        
        curl_setopt($ch, CURLOPT_HEADER, 1);

        curl_setopt($ch, CURLOPT_USERAGENT, $agent); 

    if( $CustomHeader )
        curl_setopt($ch, CURLOPT_HTTPHEADER, $CustomHeader );
    if( $BasicAuthenticate )
        curl_setopt($ch, CURLOPT_USERPWD, $BasicAuthenticate);  // $username . ":" . $password

    if( $FollowLocationFlag )
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    else
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true );

   if( !$NoCookieFlag )
     {
        curl_setopt ($ch, CURLOPT_COOKIEFILE, $Path.$cookie); 
        curl_setopt ($ch, CURLOPT_COOKIEJAR,  $Path.$cookie); 
     }

        if( $CurlRange )
            curl_setopt($ch,CURLOPT_RANGE, $CurlRange );
            
        curl_setopt($ch, CURLOPT_REFERER, $referer );

        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 8);

   if( $postfields )
     {
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields );
     }
	$buffer = curl_exec($ch);

   if( strstr( $buffer, '<html><body>You are being <a href="' ) && strstr( $buffer, 'redirected' ))
     {
       $url = StrCut( '<html><body>You are being <a href="', '"', $buffer );
       curl_setopt( $ch, CURLOPT_URL, $url );
       $buffer = curl_exec($ch);
     } 
 
  curl_close($ch);

  return $buffer;
}
//==========================================================================================
function DownloadPageFile( $url, $filename )
{
  global $Path, $cookie, $agent, $nl, $msg;
  global $Referer;
  global $CurlRange;
    
  $file = fopen( $filename, "wb+" );

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_USERAGENT, $agent); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
  //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt ($ch, CURLOPT_COOKIEFILE, $Path.$cookie); 
  curl_setopt ($ch, CURLOPT_COOKIEJAR,  $Path.$cookie); 
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_FILE, $file);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

  curl_setopt($ch, CURLOPT_REFERER, $Referer); 

        if( $CurlRange )
            curl_setopt($ch,CURLOPT_RANGE, $CurlRange );
            
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 8);

  curl_exec($ch);

  fclose( $file );
}
//==========================================================================================
function RandomTimeStr()
{
  $hours = "".rand(1,12);
  $min = "".rand(0,59);
  
  if( strlen($min) == 1 )
      $min = "0".$min;
      
  if( rand(0,1) == 1 )
    return $hours.":".$min." am";
  else
    return $hours.":".$min." pm";
}
//==========================================================================================
date_default_timezone_set('America/Los_Angeles'); 

//$todaydatetime = date("F j, Y");
//$todaydatetime = date("F j, Y, g:i a");

$nl = chr(13).chr(10);

echo '<html><head><meta http-equiv="refresh" content="'.rand(1,3).'"></head><body>';

$buffer = GotoUrl('http://www.lipsum.com/feed/html', '' );
$textblock = StrCut('<div id="lipsum">', '</div>', $buffer );
StrCut('<p>','</p>', $textblock);

$file = fopen("blogposts.txt", "a+" );

for( $i = 0; $i < 4; $i++ )
{
  $text = StrCut('<p>','</p>', $textblock);
  
  if( !$text )
      break;
  $wordarray = explode(" ", $text);
  $titlewordcount = rand(3,6);
  $title = '';
  
  for( $k = 0; $k < $titlewordcount; $k++ )
    $title = $title.$wordarray[$k].' '; 
     
  $body = '';
  
  for( $k = $titlewordcount; $k < count($wordarray); $k++ )
  $body = $body.$wordarray[$k].' ';   

  $todaydatetime = date("F j, Y, ").RandomTimeStr();
  
  echo $todaydatetime.'<br>'.$title.'<br>'.$body.'<hr><br>';  
  $finaltext = urlencode($todaydatetime).' '.urlencode($title).' '.urlencode($body).$nl;
  fputs($file, $finaltext);
}

fclose($file);

echo '</body></html>';
?>