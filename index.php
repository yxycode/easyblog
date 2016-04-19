<?php
  date_default_timezone_set('America/Los_Angeles'); 
  
  global $ShowDateTimeFlag;
  global $todaydatetime;
  
  $ShowDateTimeFlag = 1;

  if( $ShowDateTimeFlag )
      $todaydatetime = date("F j, Y, g:i a");
  else
      $todaydatetime = date("F j, Y");  
      
if( !file_exists( "blogposts.txt" ))
    file_put_contents( "blogposts.txt", "" );
      
if( isset($_GET["submit"] ))
{
  if( $_POST['title'] && $_POST['body'] )
    {
      $title = urlencode( $_POST['title'] );
      $body = urlencode( $_POST['body'] );
      $doneurl = SavePost( $title, $body );    

      if( $_POST['getlink'] )
        {
          echo '(postlink)'.$doneurl.'(/postlink)';
          exit();
        }
    }
  else
  if( $_GET['title'] && $_GET['body'] )
    {
      $title = urlencode( $_GET['title'] );
      $body = urlencode( $_GET['body'] );
      $doneurl = SavePost( $title, $body );     

      if( $_GET['getlink'] )
        {
          echo '(postlink)'.$doneurl.'(/postlink)';
          exit();
        }        
    }
    
    $redirecturl = $_SERVER['REQUEST_URI'];
    $redirecturl = str_replace("?submit=1", "", $redirecturl);   
    header("Location: ".$redirecturl );
}
//==========================================================================================
    
global $TemplateIndex;

$TemplateIndex = 1;

if( $_GET['t'] )
  {
    $arg = $_GET['t'];

    if( strstr( $arg, '_' ))
      {
        $arg = str_replace( '_', '&', $arg );
        $arg = 't='.$arg;
        header( 'Location: ?'.$arg ) ;
      }
  }
  
echo '<html><head><style type="text/css">';

if( !empty($_POST["template"]) )    
    $TemplateIndex = $_POST["template"];
else
if( !empty($_COOKIE["template"] ))
    $TemplateIndex = $_COOKIE["template"];
     
if( 1 <= $TemplateIndex && $TemplateIndex <= 4 )
   $cssdata = file_get_contents( 'blog'.$TemplateIndex.'.css' );
else
if( 5 <= $TemplateIndex && $TemplateIndex <= 10 ) 
{
  $cssdata = file_get_contents( 'blogtiled.css' );
  $cssdata = str_replace( 'images0/pattern_021.gif', 'images0/'.GetIndexFile('images0', $TemplateIndex - 5 ), $cssdata);
  
  $colorlist = array( 5 => '#FFFF00', '#00FF00','#00FFFF','#0000FF','#FF00FF');

  $cssdata = str_replace( '#FFA319', $colorlist[$TemplateIndex], $cssdata );
}
else
{
   $TemplateIndex = 1;
   $cssdata = file_get_contents( 'blog'.$TemplateIndex.'.css' );
}
   
echo $cssdata;

echo '</style>';
?>

<script>
function SetCookie(cname, cvalue, exdays) 
{
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function GetCookie(cname) 
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}
</script>

<?php

echo '</head><body>';
  
  global $PostList, $TitleList, $DateTimeList, $ListCount, $nl;
  global $BlogUrl;
  global $BlogTitle;  
  global $Ad1, $Ad2, $Ad3;
  global $Stuffing;
  
  $nl = chr(13).chr(10);

  $PostList = array();
  $TitleList = array();
  $DateTimeList = array();
  $ListCount = 0;

  global $HISTORY_DISPLAY_COUNT;
  global $POST_DISPLAY_COUNT;
  global $MAX_POST_LIST_COUNT;
  
  $HISTORY_DISPLAY_COUNT = 10;
  $POST_DISPLAY_COUNT = 5;
  $MAX_POST_LIST_COUNT = 50000;

