<?php 
/*
* Template Name: History Title Page
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
$history = get_field('IPO_history');
?>

<div class="main_body history_tmp">
  <?php if(!empty($bannerImage)){ ?>
    <div class="row orckestra_banner">
		
			<div class=" col-md-12 col-sm-12 col-xs-12 banner_image"> 
				<img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"/> 
			</div>
		
    </div>
    <?php } ?>
  
  <div class="container">
    
    
	<h1 class="page_title"><?php echo get_the_title(); ?></h1>
    <div class="row history_body">
    <div class=" col-md-4 col-sm-4 col-xs-12 sidebar_mobile">
	<?php  $thecontent = get_the_content();?>
	  <?php  if(empty($thecontent)) { ?>
        <div class="panel-group" id="accordion">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" aria-expanded="true" class=""><?php echo get_the_title(); ?></a> </h4>
            </div>
            <div id="collapse1" class="panel-collapse collapse in" aria-expanded="true" style="">
              <div class="panel-body">
              
	    <?php $j = 0; ?>
	    <?php if( get_field('two_column_content') ):while( has_sub_field("two_column_content") ):if(get_row_layout() == "2_column_content"):?>
		<a class="collapsed" href="<?php echo "#div_".$j; ?>"><?php echo the_sub_field('row_title'); ?></a>
		<?php $j++; ?>
		<?php endif; endwhile; endif; ?>
			  
              </div>
            </div>
          </div>
         <?php $fields = get_field('sidebar_page');  
		  if( $fields ){ 
		   foreach($fields as $his){
		  ?>
         <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a href="<?php echo get_permalink($his->ID); ?>" class="collapsed"><?php echo $his->post_title; ?></a> </h4>
            </div>
          </div>
    <?php } wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
<?php  } ?> 
        </div><?php  } ?>
      </div>
	  <?php  $thecontent = get_the_content();?>
	  <?php // if(empty($thecontent)) { ?>
		<div class=" col-md-9 col-sm-9 col-xs-12 history_post">
	  <?php // } else { ?>
		<!--<div class=" col-md-12 col-sm-12 col-xs-12 history_post">-->
	  <?php  //} ?>
       
       <?php $i = 0; ?>
	    <?php if( get_field('two_column_content') ):while( has_sub_field("two_column_content") ):if(get_row_layout() == "2_column_content"):?>   
		<h3 class="history_title" id="<?php echo "div_".$i; ?>"> <?php echo the_sub_field('row_title'); ?> </h3>
		   <div class="col-md-12 col-sm-12 col-xs-12 first"> 
              <?php //echo $his['history_description']; ?>
			  <?php echo the_sub_field('column_left_part')?>
           </div>
           
		    <?php $i++; ?>
		    <?php endif; endwhile;  ?>
             <?php  else :  ?>
					<div class=" col-md-12 col-sm-12 col-xs-12">
							<?php  echo the_content();?>
					</div>
					
			<?php endif;?>
      </div>
	  
		<?php  if(empty($thecontent)) { ?>
      <div class=" col-md-3 col-sm-3 col-xs-12 sidebar_desktop">
	    
        <div class="panel-group" id="accordion">
      
	   <div class="panel panel-default">
          <div class="panel-heading">
             <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapse" aria-expanded="true" class=""><?php echo get_the_title(); ?></a> </h4>
            </div><?php } ?>
			
            <div id="collapse" class="panel-collapse collapse in" aria-expanded="true" style="">
              <div class="panel-body"> 
				<?php $j = 0; ?>
				<?php if( get_field('two_column_content') ):while( has_sub_field("two_column_content") ):if(get_row_layout() == "2_column_content"):?>
				<a class="collapsed" href="<?php echo "#div_".$j; ?>"><?php echo the_sub_field('row_title'); ?></a>
				<?php $j++; ?>
				<?php endif; endwhile; endif; ?>
			  
              </div>
            </div>
          </div>
		  <?php $fields = get_field('sidebar_page');  
		  if( $fields ){ 
		   foreach($fields as $his){
		  ?>
         <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a href="<?php echo get_permalink($his->ID); ?>" class="collapsed"><?php echo $his->post_title; ?></a> </h4>
            </div>
          </div>
    <?php } wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
        </div>
      </div>
<?php  } ?> 	  
    </div>
  </div>
</div>

<?php get_footer(); ?>