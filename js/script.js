function loadFeed() {
	$('#disqus_thread').css('display', 'none');
	$('.dsq-brlink').css('display', 'none');
	jQuery.getFeed({
	       url: 'content/feed.rss',
	       success: function(feed) {
				$('#blogtitle').html(feed.title);
				$('title').html(feed.title);				
				$('#bloglink').attr('href', feed.link);

				for(var i = 0; i < feed.items.length; i++) {
	                var item = feed.items[i];

					url = "index.php#!" + item.id;
					html = "<a href='" + url + "'>"; 
					html += "<h2>" + item.title + "</h2>"
					html += "</a>";
	                html += '<div class="updated">' + item.updated;					
					html += '</div>';

	                html += '<div class="description">'+ item.description + ' &hellip;</div>';

					html += "<a href='" + url + "' class='nav_link'>&rarr; Read on</a>";
					
					html += "<div class='index-meta'>";
					
					html += "</div>";
					
					$('#main').append(html);
					
				}
	       }
	});	
}

function loadWithId(mdbHash) {
	$.getJSON('content/map.json', function(data) {

	  $.each(data, function(key, val) {
		if (key==mdbHash) {
			loadItem("content/" + val);
			return;
			//iloveu<3!!!
		}
		
	  });

	});
}

function loadItem(url) {
	jQuery.getFeed({
	       url: 'content/feed.rss',
	       success: function(feed) {
				$('#blogtitle').html(feed.title);								
				$('#bloglink').attr('href', feed.link);
				$('#main').load(url, function() {
					for (var i=0; i < feed.items.length; i++) {
		                var item = feed.items[i];
						
						// #!foo ==? item.id
						if (window.location.hash.substring(2)==item.id) {
							$('title').html(item.title + " - " + feed.title);
							$('#main').append('<div class="updated">' + item.updated + '</div>');
						}
					}
					
					
					html = "<a href='" + feed.link + "' class='nav_link'>&larr; Back</a>";
					$('#main').append(html);
					
					$('#main img').each(function(e) {
						src = $(this).attr("src");
						if (src.indexOf("_thumb.jpg")!=-1) {
							newsrc = src.replace("_thumb.jpg", ".jpg");
							$(this).wrap("<a title='" + $(this).attr("alt") +"' class='images' rel='gallery' href='" + newsrc + "'/>");
							$(this).addClass("thumbnail");
						}
					});
					$("a.images").fancybox();
					
					$('#main a').each(function(e) {
						src = $(this).attr("href");
						if (src.indexOf(".mp4")!=-1) {
							$(this).wrap("<video src='" + src + "' />");
						}
					});
					$('video,audio').mediaelementplayer();
				});
				
				
				
				
				$('#main').mouseenter(function() {
					$('#disqus_thread').animate({opacity : 0}, "slow");	
				});

				$('#main').mouseleave(function() {
					$('#disqus_thread').animate({opacity : 1}, "slow");		
				});
				
	       }
	});
}


function dispatch() {
	if (window.location.hash) loadWithId(window.location.hash.substring(2));
	else loadFeed();
}

dispatch();



$(window).bind('hashchange', function() {
	if (window.location.href.indexOf("index.php#!")!=-1) {
		mdbHash = window.location.href.split("#!")[1];
		loadWithId(mdbHash);
	}
});