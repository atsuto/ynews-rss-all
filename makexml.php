<?php
$filecon = file_get_contents( "http://dailynews.yahoo.co.jp/fc/" );
preg_match_all("/<li><a href=\"\/fc\/([a-zA-Z0-9_\/]+)\">(.+)<\/a>/", $filecon, $matches);

$ary = array_unique($matches[1]);

$i = 0;
$link;
$title;
$img;
$desc;
foreach($ary as $value){
	$path = "http://dailynews.yahoo.co.jp/fc/" . $value;
	$filecon2 = file_get_contents( $path );
	preg_match("/<h3><a href=\"(http.+)\">(.+)<\/a>/", $filecon2, $matches2);
	$link[$i] = $matches2[1];
	$title[$i] = $matches2[2];
	//echo $link[$i] . "<br>\n";
	//echo $title[$i] . "<br>\n";
	
	$isimg = preg_match("/<td class=\"image\">.+<img src=\"(.+)\" alt/", $filecon2, $matches3);
	
	if( $isimg != 0 ){
		$img[$i] = $matches3[1];
		//echo $img[$i] . "<br>\n";
	}else{
		$img[$i] = "";
	}
	///// desc ////
	if( preg_match("/sportsnavi\.yahoo\.co\.jp/", $link[$i]) || preg_match("/netallica\.yahoo\.co\.jp/", $link[$i]) ){
		$desc[$i] = getNetaSpo($link[$i]);
	}else{
		$desc[$i] = getCon($link[$i]);
	}
	//exit;

	//echo "<br>\n";
	
	//if($i == 5){break;};
	
	$i++;
}
//echo "break";
//exit;//stopper

/*for( $j=1 ; $j<sizeof($work3) ; $j++ ){
		$body_con = file_get_contents( $url[$j] );
		if( preg_match( "/\<div class=\"ymuiContainerNopad clearFix\"\>/", $body_con ) ){
			$work8 = explode( "<div class=\"ymuiContainerNopad clearFix\">", $body_con );
			$work9 = explode( "</div>", $work8['1'] );
		}else{
			$work8 = explode( "<span class=\"yjMt\">", $body_con );
			$work9 = explode( "</span>", $work8['1'] );
		}
		if( preg_match( "/\<div class=\"ynDetailPhoto\"\>/", $work9['0'] ) ){
				$work9_m = $work9['1'];
		}else{
				$work9_m = $work9['0'];
		}
		$work9_3 = preg_replace( "/\<a href=.+\<\/a\>/", "", $work9_m );
		$work10 = $work9_2['0'];
		$work10 = str_replace( "<!--- interest_match_relevant_zone_start --->", "", $work10 );
		$work10 = str_replace( "<!--- interest_match_relevant_zone_end --->", "", $work10 );
		$work10 = str_replace( "<br>", "", $work10 );
		$work10 = str_replace( "<", "", $work10 );
		$work10 = str_replace( ">", "", $work10 );
		$work10 = htmlspecialchars( $work10 );
		$body[$j] = $work10;
}*/
$xml_con = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml_con .= "<rss version=\"2.0\" xmlns:blogChannel=\"http://backend.userland.com/blogChannelModule\">\n";
$xml_con .= "<channel>\n<title>Yahoo! News Topics</title>\n<link>http://dailynews.yahoo.co.jp/fc/</link>\n
<description>Yahoo! JAPAN</description>\n
<language>ja</language>\n";
for( $k=1 ; $k<sizeof($link) ; $k++ ){
		$xml_con .= "<item>\n";
		$xml_con .= "<title>" . $title[$k] . "</title>\n";
		$xml_con .= "<link>" . $link[$k] . "</link>\n";
		if( $img[$k] != "" ){
			$imgtag = "<img src=\"" . $img[$k] . "\"><br>";
		}else{
			$imgtag = "";
		}
		$xml_con .= "<description><![CDATA[" . $imgtag . $desc[$k] . "]]></description>\n";
		$xml_con .= "</item>\n";
}
$xml_con .= "</channel>\n</rss>\n";
$xml_con = mb_convert_encoding( $xml_con, "UTF-8", "EUC-JP" );
//echo $xml_con;
//exit;
file_put_contents( "/home/atsutoms/www/news/rss.xml", $xml_con );
chmod("/home/atsutoms/www/news/rss.xml", 0644);

//// function ////
function getCon($url){
    $body_con = file_get_contents( $url );
    if( preg_match( "/\<div class=\"ynDetailPgraphWrap clearFix\"\>/", $body_con ) ){
        $work8 = explode( "<div class=\"ynDetailPgraphWrap clearFix\">", $body_con );
        $work9 = explode( "</div>", $work8['1'] );
    }else{
        $work8 = explode( "<span class=\"yjMt\">", $body_con );
        $work9 = explode( "</span>", $work8['1'] );
    }   
    if( preg_match( "/\<div class=\"ynDetailPhoto\"\>/", $work9['0'] ) ){
        $work9_m = $work9['1'];
    }else{
        $work9_m = $work9['0'];
    }
	$str = $work9_m;
	$str = str_replace( "<br><br>", "<br>", $str );
	$str = str_replace( "<br><br>", "<br>", $str );
	$str = str_replace( "<br>\n<br>", "<br>", $str );
	//$str = str_replace( "<br>", "", $str );
	return $str;
}

function getNetaSpo($url){
$filecon = file_get_contents( $url );

$flag;
if( preg_match( "/netallica\.yahoo\.co\.jp/", $url ) ){
	$sep1 = "<div id=\"ymarkable\">";
	$sep2 = "</div>";
	$flag = "neta";
}elseif( preg_match( "/sportsnavi\.yahoo\.co\.jp/", $url ) ){
	$sep1 = "<p class=\"user1\">";
	$sep2 = "</p>";
	$flag = "spo";
}else{
	//error go next roop
	$sep1 = "hoge";
	$sep2 = "foo";
	$flag = "error";
	$filecon = "";
}
$exp1 = explode( $sep1, $filecon );
$exp2 = explode( $sep2, $exp1[1] );
$str = $exp2[0];

if( $flag == "neta" ){
	$str = mb_convert_encoding( $str, "EUC-JP", "UTF-8" );
}

$str = str_replace( "<br><br>", "<br>", $str );
$str = str_replace( "<br />\n<br />", "<br>", $str );
return $str;
}
?>
