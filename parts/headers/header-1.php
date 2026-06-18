<?php

global $theme;


$search_shortcode = '[wd_asp id=1]';

$link_logo = ipo_theme_uri( 'assets/images/Logo(Hebr)-oneway.json' );
// if wpml constant is defined and language is english
if( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE == 'en' ){
    // get the search shortcode for english
    $search_shortcode = '[wd_asp id=2]';
    $link_logo = ipo_theme_uri( 'assets/images/Logo(eng).json' );
}


?>
<!-- =============== Header area start =============== -->
<header class="header">
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



     <lottie-player  src="<?php echo $link_logo; ?>"  background="transparent" speed="2" style="width: 60px; height: 207px;display: inline-block;" class="logo-lottie "></lottie-player>


					</a>
                    <!--logo end-->
					
					
                </div>
            </div>
			
			<div class="mobile-header ">
			       <!-- menu toggler -->
                    <div class="hamburger-menu width-33">
                <img src="<?php echo esc_url( ipo_theme_uri( 'assets/images/small_icons_hamburger.svg' ) ); ?>" class="open-burger"/>
                      <img src="<?php echo esc_url( ipo_theme_uri( 'assets/images/X-icon.png' ) ); ?>" class="close-x"/>

                    </div>

                    <?php echo do_shortcode('[wpml_language_selector_widget]'); ?>

                    <a href="<?php echo home_url( )?>" class="logo  width-33">
                                        <img src="/wp-content/uploads/2022/09/logo.svg" class="hide_pc">
                    </a>

                    <a href="#" class="mobile-search-toggle">
                      <span class="open-search">
                     
               <img src="/wp-content/uploads/2023/02/icons_search.svg" />
                      </span>
                         <span class="close-search">                    
<img src="<?php echo esc_url( ipo_theme_uri( 'assets/images/small_icons_X.svg' ) ); ?>" />
                      </span>
                    </a>

                <!-- sm device search start box -->
                <div class="width-33 t1">

                    <div class="search_input">
                        <div class="search-field-container"><?php echo do_shortcode($search_shortcode); ?></div>
                        <!--
                        <img src="/wp-content/uploads/2022/06/search-i1.png" alt="">
                        <img src="/wp-content/uploads/2022/06/search-i2.png" alt="">
                        -->
                    </div>
                </div>
                <!-- sm device search end box --> 

                <!-- Side logo -->
                <a href="<?php echo home_url( )?>" class="side-logo">
                  
                    <img src="/wp-content/uploads/2022/09/logo.svg" class="hide_pc">
                </a>
                <!-- Side logo end -->

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
                    
                    <!--social icons start-->
                    <ul class="social_links">
                        <?php 
                            $social_icons = get_field('social_icons', 'option');
                        
                            if($social_icons) {
                                foreach($social_icons as $item){
                                    $icon =  $item['icon'];
                                    $link =  $item['link'];

                                    if($icon)
                                        $icon = $theme->get_the_image($icon);

                                      
                                    echo '<li><a href="'.$link.'" target="_blank">'.$icon.'</a></li>';
                                }
                            }
                            
                        ?>
                        
                    </ul>
                    <!--social icons end-->

                </div>
				
			</div>

            
            
        </div>
    </header>
    <!-- =============== Header area end =============== -->