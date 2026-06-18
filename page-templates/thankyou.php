<?php 
/*
Template name: Thank You Page
*/
get_header();
?>

<div class="main_body ">
	<div class="container">
	
		<div class="row">
			<div class="col-md-12"> 
				<h1 class="page_title"><?php the_title();?></h1>  
                 <div class="thankyou_content"> <?php the_content();?> </div>
			</div>
		</div>
	</div>
</div>
<?php 
get_footer();
?>