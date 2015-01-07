<?php
/*
Template Name: Booklaunch Page
*/
?>

<iframe width="100%" height="100%" src="<?php echo get_post_meta( get_the_id(), 'booklaunch_page_url', true ) ?>?wp=1"></iframe>
<style>
	body{margin: 0px;}
	iframe{border: none;}
</style>