<?php

/* Template Name: Artist  */ 

get_header();

$position = get_field('position');
$image = get_field('image');
$banner = get_field('banner');
$artist_default_banner = get_field('artist_default_banner', 'option');
$artist_default_avatar = get_field('artist_default_avatar', 'option');

$bg_class = '';
$bg_css = '';

$avatar_bg_class = '';
$avatar_bg_css = '';


if(!$banner){
    $banner = $artist_default_banner;
}

if($banner){
    // get the image url from id
    $banner_url = wp_get_attachment_image_src($banner, 'full');
    $bg_css = 'style="background-image:url('.$banner_url[0].');position: relative;"';
    $bg_class = 'has-bg ';
}


// if($image){
//     // get the image url from id
//     $image_url = wp_get_attachment_image_src($image, 'full');
//     $bg_css = 'style="background-image:url('.$image_url[0].')"';
//     $bg_class = 'has-bg';
// }

$main_text = get_field('main_text');
$read_more = get_field('read_more');
$read_more_text = get_field('read_more_text');




$musicians_page = get_field('musicians_page', 'option');
$musicians_page_link = get_permalink($musicians_page);
$urgency_fund_page = get_field('urgency_fund_page', 'option');
$urgency_fund_page_link = get_permalink($urgency_fund_page);

$page_content = get_field('main_text',$page_id);

$conected_artiest  = get_field('conected_artiest',$page_id);


$conected_artiest = get_field('conected_artiest', $page_id); // קבלת ה-ID של הפוסט

if ($conected_artiest) {
    $post_url = get_permalink($conected_artiest); 
    if ($post_url) {
        wp_redirect($post_url);
   exit;
    } 
}

if(ICL_LANGUAGE_CODE=='en') {
    
    $text['instrument'] = 'Instrument';   
    $text['position'] = 'Position';   
    $text['go_back'] = 'Go Back';   

    $text['dedicate'] = 'Dedicate A Musician\'s Chair';   
    $more_concerts_str = 'More concerts';
    
} else {
    
    $text['instrument'] = 'כלי';   
    $text['position'] = 'תפקיד';   
    $text['go_back'] = 'חזרה';   

    $text['dedicate'] = 'הקדישו כסא';  
    $more_concerts_str = 'קונצרטים נוספים';
    

}

if(!$donate_href)
$language_prefix = '';
if (function_exists('pll_get_post_language')) {
  $post_lang = pll_get_post_language(get_the_ID());

  
  if ($post_lang === 'ar') {
    $language_prefix = 'https://donate.ipo.co.il/donate/';
  } elseif ($post_lang === 'en') {
    $language_prefix = 'https://donate.ipo.co.il/en/donate-en';
  } else {
    $language_prefix = 'https://donate.ipo.co.il/donate/';
  }
} else {
  $language_prefix = 'https://donate.ipo.co.il/donate/';
}

if(ICL_LANGUAGE_CODE == 'en'){
    $language_prefix = 'https://donate.ipo.co.il/en/donate-en';
}

$musician_type = '';
$name = '';

$terms = get_the_terms(get_the_ID(), 'musician_type');
if (!is_wp_error($terms) && !empty($terms)) {
  $musician_type = $terms[0]->slug;
}

$name = get_the_title();
$name01 = urlencode($name);

$donate_href = $language_prefix . '?ref=artist&musician_type=' . $musician_type . '&title=' . $name01 .'&id=5';



$array_fields = array();
$item = get_field('instrument');
if($item) $array_fields[] = $text['instrument'] . ' / ' . $item;
$item = get_field('position');
$item_2 = get_field('position_2');
if($item) $array_fields[] = $text['position'] . ' / ' . $item;
if($item_2) $array_fields[] = '<span class="pos_2">' . $item_2 . '</span>';
$fields = $array_fields ? '<p>' . implode('</br>', $array_fields) . '</p>' : '';

$donation_active = false;
if(isset($_GET['type']) && $_GET['type'] == 'donation' ){
    $donation_active = true;
}

// Show by default
$donation_active_display = true;
if($donation_active){
    // Check if current artist has any temr of the taxonomy 'instrument' set
    $terms = get_the_terms( get_the_ID(), 'instrument' );

    if ( $terms && ! is_wp_error( $terms ) ) {
        $donation_active_display = true;
    }
} 

