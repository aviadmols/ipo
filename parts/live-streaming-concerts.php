<?php 

$concerts= get_field('concerts');
$lang = apply_filters( 'wpml_current_language', NULL );
if($lang == 'en'){
    $read_more = 'Read More';
    $read_less = 'Close';
} else {
    $read_more = 'קראו עוד';
    $read_less = 'סגירה';
}

if($concerts) : ?>

<?php foreach($concerts as $item) : ?>

    <div class="container live_streamed_video">		
    	<div class="row">			
        	 <div class="col-md-12 col-sm-12 col-xs-12">				
        
        	  <div class="row subscription">		
        		<div class="col-md-8 col-sm-8 col-xs-12 lvideo">
        		
        			<?php 
        			$mainvideo = $item['featured_embed'];
        			if (!empty($mainvideo)) {  /* ?>
        			
        				<iframe width="750" height="400" src="<?php echo $mainvideo.'?rel=0&loop=1'; ?>" frameborder="0" allowfullscreen>
        				</iframe>	
        				
        			 <?php */ 
        			 
        			echo get_video($mainvideo,array(
        				'width' => 750,
        				'height' => 420,
        			)); 
        
        			 } else { 
        				$mainimage = get_field('gallery_image');
        				echo "<img src='".$mainimage."' />";
        			}
        			?>
        
        
        		</div>
        
        		
        
        		<div class="col-md-4 col-sm-4 col-xs-12 n-1">						
        		
            		<?php 
            
            			$brdtail = $item['short_description'];
            			$concert = $item['concert'];
            			$chapters = $item['chapters'];
            			
            			if($concert){
            			    echo '<h3>'.$concert.'</h3>';
            			}
            			
            			if($chapters){
            			    echo '<h3>'.$chapters.'</h3>';
            			}
            
            			echo $brdtail;				
            
            		?>		
            		

        		 </div>				
        
        	   </div>	
        		
        		<?php if($item['bottom_content']): ?>
        			<div class="row extra-live-streaming-text text-under-video">
        				
        				<div class="col-md-12 col-sm-12 col-xs-12 has-readmore">			
                            <div class="description">
            					<?php 
            					    $bottom_content = $item['bottom_content'];
            					    echo $bottom_content;
            					?>
        					</div>
        					
        					<div class="concert_more rtl"> 
                                <a class="read_more" href="javascript:void(0)" role="link">
                                    <?php echo $read_more; ?>
                                <i class="fa fa-angle-left" aria-hidden="true"></i>
                                </a> 
                                <a class="read_less" href="javascript:void(0)" role="link">
                                    <?php echo $read_less; ?>
                                <i class="fa fa-angle-left" aria-hidden="true"></i>
                                </a> 
                            </div>
        					
        				</div>	
        				
        			</div>
        		<?php endif; ?>
        
        	</div>			
        
        </div>	
    
    </div>

<?php endforeach;?>

<?php endif;?>