//==========================================================================================
  global $Path, $cookie, $agent;

  $Path = realpath(dirname(__FILE__));

  if( strstr( $Path, "/" ))
      $Path = $Path."/";
  else
  if( strstr( $Path, "\\" ))
      $Path = $Path."\\";

  $cookie = "cookie.txt";
  //$file = fopen( $cookie, "w+");
  //fclose( $file );

  $agent = $_SERVER["HTTP_USER_AGENT"]; 

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
function StrErase( $strhead, $strtail, &$str )
{
  $startpos = strpos( $str, $strhead, 1 );
  $endpos = strpos( $str, $strtail, 1 );

  if( !($startpos > 0 && $endpos > 0) )
        return 0;

  $endpos = $endpos + strlen($strtail) - 1;

  for( $i = $startpos; $i <= $endpos; $i++ )
       $str{$i} = ' ';

   return 1;
}
//==========================================================================================
function Decode( $str )
{ 
    //$str = html_entity_decode($str, ENT_NOQUOTES); // replace html stuff with regular text
    //$str =  htmlspecialchars_decode($str, ENT_NOQUOTES); // replace html stuff with regular text
    $str = urldecode( $str );    
    $str = urldecode( $str );
    $str = str_replace(  '\\"', '"', $str );
    $str = str_replace(  "\\'", "'", $str );
    return $str;
}
//==========================================================================================
function SavePost( $title, $body )
{
    global $todaydatetime, $ListCount;
    global $BlogUrl;

    $nl = chr(13).chr(10);

    $file = fopen( "blogposts.txt", "a+" );  
    fputs( $file, urlencode($todaydatetime).' '.urlencode($title).' '.urlencode($body).$nl );
    fclose( $file );   

    return $BlogUrl.'/?t=0&p=0&epf=1&epi='.$ListCount;
}

