<?php

global $theme;


$search_shortcode = '[wd_asp id=1]';
// if wpml constant is defined and language is english
if( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE == 'en' ){
    // get the search shortcode for english
    $search_shortcode = '[wd_asp id=2]';
}

?>
<!-- =============== Header area start =============== -->
<header>
        <div class="container">
		
		
            <div class="row align-items-center align-items-lg-start hide-mobile">
        
               
			   
                <div class="col-lg-11 col-4 npl">
                    <div class="right_item d-flex align-items-center justify-content-between desktop-menu">
        
<div class="menu-pc">
                        <!--menu start-->

                        
                        <?php 
						
							wp_nav_menu( array(
								'theme_location' => 'header-menu',
								'container' => '', 
                                'walker' => new submenu_wrap()
							) );


						?>
                        
  <div class="search_input">
  <div class="search-field-container"><?php echo do_shortcode($search_shortcode); ?></div>
  <!--
                        <img src="/wp-content/uploads/2022/06/search-i1.png" alt="">
                        <img src="/wp-content/uploads/2022/06/search-i2.png" alt=""> -->
                </div>
                        <!--menu end-->

   </div>

                <div class="d-flex align-items-center left-header">
                         <div class="lang menu">
                            
                            <?php 

                            echo do_shortcode('[wpml_language_selector_widget]');

                            // // get current WPML language with the constant ICL_LANGUAGE_CODE
                            // if( ICL_LANGUAGE_CODE == 'he' ){
                            //     // Check if current page has a translation in English
                            //     // get item type
                            //     $item_type = get_post_type( get_the_ID() );
                            //     // use the new apply_filters( 'wpml_object_id', int $element_id, string $element_type, bool $return_original_if_missing, mixed $ulanguage_code ) method
                            //     $translated_item = apply_filters( 'wpml_object_id', get_the_ID(), $item_type, true, 'en' );
                            //     $link = get_permalink( $translated_item );
                            //     // If there is a translation, display the link with the language code, otherwise go to the homepage
                            //     if( $link ){
                            //         $link = '<a href="'.$link.'" class="en">EN</a>';
                            //     } else {
                            //         $link = '<a href="'.get_home_url().'" class="en">EN</a>';
                            //     }
                            //     echo '<li class="enli">'.$link.'</li>';
                            //     echo '<li class="heli"><a href="#" class="acitve">עב</a></li>';

                            // } else {
                                
                            //     // Check if current page has a translation in Hebrew
                            //     // get item type
                            //     $item_type = get_post_type( get_the_ID() );
                            //     // use the new apply_filters( 'wpml_object_id', int $element_id, string $element_type, bool $return_original_if_missing, mixed $ulanguage_code ) method
                            //     $translated_item = apply_filters( 'wpml_object_id', get_the_ID(), $item_type, true, 'he' );

                            //     $link = get_permalink( $translated_item );
                            //     // If there is a translation, display the link with the language code, otherwise go to the homepage
                            //     if( $link ){
                            //         $link = '<a href="'.$link.'" class="he">עב</a>';
                            //     } else {
                            //         $link = '<a href="'.get_home_url().'" class="he">עב</a>';
                            //     }
                                
                            //     echo '<li class="enli"><a href="#" class="acitve">EN</a></li>';
                            //     echo '<li class="heli">'.$link.'</li>';
                            // }
                            
                            
                            ?>

                            </div>
                            <div class="cta-menu">
                            <?php 
					
                                wp_nav_menu( array(
                                    'theme_location' => 'cta-menu',
                                    'container' => '', 
                                    'container_class' => '',
                                ) );

                            ?>
                            </div>
							<div class="calltous">
							   <?php the_field('header_text', 'option');?>
							   </div>
                        </div>

                    </div>

                 

                    <!-- Ofcanvas-menu -->
                    <div class="ofcavas-menu">
                        <!--menu start-->
                        <?php 
						
							wp_nav_menu( array(
								'theme_location' => 'header-menu',
								'container' => '', 
							) );

						?>
                        <?php 
					
                            wp_nav_menu( array(
                                'theme_location' => 'cta-menu',
                                'container' => 'div', 
                                'container_class' => 'cta-menu',
                            ) );

                        ?>
                    </div>
                    <!-- Ofcanvas-menu END-->
                </div>
				
				
				 <div class="col-lg-1 col-4 text-center logo-container">
                    <!--logo start-->
                    <a href="<?php echo home_url( )?>" class="logo">
                                         
										<?php 
							$logo = get_field('logos', 'option');
							$dark_logo = $logo['dark'];
							$light_logo = $logo['light'];

							if($light_logo){
								echo $theme->get_the_image($light_logo);
                            }
                            if($dark_logo){

								echo $theme->get_the_image($dark_logo);

}
echo '';?>



     <lottie-player src="https://lottie.host/5c718666-3464-4d20-bba4-05d136ebf522/ZU8fH3ZiUe.json" background="transparent" speed="1" style="width: 60px; height: 207px;" class="logo-lottie hide-en"></lottie-player>


					</a>
                    <!--logo end-->
					
					
                </div>
            </div>
			
			<div class="mobile-header ">
			       <!-- menu toggler -->
                    <div class="hamburger-menu width-33">
                        <span class="line-top"></span>
                        <span class="line-center"></span>
                        <span class="line-bottom"></span>
                    </div>

                    <?php echo do_shortcode('[wpml_language_selector_widget]'); ?>

                    <a href="<?php echo home_url( )?>" class="logo  width-33">
                                        <img src="/wp-content/uploads/2022/09/logo.svg" class="hide_pc">
                    </a>

                    <a href="#" class="mobile-search-toggle">
                      <span class="open-search">
                        <?php 
                        $url = $theme->get_asset('icons_search.svg'); 
                        // get svg content
                        $svg = file_get_contents($url);
                        // Print svg content
                        echo $svg;
                        ?>
                      </span>
                         <span class="close-search">                    
<img src="/wp-content/themes/wpstack-child/assets/images/small_icons_X.svg" />
                      </span>
                    </a>

                <!-- sm device search start box -->
                <div class="width-33 t1">

                    <div class="search_input">
                        <div class="search-field-container"><?php echo do_shortcode('[wd_asp id=1]'); ?></div>
                        <!--
                        <img src="/wp-content/uploads/2022/06/search-i1.png" alt="">
                        <img src="/wp-content/uploads/2022/06/search-i2.png" alt="">
                        -->
                    </div>
                </div>
                <!-- sm device search end box --> 

                <div class="mobile-menu">
                    <!--menu start-->
                    <?php 
                    
                        wp_nav_menu( array(
                            'theme_location' => 'header-menu',
                            'container' => '', 
                            'walker' => new submenu_wrap()
                        ) );

                    ?>
                    <!--menu end-->
                </div>
				
			</div>

            
            
        </div>
    </header>
    <!-- =============== Header area end =============== -->