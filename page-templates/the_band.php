<?php 
/*
* Template Name: The Band Page
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()),'full');
$history = get_field('IPO_history');
?>

<div class="main_body history_tmp">
  <div class="container">
    <h1 class="page_title"><?php echo get_the_title();?></h1>
    <?php if(!empty($bannerImage)){ ?>
    <div class="row orckestra_banner">
      <div class=" col-md-12 col-sm-12 col-xs-12 banner_image">
	  <img src="<?php echo $bannerImage[0]; ?>" /> </div>
    </div>
    <?php } ?>
    <div class="row history_body">
    <div class=" col-md-4 col-sm-4 col-xs-12 sidebar_mobile">
        <div class="panel-group" id="accordion">
           
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a href="<?php echo home_url().'/history'; ?>">
              <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					History of the Philharmonic
							
			<?php }	 else {	?>
					
					  ההיסטוריה של הפילהרמונית
						
				<?php }?>
            </a> </h4>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a href="<?php echo home_url().'/musical-manager'; ?>" class="collapsed" >
              <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Music Director Zubin Mehta
							
			<?php }	 else {	?>
					
					 מנהל מוזיקלי זובין מהטהת
						
				<?php }?>
               </a> </h4>
            </div>
          </div>
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
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a href="<?php echo home_url().'/gneral-information'; ?>">
              <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					General Information
							
			<?php }	 else {	?>
					
					 מידע כללי
						
				<?php }?>
               </a> </h4>
            </div>
          </div>
        </div>
      </div>
      <div class=" col-md-8 col-sm-8 col-xs-12 history_post"> 
       
	   <?php $i = 0; ?>
	    <?php if( get_field('two_column_content') ):while( has_sub_field("two_column_content") ):if(get_row_layout() == "2_column_content"):?>   
		<h3 class="history_title" id="<?php echo "div_".$i; ?>"> <?php echo the_sub_field('row_title'); ?> </h3>
		   <div class="col-md-12 col-sm-12 col-xs-12 first"> 
              <?php //echo $his['history_description']; ?>
			  <?php echo the_sub_field('column_left_part')?>
           </div>
          <?php /* <div class="col-md-6 col-sm-12 col-xs-12"> 
              <?php //echo $his['history_right_description']; ?>
			  <?php echo the_sub_field('column_right_part')?>
           </div> */?>
		    <?php $i++; ?>
		<?php endif; endwhile; endif; ?>
	   
      </div>
      <div class=" col-md-4 col-sm-4 col-xs-12 sidebar_desktop">
        <div class="panel-group" id="accordion">
          
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a class="collapsed" href="<?php echo home_url().'/history'; ?>">
              <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					History of the Philharmonic
							
			<?php }	 else {	?>
					
					 ההיסטוריה של הפילהרמונית
						
				<?php }?>
             </a> </h4>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a href="<?php echo home_url().'/musical-manager'; ?>" class="collapsed" >
              <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Music Director Zubin Mehta
							
			<?php }	 else {	?>
					
					  מנהל מוזיקלי זובין מהטה 
						
				<?php }?>
             </a> </h4>
            </div>
          </div>
		  <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#collapse" aria-expanded="true" class=""><?php echo get_the_title(); ?></a> </h4>
            </div>
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
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title"> <a class="collapsed" href="<?php echo home_url().'/gneral-information'; ?>"> 
              <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					General Information
							
			<?php }	 else {	?>
					
					   מידע כללי
						
				<?php }?>
            </a> </h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php get_footer(); ?>