<?php 
// Template Name: Template - Podcasts

get_header();

$banner = get_field('hero_image');
if($banner)
    $banner = $banner['url'];

$banner_mob = get_field('hero_image_mobile');
if($banner_mob)
    $banner_mob = $banner_mob['url'];
else 
	$banner_mob = $banner;

$bannerImage = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), 'full' );
$history = get_field('IPO_history');

$default_banner = get_field('pages_placeholder_image', 'option');



?>

<div class="main_body">

	<div class="row escp-hero-banner">		
		<div class="col-md-12 col-sm-12 col-xs-12 banner_image"> 
			<div class="inner-banner">
			    <div class="banner-image">
			        <img class="desktop-only" src="<?php echo $banner; ?>"/>
			        <img class="mobile-only" src="<?php echo $banner_mob; ?>"/>
			    </div>
				<div class="textbox">
					<?php the_field('banner_title'); ?>
					<p class="subtitle"><?php the_field('banner_subtitle'); ?></p>
				</div>
			</div>
		</div>		
	</div>

  <div class="container">
 
	<div class="row row-content">


		<div class="col-12">
			<?php the_field('text_section'); ?>
		</div>
	</div>	
	<div class=" max-1440 prodcastpage">
	
			<?php
$podcastsbig = get_field('podcasts_categories');

if (!empty($podcastsbig)) {
    $play_svg = esc_url(ipo_theme_uri('includes/images/others/play.svg'));

    foreach ($podcastsbig as $podcasts) {
        $i = 0;

        echo '<div class="cs-text mt-50 mb-100"><ul>';

        if (!empty($podcasts['posts']) && is_array($podcasts['posts'])) {
            foreach ($podcasts['posts'] as $p) {
                $i++;

                $url = !empty($p['link']['url']) ? esc_url($p['link']['url']) : '#';
                $title = !empty($p['title']) ? esc_html($p['title']) : '';
                $subtitle = !empty($p['subtitle']) ? esc_html($p['subtitle']) : '';
                $code = !empty($p['podcast_code']) ? $p['podcast_code'] : '';

                echo '<li>';
                echo '<div class="display-prod">';
                echo '<div class="playsection">' . $code . '</div>';
                echo '<div class="text"><h3 class="Simpler">' . $title . '</h3><p style="font-weight: 300;">' . $subtitle . '</p></div>';
                echo '<div class="link"><a href="' . $url . '">להשמעה <span><img src="' . $play_svg . '" alt="Img" class="img-fluid"></span></a></div>';
                echo '</div>';
                echo '</li>';

                if ($i % 3 === 0) {
                    echo '</ul><ul>';
                }
            }
        }

        echo '</ul></div>';
    }
}
?>
		</div>
	

</div>

<?php get_footer();?>