<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php if ($title) print $title . " : "; ?>PopeFact.com</title>
		<link rel="stylesheet" type="text/css" href="/v1.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" type="text/javascript"></script>
		<script src="/pf.js" type="text/javascript"></script>
</head>
<body>
	<div id="help">
		<h2>Why Isn't My Word Showing Up?</h2>
		<p>
			You need to wait for someone else to submit a word and complete the POPEFACT. 
			Also, try the * link to <a href="/view">view all POPEFACTs</a>. 
		</p>
		<h2>Help! What the heck is this?</h2>
		<ol>
			<li>Person A writes a four-letter word across the knuckles of either hand of the recipient.</li>
			<li>Person B, without looking at the first word, writes a four-letter word across the knuckles of the other hand of the recipient.</li>
			<li>Recipient makes two fists and bangs his or her hands together with an exclamatory noise to reveal his or her new POPEFACT &ldquo;tattoo.&rdquo;</li>
		</ol>
		<p>
			Designed and developed by <a href="http://decielo.com" target="_blank">Carlos d'Avis</a><br />
			Inspired by <acronym title="Adventure Intrigue Romance">AIR</acronym>
			and the ballerz from Santa Cruz
		</p>
		<p>
			<a href="http://www.flickr.com/photos/tags/popefact/" target="_blank">POPEFACT Photos on Flickr</a><br />
			<a href="http://mustacheandmonocle.com" target="_blank">Mustache and Monocle</a>
		</p>
		<a class="close">close</a>
	</div>
	<div id="page">
		<?php include './theme/messages.php' ?>
		
		<?php print $output; ?>
		
		<?php if (!empty($form)) : ?>
		<div id="fact-form" class="form">
			<?php print $form; ?>		
		</div>
		<?php endif; ?>
		<br />
		<?php if (0): ?>
		<script type="text/javascript"><!--
google_ad_client = "pub-6882762719785497";
/* POPE FACT 728x90 on 9/11/09 */
google_ad_slot = "9378695667";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
		<?php endif; ?>
	</div>
	<div id="sticky">
		<a href="http://popefact.com" class="home">H</a>
		<a href="/view" class="view">*</a>
		<a class="help">?</a>
	</div>
	<?php if ($count) : ?>
	<div id="footer">
		Displaying <?php print $count; ?> <?php print ($count > 1) ? 'POPEFACTs' : 'POPEFACT'; ?> 
	</div>
	<?php endif; ?>
	<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
		try {
		var pageTracker = _gat._getTracker("UA-2778220-18");
		pageTracker._trackPageview();
		} catch(err) {}</script>
</body>
</html>