<?php 

$description = get_field('description');

if($description):

?>

<div class="container live-streaming-title-bar">

		<div class="row subscription">
			
			<div class="col-md-12 col-xs-12 stream_detail has-readmore">
			    
			    <div class="description">
			    	<?php echo $description;?>	
				</div>
				
				<div class="concert_more rtl"> 
                    <a class="read_more" href="javascript:void(0)" role="link">קראו עוד<i class="fa fa-angle-left" aria-hidden="true"></i>
                    </a> 
                    <a class="read_less" href="javascript:void(0)" role="link">לקרוא פחות<i class="fa fa-angle-left" aria-hidden="true"></i>
                    </a> 
                </div>
			</div>

		 </div>			
	
 </div>
 
 <?php endif; ?>