//==========================================================================================
function ReadPostsFile()
{ 
  global $PostList, $TitleList, $DateTimeList, $ListCount;

      $file = fopen( "blogposts.txt", "r" );  
      
      if( $file )
        {
           while( !feof( $file ))
             {
               $line = trim( fgets( $file ));

                if( strlen( $line ) > 2 )
                  {

                     $linetokens = explode( ' ', $line );
                     $DateTimeList[$ListCount] = Decode($linetokens[0]);
                     $TitleList[$ListCount] = Decode($linetokens[1]);
                     $PostList[$ListCount] = Decode($linetokens[2]);
                     $ListCount++; 
                  }
             }
          fclose( $file );
          return 1;
        }
      else
        return 0;
}
//==========================================================================================
function ControlPostListSize()
{ 
  global $PostList, $TitleList, $DateTimeList, $ListCount;
  global $MAX_POST_LIST_COUNT;
  $nl = chr(13).chr(10);

 if( $ListCount > $MAX_POST_LIST_COUNT )
   {
      $file = fopen( "blogposts.txt", "w+" );  
      
      if( $file )
        {
          $NewListCount = 0;

          for( $i = $ListCount - $MAX_POST_LIST_COUNT - 1; $i < $ListCount; $i++ )
             {
               fputs( $file, urlencode( $DateTimeList[$i] ).' '.urlencode( $TitleList[$i] ).' '.urlencode( $PostList[$i] ).$nl );

               $DateTimeList[$NewListCount] = $DateTimeList[$i];
               $TitleList[$NewListCount] = $TitleList[$i];
               $PostList[$NewListCount] = $PostList[$i];
               $NewListCount++;
             }

          fclose( $file );         
          $ListCount = $NewListCount;
        }
   }
}
//==========================================================================================
function GetHistoryColumn( $titlelistindex, $postlistindex )
{
  global $PostList, $TitleList, $DateTimeList, $ListCount;
  global $HISTORY_DISPLAY_COUNT;
  global $todaydatetime;

      if( 0 <= $titlelistindex && $titlelistindex <= ($ListCount - 1) )
         ; // null statement
      else
      if( $titlelistindex > ($ListCount - 1) )
         $titlelistindex = $ListCount - 1;
      else
         $titlelistindex = 0;

      if( 0 <= $postlistindex && $postlistindex <= ($ListCount - 1) )
         ; // null statement
      else
      if( $postlistindex > ($ListCount - 1) )
         $postlistindex = $ListCount - 1;
      else
         $postlistindex = 0;

      $endindex = ($ListCount - 1) - $titlelistindex; // reverse direction
      $startindex = $endindex - ($HISTORY_DISPLAY_COUNT - 1);

      if( $startindex < 0 )
          $startindex = 0;
      if( $endindex > $ListCount - 1)
          $endindex = $ListCount - 1;           

      $newerpostsindex = $titlelistindex - ($HISTORY_DISPLAY_COUNT - 1);
      $olderpostsindex = $titlelistindex + ($HISTORY_DISPLAY_COUNT - 1);

      $outstr = 'History';

      $outstr = $outstr.'<br><br>';

      for( $i = $endindex; $i >= $startindex; $i-- )
           $outstr = $outstr.'<a href="?t='.$titlelistindex.'&p='.$postlistindex.'&epf=1&epi='.$i.'"  >'.$TitleList[$i].'</a><br><br>';

      $outstr = $outstr.'<br><br>';

      if( $olderpostsindex > ($ListCount - 1) )
          $outstr = $outstr.'Previous&nbsp;&nbsp;&nbsp;&nbsp;';
      else
          $outstr = $outstr.'<a href="?t='.$olderpostsindex.'&p='.$postlistindex.'"   >Previous</a>&nbsp;&nbsp;&nbsp;&nbsp;';

      if( $newerpostsindex < 0 )
         $outstr = $outstr.'Recent&nbsp;&nbsp;&nbsp;&nbsp;';
      else
         $outstr = $outstr.'<a href="?t='.$newerpostsindex.'&p='.$postlistindex.'"  >Recent</a>&nbsp;&nbsp;&nbsp;&nbsp;';


      return $outstr;
}
//==========================================================================================
function GetPostsColumn( $titlelistindex, $postlistindex )
{
  global $PostList, $TitleList, $DateTimeList, $ListCount;
  global $HISTORY_DISPLAY_COUNT;
  global $POST_DISPLAY_COUNT;
  global $todaydatetime;


      if( 0 <= $titlelistindex && $titlelistindex <= ($ListCount - 1) )
         ; // null statement
      else
      if( $titlelistindex > ($ListCount - 1) )
         $titlelistindex = $ListCount - 1;
      else
         $titlelistindex = 0;

      if( 0 <= $postlistindex && $postlistindex <= ($ListCount - 1) )
         ; // null statement
      else
      if( $postlistindex > ($ListCount - 1) )
         $postlistindex = $ListCount - 1;
      else
         $postlistindex = 0;

      $endindex = ($ListCount - 1) - $postlistindex; // reverse direction
      $startindex = $endindex - ($POST_DISPLAY_COUNT - 1);

      if( $startindex < 0 )
          $startindex = 0;
      if( $endindex > $ListCount - 1)
          $endindex = $ListCount - 1;           

      $newerpostsindex = $postlistindex - ($POST_DISPLAY_COUNT - 1);
      $olderpostsindex = $postlistindex + ($POST_DISPLAY_COUNT - 1);

      $outstr = '';

      $outstr =  $outstr.'<br><br>';

      for( $i = $endindex; $i >= $startindex; $i-- )
         {
/*         
           $outstr = $outstr.'<div class="post_outer" ><div class="post_inner" >';
           $outstr = $outstr.$DateTimeList[$i];
           $outstr = $outstr.'</div></div>';
           
           $outstr = $outstr.'<div class="post_outer" ><div class="post_inner" >';
           $outstr = $outstr.'&nbsp;&nbsp;<a href="?t='.$titlelistindex.'&p='.$postlistindex.'&epf=1&epi='.$i.'"  ><h1>'.$TitleList[$i].'</h1></a>';

           $outstr = $outstr.$PostList[$i].'<br><br>';
           $outstr = $outstr.'</div></div>';
           $outstr = $outstr.'<br><div class="flatline"></div><br>';         
*/         
           $outstr = $outstr.'<div class="post_outer" ><div class="post_inner" >';
           $outstr = $outstr.$DateTimeList[$i];
           $outstr = $outstr.'</div>';
           
           $outstr = $outstr.'<div class="post_inner" >';
           $outstr = $outstr.'&nbsp;&nbsp;<a href="?t='.$titlelistindex.'&p='.$postlistindex.'&epf=1&epi='.$i.'"  ><h1>'.$TitleList[$i].'</h1></a>';

           $outstr = $outstr.$PostList[$i].'<br><br>';
           $outstr = $outstr.'</div></div>';
           $outstr = $outstr.'<br><div class="flatline"></div><br>';
          
         }

      

      if( $olderpostsindex > ($ListCount - 1) )
          $outstr = $outstr.'Previous&nbsp;&nbsp;&nbsp;&nbsp;';
      else
          $outstr = $outstr.'<a class="biglink" href="?t='.$titlelistindex.'&p='.$olderpostsindex.'"  >Previous</a>&nbsp;&nbsp;&nbsp;&nbsp;';

      if( $newerpostsindex < 0 )
         $outstr = $outstr.'Recent&nbsp;&nbsp;&nbsp;&nbsp;';
      else
         $outstr = $outstr.'<a class="biglink" href="?t='.$titlelistindex.'&p='.$newerpostsindex.'"  >Recent</a>&nbsp;&nbsp;&nbsp;&nbsp;';


      return $outstr;
}
//==========================================================================================
function GetExactPost( $postindex, $titlelistindex, $postlistindex )
{
  global $PostList, $TitleList, $DateTimeList, $ListCount;
  global $HISTORY_DISPLAY_COUNT;
  global $todaydatetime;

      if( 0 <= $postindex && $postindex <= ($ListCount - 1) )
         ; // null statement
      else
         $postindex = 0;

      if( 0 <= $titlelistindex && $titlelistindex <= ($ListCount - 1) )
         ; // null statement
      else
         $titlelistindex = 0;

      if( 0 <= $postlistindex && $postlistindex <= ($ListCount - 1) )
         ; // null statement
      else
         $postlistindex = 0;

      $outstr = '';

     

      $outstr = $outstr.'<br><br>';
/*
      $outstr = $outstr.'<div class="post_outer" ><div class="post_inner" >';
      $outstr = $outstr.$DateTimeList[$postindex];
      $outstr = $outstr.'</div></div>';
      
      $outstr = $outstr.'<div class="post_outer" ><div class="post_inner" >';
      $outstr = $outstr.'<a href="?t='.$titlelistindex.'&p='.$postlistindex.'"  ><h1>'.$TitleList[$postindex].'</h1></a><br><br>';
*/
      $outstr = $outstr.'<div class="post_outer" ><div class="post_inner" >';
      $outstr = $outstr.$DateTimeList[$postindex];
      $outstr = $outstr.'</div>';
      
      $outstr = $outstr.'<div class="post_inner" >';
      $outstr = $outstr.'<a href="?t='.$titlelistindex.'&p='.$postlistindex.'"  ><h1>'.$TitleList[$postindex].'</h1></a><br><br>';

      $outstr = $outstr.$PostList[$postindex].'<br>';
      //$outstr = $outstr.'<hr style="width: 100%; height: 2px;"><br><br>';      
      $outstr = $outstr.'</div></div><br><br>';
      
      if( $postindex < $ListCount - 1 )
          $outstr = $outstr.'<a class="biglink" href="?t='.$titlelistindex.'&p='.$postlistindex.'&epf=1&epi='.($postindex + 1).'"  >Newer Post</a>&nbsp;&nbsp;&nbsp;&nbsp;';
            
      $outstr = $outstr.'<a class="biglink" href="?t=0&p=0"  >Home</a>&nbsp;&nbsp;&nbsp;&nbsp;';

      if( 0 < $postindex )
          $outstr = $outstr.'<a class="biglink" href="?t='.$titlelistindex.'&p='.$postlistindex.'&epf=1&epi='.($postindex - 1).'"  >Older Post</a>';
                
      return $outstr;
}
//==========================================================================================
function GotoUrl( $url, $postfields )
{
  error_reporting(0);

  global $Path, $cookie, $agent;

	$ch = curl_init($url);
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt ($ch, CURLOPT_COOKIEFILE, $Path.$cookie); 
        //curl_setopt ($ch, CURLOPT_COOKIEJAR,  $Path.$cookie); 
   
   if( $postfields )
     {
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields );
     }
	$buffer = curl_exec($ch);

  error_reporting(1);
  return $buffer;
}
//==========================================================================================
function GetTwitterUpdates( $twitterid, $tweetcount )
{
  $buffer = GotoUrl( 'http://twitter.com/'.$twitterid, '' );

  $outstr = '';
  
  if( strstr( $buffer, '<div class="section">' ) )
   { 
      StrCut( '<div class="section">', '<ol', $buffer );

    for( $i = 0; $i < $tweetcount; $i++ )
     {
      $tweet = StrCut( '<li', '</li>', $buffer );

      if( !$tweet )
          break;    

      $outstr = $outstr.'<li'.$tweet.'</li><br>';     
     }       
       
   } 

  echo '<b>Twitter Updates</b><br><br><div style="font-size:12px; text-align:left;" >'.$outstr.'</div><br>';
}
//==========================================================================================
function Ch( $ch )
{
  return $ch;
}
//==========================================================================================
function StuffCookie( $url )
{
/*
  return '<TEXTAREA NAME="t1" COLS=1 ROWS=1 style="background-color: transparent; background-image: url('.Ch("'").$url.Ch("'").') ; overflow:hidden; border: 0" allowtransparency="true"></TEXTAREA>';
*/

  return '<img src="'.$url.'" width=1 height=1 border=0 />';
}
//==========================================================================================
function CheckAnyStrExist( $mainstr, $keywordstr )
{
  $keywordlist = explode( ' ', $keywordstr );
  $keywordlistcount = count( $keywordlist );

  $ExistsFlag = 0;

  for( $i = 0; $i < $keywordlistcount; $i++ )
    if( strstr( $mainstr, $keywordlist[$i] ))
      {
        $ExistsFlag = 1;
        break;
      }

  return $ExistsFlag;
}
//==========================================================================================
function ReadBlogInfoFile()
{
  global $Ad1, $Ad2, $Ad3, $BlogUrl, $BlogTitle;
  global $Stuffing;
    
  if( file_exists( 'bloginfo.txt' ))
    {
      $buffer = file_get_contents ( 'bloginfo.txt' );
      $BlogTitle = StrCut( '[BLOG_TITLE]', '[/BLOG_TITLE]', $buffer );
      $BlogUrl = StrCut( '[BLOG_URL]', '[/BLOG_URL]', $buffer );
      $Ad1 = StrCut( '[TOP_BAR]', '[/TOP_BAR]', $buffer );
      $Ad2 = StrCut( '[LEFT_COLUMN]', '[/LEFT_COLUMN]', $buffer );
      $Ad3 = StrCut( '[RIGHT_COLUMN]', '[/RIGHT_COLUMN]', $buffer );   
      $Stuffing = StrCut( '[STUFF]', '[/STUFF]', $buffer ); 
    }  
}
//==========================================================================================
function GetAds( $index )
{
  global $Ad1, $Ad2, $Ad3, $Stuffing, $nl;
  
  switch( $index )
    {
       case 1: 
         $str = $Ad1;
         break;
       case 2:
         $str = $Ad2;
         break;
       case 3:
         $str = $Ad3;
         break;
       default:
         break;
    }
        
    return $str;
}
//==========================================================================================
function GetRandomFile( $directory )
{
  $filelist = scandir( $directory );
  $index = rand( 2, count($filelist) - 1 );
  return $filelist[$index];  
}
//==========================================================================================
function GetIndexFile( $directory, $index )
{
  $filelist = scandir( $directory );
  return $filelist[$index + 2];  
}
//==========================================================================================
  if( !ReadPostsFile() )
    {
      echo 'Failed to read blog file!';
      exit();
    }
    
  ReadBlogInfoFile();
  
  ControlPostListSize();
    