?>


        <!-- =============== Hero area start =============== -->
		
		<section class="hero_area-artist hide-mobile <?php echo $bg_class;?>" <?php echo $bg_css;?> style="    position: relative;" data-donation-active="<?php echo $donation_active_display ? 'true' : 'false';?>">
                 <?php   echo '<div class="gradient-bottom" style="max-height: 50%;">
                                    </div>';
             
               echo '<div class="gradient-top" style="max-height: 25%;">
                                    </div>'; ?>
                                      
            <div class="container">
            <div class="content">
                    <h1 class="header-title"><?php the_title();?></h1>

                    <?php if($position):?>
                        <p><?php echo $position;?></p>
                    <?php endif;?>

                </div>
            </div>
			<div class="gradient-top"></div>
        </section>
		
		
       
        <!-- =============== Hero area end =============== -->


        <!-- =============== about area start =============== -->
        <section class="about_area no-pb">
   <div class="content hide-pc">
                    <h1 class="title-pages" style="    text-align: center;    margin-bottom: -40px!important;    margin-top: 14px;"><?php the_title();?></h1>

                    <?php if($position):?>
                        <p><?php echo $position;?></p>
                    <?php endif;?>

                </div>
            <div class="container max-1440 mt-100 mb-100">
                <div class="row justify-content-between">
                 
                    <div class="col-lg-6">
                        <div class="content">

                         
                            <?php if( $donation_active ) : ?>

                                <?php 
                                
                                $donation_bio = get_field('donation_bio');
                                if(!$donation_bio){
                                    $donation_bio = $main_text;
                                }
                                echo $donation_bio;
                                
                                ?>

                            <?php else : ?>

                    

                                <?php if($main_text):?>
                                    <p><?php echo $main_text;?></p>
                                <?php endif;?>

                                <?php if($read_more):?>
                                    <div class="program-info">
                                         <div class="readmore-text read-more-text">
                                            <?php echo $read_more_text;?>
                                        </div>
                                        <a href="#" class="read-more"><span class="more"><?php $readmoretext = 'לקריאה נוספת';
$readlessetext = 'קרא פחות';
if(ICL_LANGUAGE_CODE == 'en'){
   $readmoretext  = 'Read More';
$readlessetext = 'Read less';
}
echo $readmoretext; ?></span><span class="less"><?php echo $readlessetext; ?></span></a>
                                     
                                    </div>
                                <?php endif;?>

                            <?php endif;?>

                        </div>
                    </div>
					
					   <div class="col-lg-5 order-first-mobile">
                        <div class="avatar">

                            <?php  
                                if ($image) {
                                    echo wp_get_attachment_image($image, 'full');
                              
    $caption = wp_get_attachment_caption($image);
    if ($caption) {
        echo '<p>'.$caption.'</p>';
    }
    } else {
      
             
       echo wp_get_attachment_image($artist_default_avatar, 'full');

                           
    }
                            ?>
                        
                        </div>

     <?php if( $donation_active_display ) :  ?>  


                            <div class="musicians-text"><?php echo $fields;?></div>

                            <?php
                                if(!get_field('donor') &&  has_term( '', 'instrument' ) ){
                                    
                                   echo '<a href="' . $donate_href . '" class="musicians-link" target="_blank">' . $text['dedicate'] . ' <img src="/wp-content/uploads/2022/06/left-arrow.png" alt=""></a>';

                                }
                            ?>
     <?php endif;  ?>  
                         
                         <div class="musician-donation-block">


                        <?php 
                                $text['donor'] = get_field('donor_text');
                                if($text['donor'] && get_field('donor')){
                                    echo '<p class="recognition-text">'. $text['donor'] .'</p>';
                                }
                            ?>
                         
                   <?php if( $donation_active_display ) :  ?>  


                            <div class="musicians-text"><?php echo $fields;?></div>

                            <?php
                           
                            ?>
                            <div class="link-back hidden-sm hidden-xs display-none">
                                <a href="<?php echo $musicians_page_link;?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path id="ic_arrow_back_24px" d="M20,11H7.83l5.59-5.59L12,4,4,12l8,8,1.41-1.41L7.83,13H20Z" transform="translate(-4 -4)" fill="#e00326"/></svg>
                                    <?php echo $text['go_back'];?>
                                </a>
                            </div> 
                            <?php
                                if(!get_field('donor') && ! has_term( '', 'instrument' )){
                                    if(ICL_LANGUAGE_CODE=='en') {
                                        echo '<div class="musician-form en">';
                                        echo do_shortcode(get_field('donations_form','option'));
                                        echo '</div>';
                                    }else{
                                        echo '<div class="musician-form he">';
                                        echo do_shortcode(get_field('donations_form','option'));
                                        echo '</div>';
                                    }
                                    
                                }
                            ?>

                        <?php endif;  ?>


                        </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- =============== about area end =============== -->

     

        <!-- =============== More concerts area start =============== -->
        <?php 

        /*
        $program_related_programs = get_posts([
            'post_type' => 'program',
            'posts_per_page' => 4,
            'post__not_in' => [$program_id], 
            'fields' => 'ids', 
            'suppress_filters' => false,
        ]);
        */
        // Find all programs with this artist
        /*
        $args = array(
            'post_type' => 'program',
            'posts_per_page' => 4,
            'post__not_in' => [$program_id], 
            'fields' => 'ids', 
            'suppress_filters' => false,
            'meta_query' => array(
                array(
                    'key' => 'program_artists',
                    'value' => '"' . get_the_ID() . '"',
                    'compare' => 'LIKE'
                )
            )
        );

        $program_related_programs = get_posts($args);
        */

        $program_related_programs = get_field('related_programs');
        

        ?>
        <?php 
        $count = 0;
        $items_html = '';
