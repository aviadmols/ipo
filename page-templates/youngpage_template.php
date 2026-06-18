<?php 
/*
* Template Name: Young-page Template
*/

get_header();

global $theme;

$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
$bannervideo = get_field('video_field');
$ID = get_the_ID();
?>





    <style>
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
    <div id="preloader"></div>
    <script>
        window.addEventListener("load", function() {
            setTimeout(function() {
                document.getElementById("preloader").style.display = "none";
            }, 500); // חצי שנייה
        });

 </script>

		    <section class="hero_page-section" >



	
  <?php echo do_shortcode('[display_svg]'); ?>

        </section>
		

<section class="about_area young_page ">
		
		<div class="container  max-1440 ">

<div class=" page-txt ">
	 

				
			
			<div class="content_part ">
		
				<?php the_content();?>
			</div>
				</div>
          		</div>
	</section>
     
          
          
          <style>
             .open-search img {
    filter: invert(1)!important;
}

        .content_part     em{
    background: #fbd560;
    padding: 5px;
    text-decoration: none !important;
    font-style: normal !important;
}
            
 .svg-container svg{
    max-width: 100%!important;
    height: auto!important;
}

            .subscribe-content ul {
                  list-style: disc!important;
    				margin-right: 30px;
            }

.subscribe-section .sb-text a {
    text-decoration: underline!important;
}
            
            .subscribe-section * {
                color: #000 !important;
    fill: #000;
    -webkit-text-fill-color: #000!important;
            }
            
            .subscribe-content .title:before {

    background-color: #000!important;
}
            
             .subscribe-content    .white-color img {
    filter: none !important;
}

			  @media (max-width: 768px) {
			 .ipo-breadcrumbs-div {
   display: none!important; 
  }

			  }

          </style>
          
              <section class="subscribe-section pt-100 pb-100" style="    background-color: #f9f6ff;">
<?php 
$subscribe_title = get_field('subscribe_title');
$subscribe_text = get_field('subscribe_text');
$subscribe_link = get_field('subscribe_link');
?>
                <div class="container">
                    <div class="subscribe-content">
                        <div class="title">
                            <?php if($subscribe_title) : ?>
                            <h2 class="lette-sapce-10"><?php echo $subscribe_title;?></h2>
                            <?php endif;?>
                        </div>
                        <div class="sb-text">

                            <?php echo $subscribe_text;?>

                          <div class="view-more pt-50">

                                <?php 
                                if( $subscribe_link ): 
                                    $link_url = $subscribe_link['url'];
                                    $link_title = $subscribe_link['title'];
                                    $link_target = $subscribe_link['target'] ? $subscribe_link['target'] : '_self';
                                    ?>
                                    <a class="white-color" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                        </div>                        
                    </div>
                </div><!-- /.container -->
            </section><!-- /.subscribe-section -->



    <div style="background: #f7f7f7;">
      <!-- =============== upcoming area start =============== -->
            <?php $theme->the_part('section-upcoming'); ?>
        <!-- =============== upcoming area end =============== -->
</div>
          
          
       
          
          
          
            
              <section>
               
       
				  	<?php if(ICL_LANGUAGE_CODE=='en'){ ?>
				                 <div class="contact_form mb-75 mt-75" style="margin-right: auto; margin-left: auto;">
<h3>Register Here</h3>
<p><?php echo do_shortcode('[gravityforms id="7" title="false"]');?></p>
</div>
               	<?php } else { ?>


            </section>
            
            
            <section>
                        <div class="container_upcaming">
		<div class="col-md-12 col-sm-12 col-xs-12 last_section" id="contact">
			<?php if(ICL_LANGUAGE_CODE=='en'){ ?>
			
		<div class="col-md-4 col-sm-4 col-xs-12">
			
		</div>
		
		<div class="">
			
		</div>
		
		<?php } else { ?>
		
			        <div class="contact_form mb-75 mt-75" style="margin-right: auto; margin-left: auto;">
<h3>להרשמה</h3>
<p><?php echo do_shortcode('[gravityforms id="6" title="false" ajax="true"]');?></p>
</div>
				  

			
			<?php } ?>
			
<style>
  #gform_confirmation_wrapper_6 {
	min-height: 250px;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  
 
          </style>
		<div class="">
		
		</div>
		
		<div class="col-md-4 col-sm-4 col-xs-12">
		
		</div>
		<?php } ?>
		
		</div><!-- last_section over -->
                           </div>
</section>

          
              <section class="about_area young_page ">
		
		<div class="container  max-1440 ">

<div class=" page-txt ">
	 

				
			
			<div class="content_part ">
		
		<?php echo get_field('last_section_text');?>
			</div>
				</div>
          		</div>
	</section>
       
<?php  get_footer();?>