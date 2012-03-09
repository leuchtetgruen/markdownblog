<?php
include "config.php";

// For the google crawler
if (isset($_GET['_escaped_fragment_'])) {
	$hash = $_GET['_escaped_fragment_'];
	if ($hash=="") {
		$xml = simplexml_load_file("content/feed.rss");
		echo "<h1>" . $xml->channel->title . "</h1>\t\n";
		foreach ($xml->channel->item as $item) {
			echo "<h2><a href='" . $item->link . "'>". $item->title . "</a></h2><br/>\r\n";
			echo "<p>" . $item->description . "</p>\r\n";
		}
		exit;
		
	}
	else {
		$a_map = json_decode(file_get_contents("content/map.json"), true);
		$filename = $a_map[$hash];

		echo file_get_contents("content/" . $filename);
		exit;
	}
}

?>

<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- Consider adding an manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta name="fragment" content="!">
  <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/b/378 -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title></title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

  <!-- CSS: implied media=all -->
  <!-- CSS concatenated and minified via ant build script-->
  <link rel="stylesheet" href="css/style.css">
  <link href='http://fonts.googleapis.com/css?family=Arvo' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Caudex' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />
  <link rel="stylesheet" href="mediaelement/mediaelementplayer.css" /></code>
  <!-- end CSS-->

  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

  <!-- All JavaScript at the bottom, except for Modernizr / Respond.
       Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries
       For optimal performance, use a custom Modernizr build: www.modernizr.com/download/ -->
  <script src="js/libs/modernizr-2.0.6.min.js"></script>

	<link rel="alternate" type="application/rss+xml" title="RSS" href="content/feed.rss" />
</head>

<body>

  <div id="container">
	<header>
    	<a id="bloglink" href="#"><h1 id="blogtitle"></h1></a>
	</header>
	
    <div id="main" role="main">

    </div>

	<hr/>

	<div id="disqus_thread"></div>
	<script type="text/javascript">
	    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
	    var disqus_shortname = '<?php echo $disqusName; ?>'; // required: replace example with your forum shortname

	    /* * * DON'T EDIT BELOW THIS LINE * * */
	    (function() {
	        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
	        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
	        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
	    })();
	

	</script>
	<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
	<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
	
	
  </div> <!--! end of #container -->
  <footer>
		Created with <a href="http://leuchtetgruen.de/blog/index.php#!whatismarkdownblog">markdownblog</a> - <a href="content/feed.rss">RSS</a>
  </footer>

  <!-- JavaScript at the bottom for fast page loading -->

  <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/libs/jquery-1.6.2.min.js"><\/script>')</script>
  <script type="text/javascript" src="js/libs/jquery.jfeed.pack.js"></script>
  <script type="text/javascript" src="js/libs/jquery-color.js"></script>
  <script type="text/javascript" src="fancybox/jquery.fancybox-1.3.4.pack.js"></script>

  <script type="text/javascript" src="mediaelement/mediaelement-and-player.min.js"></script>
  <!-- scripts concatenated and minified via ant build script-->
  <script defer src="js/plugins.js"></script>
  <script defer src="js/script.js"></script>
  <!-- end scripts-->

	



  <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 7 ]>
    <script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
    <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
  <![endif]-->  
</body>
</html>
