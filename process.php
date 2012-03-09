<?php

include_once "config.php";

include 'Dropbox/Dropbox/autoload.php';
include_once "Markdown/markdown.php";

$oauth = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);
$dropbox = new Dropbox_API($oauth);
$tokens = $dropbox->getToken($dbUsername, $dbPassword); 
$oauth->setToken($tokens);

$a_images = array();
$a_files = array();

session_start();

function getListOfPosts() {
	global $blogDir;
	global $dropbox;
	global $a_images;
	global $a_files;



	$a_metadata = $dropbox->getMetaData($blogDir, true);

	$a_ret = array();
	
	foreach ($a_metadata['contents'] as $metadata) {
		$item = $metadata['path'];
		$ts = strtotime($metadata['modified']);
		
		$a_ret []= array("path" => $item, "ts" => $ts, "size" => $metadata['bytes']);
		
		if (stristr($item, ".jpg")) $a_images []= basename($item);
		elseif (!stristr($item, ".mdown")) $a_files []= basename($item);
		
	}

	return $a_ret;
}

function mdbHash($filename) {
	return md5($filename);
}

function downloadFilesAndTransform(&$list) {
	global $dropbox;
	global $a_images;
	global $a_files;
	global $rssLink;

	$a_map = array();
	
	
	if ($handle = opendir('content')) {
	    while (false !== ($file = readdir($handle))) {
			if (($file==".") || ($file=="..")) continue;
			// Check if file is still in the dropbox
	        $filenameSrv = basename($file);
			$found = false;
			foreach ($list as $item) {
				$filenameDb = basename($item['path']);
				if ($filenameDb == $filenameSrv) $found = true;
				if (basename($filenameDb, ".mdown")==basename($filenameSrv, ".html")) $found = true;
				if (stristr($filenameSrv, "_thumb.jpg")) {
					// filename is a thumb --> check if the original file still exists
					$filenameSrvReal = str_replace("_thumb.jpg", ".jpg", $filenameSrv);
					if ($filenameDb == $filenameSrvReal) $found = true;					
				}
			}
			// Remove files that are on your server but not in the dropbox
			if ((!$found) && (!stristr($file, "-ignore"))) {
				echo "...Deleting " . $file . "<br/>\n";
				unlink("content/" . basename($file));
				$filename = basename($file);
				unset($a_map[$filename]);
				unset($a_map[mdbHash($filename)]);
			}
			if (stristr($file, "-ignore")) {
				// file might not be in dropbox
				if (!in_array(basename($file), $a_files)) $a_files []= basename($file);
			}
	    }
		closedir($handle);
	}
	
	
		
	foreach ($list as &$item) {
		$filename = basename($item['path']);
		if (stristr($filename, ".mdown")) $filename = basename($filename, ".mdown") . ".html";
		$a_map[$filename] = mdbHash($filename);
		$a_map[mdbHash($filename)] = $filename;
		$item['id'] = mdbHash($filename);
		
		
		if (stristr($item['path'], ".mdown")) {
			echo "...Downloading " . $item['path'] . "<br/>\n";			
			$filename = "content/" . basename($item['path'], ".mdown") . ".html";

			$mdown = $dropbox->getFile($item['path']);
			
			$lines = explode("\n", $mdown);
			$firstLine = $lines[0];
			$a_config = array();
			if (substr($firstLine, 0, 2)=="::") {
				// :: Syntax allows configuration
				$config = substr($firstLine, 2);
				$configValues = explode(",", $config);
				foreach ($configValues as $configValue) {
					$kv = explode("=>", $configValue);
					$a_config[strtolower($kv[0])] = $kv[1];
				}
				
				// do other stuff
				$mdown = str_replace($firstLine, "", $mdown);
			}
			
			// replace local images with thumbnails in sourcecode
			foreach ($a_images as $image) {
				$imLink = "($image)";
				$imFilename = str_replace(".jpg", "_thumb.jpg", $image);
				$newImLink = "(" . $rssLink . "/content/" . $imFilename .")";
				$mdown = str_replace($imLink, $newImLink, $mdown);
			}
			// replace local files with real path
			foreach ($a_files as $file) {
				$fLink1 = "($file)";
				$newfLink1 = "($rssLink/content/$file)";
				$mdown = str_replace($fLink1, $newfLink1, $mdown);
				
				$fLink2 = "<$file>";
				$newFlink2 = "<$rssLink/content/$file>";
				$mdown = str_replace($fLink2, $newFlink2, $mdown);
			}
			
			$html  = Markdown($mdown);
			
			$searchH1 = "/<h1>.*<\/h1>/";
			$a_matchesH1 = array();
			preg_match($searchH1, $html, $a_matchesH1);
			$title = strip_tags($a_matchesH1[0]);
			$html = "<title>" . $title . "</title>\r\n" . $html;


			// parse config array
			foreach ($a_config as $key => $value) {
				if ($key == "pretty") {
					$a_map[$value] = basename($item['path'], ".mdown") . ".html";
					$item['id'] = $value;
				}
				if ($key == "author") {
					$item['author'] = $value;
				}
			}

			file_put_contents($filename, $html);		
		}
		else if (!stristr($item['path'], "-ignore")) {
			$filename = "content/" . basename($item['path']);
			if (!(file_exists($filename) && (filesize($filename)==$item['size']))) {
				// file changed
				echo "...Downloading " . $item['path'] . "<br/>\n";				
				file_put_contents($filename, $dropbox->getFile($item['path']));
				
				// check if thumbnail needs to be created
				if (stristr($filename, ".jpg")) {
					createThumbnail($filename);
				}
			}
		}
	}
	
	file_put_contents("content/map.json", json_encode($a_map));
}

