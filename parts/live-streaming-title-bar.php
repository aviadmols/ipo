<?php 

$title = get_field('title');
$description = get_field('description');

if($title || $description):

?>

<div class="container live-streaming-title-bar">

		<div class="row subscription">
			
			<?php if($title): ?>
			<div class="col-md-4 col-sm-4 col-xs-12 mobile_section">
				<h2> <?php echo $title; ?> </h2>
			</div>
			<?php endif; ?>
			
			<?php if($description): ?>
			<div class="col-md-8 col-sm-8 col-xs-12 stream_detail">
				<?php echo $description;?>				
			</div>
			<?php endif; ?>
			
			<?php if($title): ?>
			<div class="col-md-4 col-sm-4 col-xs-12 desktop_section">
				<h2> <?php echo $title; ?> </h2>
			</div>	
			<?php endif; ?>
			
		 </div>			
	
 </div>
 
 <?php endif; ?>