//echo "<img class='titleimage' src='background-title.png' >";

echo "<div class='overbackground' ></div>";
echo "<div class='wrapper'>";
echo "<div class='title_outer_div' ></div>";
echo "<div class='title_inner_div' ><h2>".$BlogTitle."</h2></div>";

  
 if( $_GET['post'] )
   {
    $msg = '<br><br><br><br><br>';
    $msg = $msg.'<div style="z-index: 100; position: absolute; top: 100; left: 400;" align="left" >';
    $msg = $msg.'<form method="post" action="index.php?submit=1" name="form1" autocomplete="off">';
    $msg = $msg.'<input size="58" name="title" style="color: white; font-family: Arial; background-color: black; border-radius: 5px;" value="title" ><br>';
    $msg = $msg.'<br>';
    $msg = $msg.'<textarea cols="60" rows="10" name="body" style="color: white; font-family: Arial; background-color: black; border-radius: 5px;">body</textarea><span style="font-family: Arial;"><br>';
    $msg = $msg.'<br>';
    $msg = $msg.'<input name="login" value=" Post " style="font-size: 150%; color: white; background-color: black; border-radius: 5px;" type="submit">&nbsp;';
    $msg = $msg.'<br></span></form>';
    $msg = $msg.'</div>';

    echo $msg;
   }
 else
   {
   
  echo '<br><br><br><br>';
  echo '<div class="left_column_outer"></div>';
  echo '<div class="left_column_inner">';
  echo '<div style="position: relative; top: 0px; left: 0px;" ><br><br>'.GetAds(2).'<br><br>';
echo '<form action="index.php" method="post">
<select name="template" id="template" style="border-radius: 5px">';

for( $i = 1; $i <= 10; $i++ )
  if( $TemplateIndex == $i )
    echo '<option selected="selected">'.$i.'</option>';
  else
    echo '<option>'.$i.'</option>';
  
echo '</select>
  <input type="submit" value="Change Template">
</form>'; 
    
  echo '<br><a href="?post=1" >Create New Post</a>';    
  echo '</div></div>';   
  
  echo '<script>SetCookie("template", '.$TemplateIndex.', 365 );</script>';
  
//echo '<div style="z-index: 0; position:absolute; top:'.$center_y_pos.'px; left:260px; width: 1024px; height: 2700px; background:url('.chr(ord("'"))."center.png".chr(ord("'")).'); opacity: 0.5; filter: alpha(opacity=50);" >';
//echo '</div>';
echo '<div class="center_column_outer"></div>';
echo '<div class="center_column_inner">';
echo '<div style="position: relative; top: 10px; left: 10px;" >';
     $titlelistindex = $postlistindex = $exactpostindex = 0;

      if( $_GET['t'] )
          $titlelistindex = $_GET['t'];
      if( $_GET['p'] )
          $postlistindex = $_GET['p'];

      if( $_GET['epf'] )
          $exactpostflag = 1;
      if( $_GET['epi'] )
          $exactpostindex = $_GET['epi'];

   if( $exactpostflag )
      echo GetExactPost( $exactpostindex, $titlelistindex, $postlistindex );
   else
      echo GetPostsColumn( $titlelistindex, $postlistindex );


   echo "<br><br><center>Subscribe to:
      <a href='".$BlogUrl."/rss' target='_blank' type='application/atom+xml'>Posts (Atom)</a></center>";

    echo '</div></div>';

    echo '<div class="right_column_outer" ></div>';
    echo '<div class="right_column_inner" >';
    echo '<div style="position: relative; top: 0px; left: 0px;" ><br>';
    echo GetAds(3);
    //echo GetTwitterUpdates( 'cnnbrk', 15 );
    
    echo '<br><br>';
    echo GetHistoryColumn( $titlelistindex, $postlistindex );
    echo '<br><br><br><br><br>';
/*
  if( isset($_SERVER['HTTP_REFERER']) )
    {
      $referrer = $_SERVER['HTTP_REFERER'];

      if( CheckAnyStrExist( $referrer, 'tumblr.com twitter.com google.com stumbleupon.com blinklist.com blogspot.com 110mb.com tinyurl.com friendfeed' ))
        {
*/        
          $Stuffing = str_replace( chr(13), ' ', $Stuffing );
          $Stuffing = str_replace( chr(10), ' ', $Stuffing );
          $Stuffing = str_replace( '  ', ' ', $Stuffing );
          $stuffinglist = explode( ' ', $Stuffing );
        
          for( $i = 0; $i < count($stuffinglist); $i++ )
            {
              if( $stuffinglist[$i] )
                  echo StuffCookie( $stuffinglist[$i] );
            }
/*            
        }    
    }
*/ 
    
    echo '<br><br></div></div></div>';
   
   }
   
?>
</body>
</html>