function createThumbnail($origImageFile) {
	global $thumbWidth;

	// load image and get image size
	$img = imagecreatefromjpeg($origImageFile);
	$width = imagesx( $img );
	$height = imagesy( $img );

	// calculate thumbnail size
	$new_width = $thumbWidth;
	$new_height = floor( $height * ( $thumbWidth / $width ) );

	// create a new temporary image
	$tmp_img = imagecreatetruecolor( $new_width, $new_height );

	// copy and resize old image into new image 
	imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

	// save thumbnail into a file
	$thumbFile = str_replace(".jpg", "_thumb.jpg", $origImageFile);
	echo "...Creating thumbnail $thumbFile <br/>\n";
	imagejpeg( $tmp_img, $thumbFile );
}

function createRssFromList($list) {
	
	uasort($list, "cmpEntries");
	
	global $rssTitle;
	global $rssLink;
	global $rssDesc;
	global $rssLang;
	
	$rssfeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
	$rssfeed .= '<rss version="2.0">' . "\r\n";
	$rssfeed .= "\t". '<channel>' . "\r\n";
	$rssfeed .= "\t\t". '<title>' . $rssTitle . '</title>' . "\r\n";
	$rssfeed .= "\t\t".'<link>'. $rssLink .'</link>' . "\r\n";
	$rssfeed .= "\t\t".'<description>' . $rssDesc . '</description>' . "\r\n";
	$rssfeed .= "\t\t".'<language>' . $rssLang . '</language>' . "\r\n";

	
	foreach ($list as $item) {
		$filename = "content/" . basename($item['path'], ".mdown") . ".html";
		if (stristr($filename, "published")) {
			$content = file_get_contents($filename);
			$searchH1 = "/<h1>.*<\/h1>/";
			$a_matchesH1 = array();
			preg_match($searchH1, $content, $a_matchesH1);
			$title = strip_tags($a_matchesH1[0]);
			
			$searchP = "/<p\b[^>]*>(.*?)<\/p>/msi";
			$a_matchesP = array();
			preg_match($searchP, $content, $a_matchesP);
			
			$description = strip_tags($a_matchesP[0]);
			
			
			$link = $rssLink . "/index.php#!" . $item['id'];


			$rssfeed .= "\t\t\t". '<item>' . "\r\n";
	        $rssfeed .= "\t\t\t".'<title>' . $title . '</title>' . "\r\n";
	        $rssfeed .= "\t\t\t".'<description>' . $description . '</description>' . "\r\n";
	        $rssfeed .= "\t\t\t".'<link>' . $link . '</link>' . "\r\n";
	
			$rssfeed .= "\t\t\t<guid isPermaLink='false'>" . $item['id'] . "</guid>\r\n";
	
	        $rssfeed .= "\t\t\t".'<pubDate>' . date("D, d M Y H:i:s O", $item['ts']) . '</pubDate>' . "\r\n";
	
			if ($item['author']) {
				$rssfeed .= "\t\t\t<author>" . $item['author'] . "</author>\r\n";
			}
	        $rssfeed .= "\t\t\t".'</item>' . "\r\n\r\n";
		}
	}
	
	$rssfeed .= "\t".'</channel>' . "\r\n";
	$rssfeed .= '</rss>' . "\r\n";
	
	file_put_contents("content/feed.rss", $rssfeed);
}

function cmpEntries($a, $b) {
	if ($a['ts'] == $b['ts']) return 0;
	else return ($a['ts'] > $b['ts']) ? -1 : 1;
}

function generateSitemapTxt($list) {
	global $rssLink;
	
	$sitemap = "$rssLink/\r\n";
	foreach ($list as $item) {
		if (stristr($item['path'], "-published.mdown") || stristr($item['path'], '-google.mdown')) {
			// only put public markdown articles into sitemap
			$sitemap .= $rssLink . "/index.php#!" . $item['id'] . "\r\n";
		}
	}
	file_put_contents("content/sitemap-xml.txt", $sitemap);
}

echo "1. Getting all posts<br/>\n";
$list = getListOfPosts();
echo "2. Downloading posts and transforming them<br/>\n";
downloadFilesAndTransform($list);
echo "3. Creating RSS<br/>\n";
createRssFromList($list);
echo "4. Creating TXT-Sitemap<br/>\n";
generateSitemapTxt($list);


?>
