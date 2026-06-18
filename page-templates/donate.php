<?php 
/*
* Template Name: Donate Page
*/

get_header(); 

$history = get_field('IPO_history');

$bannerImage = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), 'full' );
$default_banner = get_field('pages_placeholder_image', 'option');
$banner_image_mobile  = get_field('banner_image_mobile_main');

if ($banner_image_mobile) {
 
 echo '<style>
 @media (max-width: 768px){
 .hero_page-section{
     min-height: 265px;

background-image: url(' . wp_get_attachment_url($banner_image_mobile) .')!important;
}


.hero_page-section {
    padding: 0px!important;
    }
    
.hero_page-section>.container {
    position: absolute;
    bottom: 10px;
}
.hero_page-section img{
display: none!important;
}
}
</style>'; 
}

?>

<section class="hero_page-section" >
      <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
<div class="gradient-bottom" > </div>
  <div class="gradient-top"> </div>
        <div class="banner_image"> 
            <?php 

                if($bannerImage) {
                    echo $bannerImage;
                }elseif($default_banner) {
                    $default_banner = new wpstack_image($default_banner);
                    echo $default_banner->get_img();
                }

            ?>
        </div>      

   
</section>


		
       
        <!-- =============== Hero area end =============== -->


        <!-- =============== Section area start =============== -->
        <section class="about_area mt-100 mb-100">
            <div class="container  max-1440">

<h3 class="mb-25 sub-title-simpler" style="text-align: center!important; line-height: 1.4!important;">Support the Israel Philharmonic Now and Secure its Future!</h3>
  				<table class="donate_teble">
<tbody>
<tr>
<td style="text-align: center;"><img class="alignnone wp-image-19889" src="https://www.ipo.co.il/wp-content/uploads/2023/10/israel-150x150-1.png" alt="" width="50" height="50" />

<p class="info_text">To donate
  to the Israel Philharmonic Foundation</p>
 <p><a href="https://donate.ipo.co.il/" target="_blank" rel="noopener">Click here</a> </p></td>
<td style="text-align: center;"><img class="alignnone wp-image-19883" src="https://www.ipo.co.il/wp-content/uploads/2023/10/flag-150x150-1.png" alt="" width="50" height="50" />

<p class="info_text">To donate
to the American Friends of the Israel Philharmonic</p>
 <p><a href="https://connect.afipo.org/site/Donation2?1400.donation=form1&amp;df_id=1400&amp;mfc_pref=T" target="_blank" rel="noopener">Click here</a></p></td>
</tr>
<tr>
<td style="text-align: center;"><img class="alignnone wp-image-19887" src="https://www.ipo.co.il/wp-content/uploads/2023/10/canada-150x150-1.png" alt="" width="50" height="50" />

<p class="info_text">To donate
to the Canadian Friends of the Israel Philharmonic</p>
 <p><a href="https://www.canadahelps.org/en/charities/canadian-friends-of-the-israel-philharmonic-orchestra/" target="_blank" rel="noopener">Click here</a></p></td>
<td style="text-align: center;"><img class="alignnone wp-image-19885" src="https://www.ipo.co.il/wp-content/uploads/2023/10/uk-150x150-1.png" alt="" width="50" height="50" />

<p class="info_text">To donate
to the Israel Philharmonic Foundation UK</p>
 <p><a href="https://www.ipofoundationuk.com/donate/donation/" target="_blank" rel="noopener">Click here</a></p></td>
</tr>
<tr>
<td colspan="2">
<img class="alignnone wp-image-19895" src="https://www.ipo.co.il/wp-content/uploads/2023/10/globe.png" alt="" width="50" height="50" />
<p style="text-align: center;">TO DONATE FROM ANY OTHER COUNTRY</p>

  <p><a href="https://donate.ipo.co.il/" target="_blank" rel="noopener">Click here</a></p>
</td>
</tr>
</tbody>
</table>
<p style="text-align: center;" class="mt-25"><strong>To receive a charitable tax-deductible receipt (if applicable),
  please donate via the link above that applies to you.</strong></p>
            </div>
        </section>
        <!-- =============== about area end =============== -->
<style>

@media(max-width: 768px){
.donate_teble td a {
    background-color: rgba(0, 0, 0, 1);
    border-radius: 50px;
    font-size: 12px!important;
    display: inline-block;
    text-decoration: none;
    color: rgba(255, 255, 255, 1);
    letter-spacing: 1px;
    padding: 1rem 2.5rem!important;
    transition: .2s;
    margin-bottom: 15px;
    margin-top: 15px;
}

.donate_teble td {
    padding: 15px!important;
  }

  .info_text {
    font-size: 15px;
    min-height: 100px;
  }

  }
  .donate_teble td{
background: rgba(243, 243, 243, 1);
        border: 1px solid  rgba(175, 175, 175, 0.4);

    text-align: center;
    text-align: center;
    flex-flow: column;
    justify-content: center;
    align-items: center;
    padding: 25px;
}
  .donate_teble td img {
    margin-bottom: 15px;
  }

 .donate_teble {
    margin-left: auto;
    margin-right: auto;
}

  
  .donate_teble td a{
    background-color: rgba(0, 0, 0, 1);
    border-radius: 50px;
    font-size: 15px;
    display: inline-block;
    text-decoration: none;
    color: rgba(255, 255, 255, 1);
    letter-spacing: 1px;
    padding: 0.75rem 3.5rem;
    transition: .2s;
    margin-bottom: 15px;
    margin-top: 15px;

  }



</style>

	<?php get_footer(); ?>