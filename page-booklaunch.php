<?php
/*
Template Name: Booklaunch Page
*/
?>
<meta name="HandheldFriendly" content="True">
<meta name="MobileOptimized" content="320">
<meta name="viewport" content="user-scalable=no, width=device-width, minimum-scale=1, maximum-scale=1">

<iframe width="100%" height="100%" src="<?php echo get_post_meta( get_the_id(), 'booklaunch_page_url', true ) ?>?wp=1"></iframe>
<style>
	html{margin: 0px; min-height: 100%; height: 100%;}
	body{margin: 0px; min-height: 100%; height: 100%;}
	iframe{border: none;}
</style>