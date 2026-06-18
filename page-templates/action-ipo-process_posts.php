<?php 
/*
* Template Name: ACTION - IPO POSTS PROCESS
*/


get_header();


?>
<style>
	.site > *:not(.site-content){
		display:none;
	}
</style>
<div style="margin: 2%; padding: 2%; max-width: calc(100vw - 4% - 4%);">
	
	<form action="/action-process-posts" method="post">

		<input type="text" name="amount" placeholder="number of posts" value="10" />
		<input type="text" name="offset" placeholder="offset" value="0" />
		<input type="text" name="times" placeholder="times" value="999" />
		<input id="ajax-posts-process-start" type="submit" value="Start">

	</form>

	<div class="ajax-posts-process-msg-log" style="color: green; width: 100%; margin-bottom: 50px; min-height: 20vh; color: white; background-color: black;">

	</div>

	
</div>


<?php 

get_footer();