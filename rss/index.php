<?php

/*
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
	<title>RSS Title</title>
	<description>This is an example of an RSS feed</description>
	<link>http://www.someexamplerssdomain.com/main.html</link>
	<lastBuildDate>Mon, 06 Sep 2010 00:01:00 +0000 </lastBuildDate>
	<pubDate>Mon, 06 Sep 2009 16:45:00 +0000 </pubDate>
 
	<item>
		<title>Example entry</title>
		<description>Here is some text containing an interesting description of the thing to be described.</description>
		<link>http://www.wikipedia.org/</link>
		<guid>unique string per item</guid>
		<pubDate>Mon, 06 Sep 2009 16:45:00 +0000 </pubDate>
	</item>
 
</channel>
</rss>

*/

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
function RemoveNonKeyBoardChar( &$str )
{
  $length = strlen($str);
  $str2 = '';
  
  for( $i = 0; $i < $length; $i++ )
   { 
     $c = ord($str[$i]);
     
     if(( 32 <= $c && $c <= 126 ) || ( 13 == $c || 10 == $c ))         
         $str2 = $str2.$str[$i];
   }     
  $str = $str2;
}
//==========================================================================================
  if( file_exists( '../bloginfo.txt' ))
    {
      $buffer = file_get_contents ( '../bloginfo.txt' );
      $BlogTitle = StrCut( '[BLOG_TITLE]', '[/BLOG_TITLE]', $buffer );
      $BlogUrl = StrCut( '[BLOG_URL]', '[/BLOG_URL]', $buffer );   
    }


  $linearray = array();
  $linearraycount = 0;

  $file = fopen( '../blogposts.txt', 'r' );

  if( !$file )
      exit();
 
  while( !feof($file))
   {
     $line = fgets( $file );

     if( feof($file))
         break;
         
     $linearray[$linearraycount] = $line;
     $linearraycount++;
   }


      $contents = explode( ' ', $linearray[$linearraycount - 1] );

      $thetime = urldecode( $contents[0] );

$output = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"><channel>

<title>'.$BlogTitle.'</title>
<description>Latest news and events from around the world</description>

<link>'.$BlogUrl.'/rss</link>
<lastBuildDate>'.$thetime.'</lastBuildDate><pubDate>'.$thetime.'</pubDate>
';

  for( $i = $linearraycount - 1; $linearraycount - 10 < $i; $i-- )
     {
      $contents = explode( ' ', $linearray[$i] );

      $thetime = urldecode( $contents[0] );
      $thetitle = urldecode(urldecode( $contents[1] ));
      $thepost = strip_tags(urldecode(urldecode( $contents[2] )));

      RemoveNonKeyBoardChar( $thetitle );
      RemoveNonKeyBoardChar( $thepost );

      $link = $BlogUrl.'?t=0_p=0_epf=1_epi='.$i;

      $output = $output.'<item><title><![CDATA['.$thetitle.']]></title>
		<link>'.$link.'</link>
		<description><![CDATA['.$thepost.']]></description>
		<guid>'.uniqid().'</guid>
		<pubDate>'.$thetime.'</pubDate>
	</item>';
     }    

$output = str_replace( '=\"', '="', $output );
$output = str_replace( "\'", "'", $output );
$output = str_replace( '\"', '"', $output );
$output = htmlspecialchars_decode($output, ENT_QUOTES);
$output = str_replace( '&nbsp;', ' ', $output );
$output = $output.'</channel></rss>';

echo $output;

?>