if (!empty($program_related_programs) && is_array($program_related_programs)) {
    foreach ($program_related_programs as $related_program_id) { 
        $count = $count + 1;
        $part = $theme->get_part('loop-program', $related_program_id);
        if (trim($part) !== '') {
            $items_html .= '<div class="item">' . $part . '</div>';
        }
    }
}


        // Remove html comments from the html and trim it
        $items_html = trim(preg_replace('/<!--(.|\s)*?-->/', '', $items_html));



        ?>
<?php if ($count > 0 && $items_html): ?>
        <section class="moreConcerts">
            <div class="container max-1440">
                <div class="row">
                    <div class="" style="width: 100%;">
                        <h2><?php echo $more_concerts_str; ?></h2>
                    </div>
                </div>

                <!-- slider start -->
         <div class="owl-carousel owl-theme moreConcerts-slider t3" data-items_ids="<?php echo implode(',', $program_related_programs); ?>">

            <?php
            echo $items_html;
                ?>

           </div> 
                    
                <!-- slider end -->

            </div>
        </section>
<?php endif; ?>
        <!-- =============== More concerts area end =============== -->

      
        <?php get_footer();?>
		

<script>
jQuery(document).ready(function($){
    // Check if page was loaded with parameter type=donation
    var urlParams = new URLSearchParams(window.location.search);
    var type = urlParams.get('type');
    if(type == 'donation'){
        // Add the parameter to the href of a.wpml-ls-link 
        $('.wpml-ls-link').each(function(){
            var href = $(this).attr('href');
            $(this).attr('href', href + '?type=donation');
        });
    }
});
</script>
		
<style>
	.hero_area-artist{
					
					height: 380px;
					     background-size: contain!important;
    max-height: 770px;
    background-repeat: no-repeat!important;
     display: flex;

    align-items: flex-end;
	background: rgba(239, 163, 139, 1);
				}
				
				.hero_area-artist p{
				font-family: 'SimplerPro-Light';
font-size: 28px;
    font-family: 'Simpler';
    font-weight: 300;
	line-height: 1.8;
	margin-right: 1rem;
	color: #fff;
}
				.hero_area-artist>.container {
					padding-bottom: 40px;
				}
				

</style>


  <script>
                            // Wrap for wordpress
                            jQuery(document).ready(function($) {
                                $('.program-info .read-more').click(function(e) {
                                    e.preventDefault();
                                    // Add class to .program-info to show read more text
                                    $('.program-info').toggleClass('show-read-more');
                                });
                            });
                            </script>
                            <style>
                        
                            .program-info .read-more-text {
                                display: none;
                            }
                            .program-info.show-read-more .read-more-text {
                                display: block;
                              }
                              
                            .program-info  .less {
                                display: none;
                              }
                              
                                    .program-info.show-read-more  .less {
                                display: block!important;
                            }
                            
                                    .program-info.show-read-more  .more {
                                display: none!important;
                            }
                        

                            </style>