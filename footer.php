<?php 


global $theme;

$social_icons = get_field('social_icons', 'option');
$footer_form = get_field('footer_form', 'option');

$back_to_top_text = get_field('back_to_top_text', 'option');


?>

</main>

    <!-- =============== footer area start =============== -->
    <footer class=" section-view min-height-0" >
        <div class="container">
          <div class="col-12">
            <div class="row">
			
			   <div class="" style="vertical-align: bottom;
    display: flex;
    padding-bottom: 25px;
    align-items: flex-end;">
                    <div class="footer-logo ">
                        <a href="<?php echo home_url( )?>" class="logo">
<?php                     
  if (ICL_LANGUAGE_CODE == 'he') {
       $logo_pc = '/wp-content/uploads/2022/11/we_here_for_u.svg';
                       $mobile_logo = '/wp-content/uploads/2023/02/we_here_for_u.svg';
} else {
     $logo_pc = '/wp-content/uploads/2023/05/kotarot.svg';
  $mobile_logo =  '/wp-content/uploads/2023/03/weheretolisten_mobile.svg';
}  ?>
                          
<img src="<?php echo $logo_pc; ?>" class="hide-mobile" style="width: 370px; max-width: 20vw;" />
<img src="<?php echo $mobile_logo; ?>" class="hide-pc" />
                             <?php /*

                                $logo = get_field('logos', 'option');
                                $footer_logo = $logo['footer_logo'];
                                $footer_logo_mobile = $logo['footer_logo_mobile'];

                                if($footer_logo)
                                    echo $theme->get_the_image($footer_logo);
                                if($footer_logo_mobile)
                                    echo $theme->get_the_image($footer_logo_mobile);
                            */ ?> 
                        </a>
                    </div>
                </div>
				
             
                <div class=" d-lg-none">
                    <div class="contact_info">
                        <div class="row">
                         
                            <div class="col-6">
                                <ul>
                                    <?php if (ICL_LANGUAGE_CODE == 'he'): ?>
                                    <li><strong>3766*<span> | </span>03-6211777</strong></li>
                                    <li><strong>רח’ הוברמן 1, תל אביב</strong></li>
                                    <li>א’-ה’ 9:00-18:00 l ו’ עד 13:00</li>
                                  <?php else: ?>
                                     <li><strong>3766*<span> | </span>03-6211777</strong></li>
                                    <li><strong>1 Huberman St., Tel Aviv</strong></li>
                                    <li>Sunday-Thursday 9:00-18:00, <br>Friday until 13:00</li>
                    
                                    <?php endif; ?>
                                   
                                </ul>
                            </div>
                             <div class="col-6">
                                <ul>
                                    <li> <img src="/wp-content/themes/wpstack-child/assets/images/small_icons_email.svg" alt=""> <a href="mailto:info@ipo.co.il">info@ipo.co.il</a></li>
                                    <li><img src="/wp-content/themes/wpstack-child/assets/images/small_icons_sms.svg" alt=""> <a href="tel:055-7000-232">055-7000-232</a></li>
<li><img src="/wp-content/uploads/2023/02/small_icons_fax.svg" alt="">  <a href="tel:1533-5253695">1533-5253695</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="footer-info">
                    <div class="info_widget fade-in-bottom one animate_wow" data-wow-delay="0.2s">
                        <?php the_field('footer_text', 'option');?>
                    </div>
                </div>
				   <div class="footer-email">
                    <div class="mail_widget">
                        <div class="row align-items-end">
						
						       <div class="col-lg-4 border-right-footer animate_wow">
	<h3>
        <?php the_field('footer_title', 'option');?>
</h3>

                                <?php  /*

                                    $form_title = $footer_form['form_title'];

                                    if($form_title)
                                        echo '<h2>'.$form_title.'</h2>'; 
*/
                                ?>
                                
                            </div>
							
                            <div class="col-lg-8 order-lg-1 fade-in-bottom one animate_wow" data-wow-delay="0.2s">
                                <?php 

                                    $form_shortcode = $footer_form['form_shortcode'];

                                    if($form_shortcode)
                                        echo do_shortcode($form_shortcode);

                                ?>
                            </div>
                     
                        </div>
                    </div>
                </div>
				
                
             
            </div>
        </div>
       </div>
    </footer>
    <!-- =============== footer area end =============== -->

    <!-- =============== copyright area start =============== -->
    <div class="copyright">
        <div class="container">
            <div class="row align-items-center">
			
			  <div class="col-lg-4 d-none d-lg-block">
                    <a class="back-to-top" href="#">
<img src="/wp-content/uploads/2022/06/bacl-to-top.png" alt="">
<?php echo $back_to_top_text; ?></a>
                </div>
				
            
                <div class="col-lg-4 col-7 text-left text-lg-center">
                    <ul class="social_links">
                        <?php 
                        
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
                </div>
				
				    <div class=" text-right display-flex flex_footer">
                    <?php if(get_field('footer_image', 'option')) :?>
						  <a href="https://tabuzzco.com/" target="_blank"> <img src="/wp-content/uploads/2023/01/tabuzzco2x.png" style="    height: 35px;
    margin-left: 25px;" ></a>
                        <a href="https://www.uxpert.com/"> <?php echo $theme->get_the_image(get_field('footer_image', 'option'));?></a>
			<div class="Collaborations">		
 <a href=""> <img src="https://www.ipo.co.il/wp-content/uploads/2023/08/%D7%9C%D7%95%D7%92%D7%95-%D7%A2%D7%99%D7%A8%D7%99%D7%99%D7%AA-%D7%AA%D7%9C-%D7%90%D7%91%D7%99%D7%91-e1692542391688.png"   /></a>



 <a href=""><img src="https://www.ipo.co.il/wp-content/uploads/2026/02/Blue-Logo-Hilton-TLV.png"  style="max-width: 60px;" /></a>



 <a href=""><img src="https://www.ipo.co.il/wp-content/uploads/2023/08/Vista-Logo_RGB.png"  /></a>


 <a href=""><img src="https://www.ipo.co.il/wp-content/uploads/2023/08/לוגו-עברית-scaled.jpg"  /></a>
                      </div>
                    <?php endif;?>
                </div>
              
            </div>
        </div>
    </div>
    <!-- =============== copyright area end =============== -->
    
</div> <!-- #page -->


<?php wp_footer(); ?>
</body>
</html>