<?php

global $theme;

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
  <div class="search-field-container"><?php echo do_shortcode('[wd_asp id=1]'); ?></div>
  <!--
                        <img src="/wp-content/uploads/2022/06/search-i1.png" alt="">
                        <img src="/wp-content/uploads/2022/06/search-i2.png" alt=""> -->
                </div>
                        <!--menu end-->

   </div>

                <div class="d-flex align-items-center">
                         <div class="lang menu">
                            <li class="enli">
                            <a href="#" class="en">EN</a></li>
                            <li class="heli"><a href="#" class="acitve">עב</a></li>
                            </div>
                            <?php 
					
                                wp_nav_menu( array(
                                    'theme_location' => 'cta-menu',
                                    'container' => '', 
                                    'container_class' => '',
                                ) );

                            ?>
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
				
				
				 <div class="col-lg-1 col-4 text-center">
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



<script>

</script>

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


                    <a href="<?php echo home_url( )?>" class="logo  width-33">
                                        <img src="/wp-content/uploads/2022/09/logo.svg" class="hide_pc">
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

