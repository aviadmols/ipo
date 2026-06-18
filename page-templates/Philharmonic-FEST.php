<?php
/*
 * Template Name: Philharmonic FEST
 */

get_header();

global $theme;

$bannerImage = get_field('SVG_pc_img');
$video_categories = get_field('video_categories');

$subtitle = get_field('subtitle');

$subscribe_title = get_field('subscribe_title');
$subscribe_text = get_field('subscribe_text');
$subscribe_link = get_field('subscribe_link');

$program_title = get_field('program_title');
$program_subtitle = get_field('program_subtitle');
$program_text = get_field('program_text');
$program_link = get_field('program_link');
$program_image = get_field('program_image');

$podcast_title = get_field('podcast_title');
$podcast_subtitle = get_field('podcast_subtitle');
$podcast_image = get_field('podcast_image');
$podcasts = get_field('podcasts');
$podcast_link = get_field('podcast_link');

$videos_title = get_field('videos_title');
$videos_subtitle = get_field('videos_subtitle');
$videos = get_field('videos');
$videos_link = get_field('videos_link');

$artists = get_field('artists');
$bannerImage = get_field('SVG_pc_img');

$bannerImagesvg = get_field('SVG_Mobile_img');
if (empty($bannerImage)) {
    $bannerImage = ipo_theme_uri('includes/images/bg/1.jpg');
}

$banner_image_mobile = get_field('banner_image_mobile');
$color_header = get_field('color_header');
$background_color = get_field('background_color');
$hero_background_color = $background_color ? $background_color : '#ff422e';
$hero_mobile_image = $banner_image_mobile ? wp_get_attachment_url($banner_image_mobile) : $bannerImagesvg;

if ($color_header) {
    echo '<style>
    .hero-text h1 {
        color: ' . esc_attr($color_header) . '!important;
    }

    body:not(.white-intro) .hero-text h1 {
        text-shadow: unset!important;
    }

    .sub-title-simpler {
        color: ' . esc_attr($color_header) . '!important;
    }
    </style>';
}

if ($bannerImagesvg) {
    echo '<style>
    .hero-section {
        background-color: ' . esc_attr($hero_background_color) . '!important;
    }
    </style>';
}

if ($banner_image_mobile) {
    echo '<style>
    @media (max-width: 768px) {
        .hero-section {
            background-color: ' . esc_attr($hero_background_color) . '!important;
        }

        .hero-section {
            padding: 0px!important;
        }

        .hero-section>.container {
            position: absolute;
            bottom: 10px;
        }

        body:not(.white-intro) .hero-text h1 {
            text-shadow: unset!important;
        }

        body.no_menu_overlay_mobile .hero-section {
            display: none!important;
        }

        .ipo-breadcrumbs > * {
            color: #000!important;
        }

        .hero-text h1 {
            margin-bottom: 0px!important;
            font-size: 120px;
            padding-bottom: 0px!important;
        }

        .ipo-breadcrumbs-div {
            margin-top: -60px!important;
        }

        .sub-title-simpler {
            text-align: center!important;
        }
    }
    </style>';
}
?>

<div class="main-content">
    <section class="hero-section" style="background-color: <?php echo esc_attr($hero_background_color); ?>; display: block!important;">
        <picture class="hero-section-picture">
            <?php if ($hero_mobile_image) : ?>
                <source media="(max-width: 768px)" srcset="<?php echo esc_url($hero_mobile_image); ?>">
            <?php endif; ?>
            <img class="hero-section-image" src="<?php echo esc_url($bannerImage); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
        </picture>
        <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
        <div class="container">
            <div class="hero-text">
                <h1 class="header-title pb-25" style="text-shadow: unset!important;"><?php echo esc_html(get_the_title()); ?></h1>
            </div><!-- /.hero-text -->
        </div><!-- /.container -->
    </section><!-- /.hero-section -->

    <section class="text-intro-section pb-50 pt-100">
        <div class="container">
            <?php
            while (have_posts()) :
                the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </section>

    <section class="video-section pb-50 pt-100">
        <div class="container">
            <div class="title style-1 pb-50">
                <?php if (get_field('section-title1')) : ?>
                    <h2 class="lette-sapce-10" data-aos="fade" data-aos-duration="750" data-aos-delay="0"><?php echo get_field('section-title1'); ?></h2>
                <?php endif; ?>
            </div>

            <div class="slider-content">
                <div class="owl-carousel video-slider video-slider-loading">
                    <?php foreach ((array) get_field('Banners1') as $Banner) : ?>
                        <div class="img_box" data-aos="fade-up" data-aos-duration="750" data-aos-delay="0">
                            <a href="<?php echo $Banner['url']; ?>">
                                <div class="thumb">
                                    <img src="<?php echo $Banner['image']; ?>">
                                </div>
                                <?php if ($Banner['title']) : ?>
                                    <h4><?php echo $Banner['title']; ?></h4>
                                <?php endif; ?>
                                <?php if ($Banner['subtitle']) : ?>
                                    <p class="text"><?php echo $Banner['subtitle']; ?></p>
                                <?php endif; ?>
                            </a>

                            <?php if ($Banner['url']) : ?>
                                <?php
                                if (ICL_LANGUAGE_CODE == 'he') {
                                    $btn_text = 'למידע ורכישה';
                                    echo '<a href="' . $Banner['url'] . '" style="display: flex; font-weight: 400; align-items: center;">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-right: 6px;"></a>';
                                } else {
                                    $btn_text = 'info & tickets';
                                    echo '<a href="' . $Banner['url'] . '" style="display: flex; direction: ltr; font-weight: 400; align-items: center; ">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-left: 6px; transform: rotate(180deg);"></a>';
                                }
                                ?>
                            <?php endif; ?>
                        </div><!-- /.video -->
                    <?php endforeach; ?>
                </div><!-- /.video-slider -->
            </div><!-- /.slider-content -->
        </div><!-- /.container -->
    </section><!-- /.video-section -->

    <section class="video-section pb-50 pt-100" style="background-color: #f9f6ff;">
        <div class="container">
            <div class="title style-1 pb-50">
                <?php if (get_field('section-title2')) : ?>
                    <h2 class="lette-sapce-10" data-aos="fade" data-aos-duration="750" data-aos-delay="0"><?php echo get_field('section-title2'); ?></h2>
                <?php endif; ?>
            </div>

            <div class="slider-content">
                <div class="owl-carousel video-slider video-slider-loading">
                    <?php foreach ((array) get_field('Banners2') as $Banner) : ?>
                        <div class="img_box" data-aos="fade-up" data-aos-duration="750" data-aos-delay="0">
                            <a href="<?php echo $Banner['url']; ?>">
                                <div class="thumb">
                                    <img src="<?php echo $Banner['image']; ?>">
                                </div>
                                <?php if ($Banner['title']) : ?>
                                    <h4><?php echo $Banner['title']; ?></h4>
                                <?php endif; ?>
                                <?php if ($Banner['subtitle']) : ?>
                                    <p class="text"><?php echo $Banner['subtitle']; ?></p>
                                <?php endif; ?>
                            </a>

                            <?php if ($Banner['url']) : ?>
                                <?php
                                if (ICL_LANGUAGE_CODE == 'he') {
                                    $btn_text = 'למידע ורכישה';
                                    echo '<a href="' . $Banner['url'] . '" style="display: flex; font-weight: 400; align-items: center;">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-right: 6px;"></a>';
                                } else {
                                    $btn_text = 'info & tickets';
                                    echo '<a href="' . $Banner['url'] . '" style="display: flex; direction: ltr; font-weight: 400; align-items: center; ">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-left: 6px; transform: rotate(180deg);"></a>';
                                }
                                ?>
                            <?php endif; ?>
                        </div><!-- /.video -->
                    <?php endforeach; ?>
                </div><!-- /.video-slider -->
            </div><!-- /.slider-content -->
        </div><!-- /.container -->
    </section><!-- /.video-section -->

    <?php if (get_field('section-title3')) : ?>
        <section class="video-section pb-50 pt-100" style="background-color: #eefbf1;">
            <div class="container">
                <div class="title style-1 pb-50">
                    <h2 class="lette-sapce-10" data-aos="fade" data-aos-duration="750" data-aos-delay="0"><?php echo get_field('section-title3'); ?></h2>
                </div>

                <div class="slider-content">
                    <div class="owl-carousel video-slider video-slider-loading">
                        <?php foreach ((array) get_field('Banners3') as $Banner) : ?>
                            <div class="img_box" data-aos="fade-up" data-aos-duration="750" data-aos-delay="0">
                                <a href="<?php echo $Banner['url']; ?>">
                                    <div class="thumb">
                                        <img src="<?php echo $Banner['image']; ?>">
                                    </div>
                                    <?php if ($Banner['title']) : ?>
                                        <h4><?php echo $Banner['title']; ?></h4>
                                    <?php endif; ?>
                                    <?php if ($Banner['subtitle']) : ?>
                                        <p class="text"><?php echo $Banner['subtitle']; ?></p>
                                    <?php endif; ?>
                                </a>

                                <?php if ($Banner['url']) : ?>
                                    <?php
                                    if (ICL_LANGUAGE_CODE == 'he') {
                                        $btn_text = 'למידע ורכישה';
                                        echo '<a href="' . $Banner['url'] . '" style="display: flex; font-weight: 400; align-items: center;">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-right: 6px;"></a>';
                                    } else {
                                        $btn_text = 'info & tickets';
                                        echo '<a href="' . $Banner['url'] . '" style="display: flex; direction: ltr; font-weight: 400; align-items: center; ">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-left: 6px; transform: rotate(180deg);"></a>';
                                    }
                                    ?>
                                <?php endif; ?>
                            </div><!-- /.video -->
                        <?php endforeach; ?>
                    </div><!-- /.video-slider -->
                </div><!-- /.slider-content -->
            </div><!-- /.container -->
        </section><!-- /.video-section -->
    <?php endif; ?>

    <?php if (get_field('section-title4')) : ?>
        <section class="video-section pb-50 pt-100" style="background-color: #c4f0cf;">
            <div class="container">
                <div class="title style-1 pb-50">
                    <h2 class="lette-sapce-10" data-aos="fade" data-aos-duration="750" data-aos-delay="0"><?php echo get_field('section-title4'); ?></h2>
                </div>

                <div class="slider-content">
                    <div class="owl-carousel video-slider video-slider-loading">
                        <?php foreach ((array) get_field('Banners4') as $Banner) : ?>
                            <div class="img_box" data-aos="fade-up" data-aos-duration="750" data-aos-delay="0">
                                <a href="<?php echo $Banner['url']; ?>">
                                    <div class="thumb">
                                        <img src="<?php echo $Banner['image']; ?>">
                                    </div>
                                    <?php if ($Banner['title']) : ?>
                                        <h4><?php echo $Banner['title']; ?></h4>
                                    <?php endif; ?>
                                    <?php if ($Banner['subtitle']) : ?>
                                        <p class="text"><?php echo $Banner['subtitle']; ?></p>
                                    <?php endif; ?>
                                </a>

                                <?php if ($Banner['url']) : ?>
                                    <?php
                                    if (ICL_LANGUAGE_CODE == 'he') {
                                        $btn_text = 'למידע ורכישה';
                                        echo '<a href="' . $Banner['url'] . '" style="display: flex; font-weight: 400; align-items: center;">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-right: 6px;"></a>';
                                    } else {
                                        $btn_text = 'info & tickets';
                                        echo '<a href="' . $Banner['url'] . '" style="display: flex; direction: ltr; font-weight: 400; align-items: center; ">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-left: 6px; transform: rotate(180deg);"></a>';
                                    }
                                    ?>
                                <?php endif; ?>
                            </div><!-- /.video -->
                        <?php endforeach; ?>
                    </div><!-- /.video-slider -->
                </div><!-- /.slider-content -->
            </div><!-- /.container -->
        </section><!-- /.video-section -->
    <?php endif; ?>

    <?php if (get_field('section-title5')) : ?>
        <section class="video-section pb-50 pt-100" style="background-color: #f9f6ff;">
            <div class="container">
                <div class="title style-1 pb-50">
                    <h2 class="lette-sapce-10" data-aos="fade" data-aos-duration="750" data-aos-delay="0"><?php echo get_field('section-title5'); ?></h2>
                </div>

                <div class="slider-content">
                    <div class="owl-carousel video-slider video-slider-loading">
                        <?php foreach ((array) get_field('Banners5') as $Banner) : ?>
                            <div class="img_box" data-aos="fade-up" data-aos-duration="750" data-aos-delay="0">
                                <a href="<?php echo $Banner['url']; ?>">
                                    <div class="thumb">
                                        <img src="<?php echo $Banner['image']; ?>">
                                    </div>
                                    <?php if ($Banner['title']) : ?>
                                        <h4><?php echo $Banner['title']; ?></h4>
                                    <?php endif; ?>
                                    <?php if ($Banner['subtitle']) : ?>
                                        <p class="text"><?php echo $Banner['subtitle']; ?></p>
                                    <?php endif; ?>
                                </a>

                                <?php if ($Banner['url']) : ?>
                                    <?php
                                    if (ICL_LANGUAGE_CODE == 'he') {
                                        $btn_text = 'למידע ורכישה';
                                        echo '<a href="' . $Banner['url'] . '" style="display: flex; font-weight: 400; align-items: center;">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-right: 6px;"></a>';
                                    } else {
                                        $btn_text = 'info & tickets';
                                        echo '<a href="' . $Banner['url'] . '" style="display: flex; direction: ltr; font-weight: 400; align-items: center; ">' . $btn_text . '<img src="' . ipo_arrow_icon_url() . '" class="arrow" alt="" style="width: 8px; margin-top: 1px; margin-left: 6px; transform: rotate(180deg);"></a>';
                                    }
                                    ?>
                                <?php endif; ?>
                            </div><!-- /.video -->
                        <?php endforeach; ?>
                    </div><!-- /.video-slider -->
                </div><!-- /.slider-content -->
            </div><!-- /.container -->
        </section><!-- /.video-section -->
    <?php endif; ?>
</div><!-- /.main-content -->

<style>
.img_box a:not(.img-link):hover {
    background: transparent!important;
}

.hero-section {
    position: relative;
    min-height: 0!important;
    padding: 0!important;
    background-image: none!important;
    overflow: hidden;
}

.hero-section-picture,
.hero-section-image {
    display: block;
    width: 100%;
}

.hero-section-image {
    height: auto;
}

.hero-section .ipo-breadcrumbs-div,
.hero-section>.container {
    position: absolute;
    z-index: 2;
}

.hero-section>.container {
    left: 0;
    right: 0;
    bottom: 10px;
}

@media (max-width: 768px) {
    .owl-carousel.owl-rtl .img_box {
        width: 70vw!important;
    }

    .text-intro-section {
        padding-bottom: 0px!important;
    }

    .video-section {
        padding-bottom: 50px!important;
    }

    .img_box>a:first-child {
        min-height: 270px;
        padding-bottom: 15px;
        align-items: flex-start!important;
    }

    [lang="en-US"] .video-section .owl-carousel .owl-stage-outer {
        padding-left: 25px;
    }

    .video-section .thumb> img {
        min-width: 60vw!important;
        width: 100%!important;
        height: 100%!important;
        aspect-ratio: 390 / 235!important;
        width: 268.38px!important;
    }

    .thumb> img {
        max-height: 43vw!important;
    }
}

@media (min-width: 768px) {
    h2.lette-sapce-10 {
        font-size: 80px!important;
        color: #000000;
        text-align: right;
        letter-spacing: 5.28px!important;
    }

    .img_box .text {
        font-weight: 300;
        overflow: hidden;
    }
}

.img_box .text {
    width: 100%;
}

@media (min-width: 768px) {
    .img_box>a:first-child {
        min-height: 330px;
        align-items: flex-start!important;
    }

    .hero-section {
        min-height: 0!important;
        display: block!important;
        background-image: none!important;
        background-color: <?php echo esc_attr($hero_background_color); ?>;
    }
  
  .video-slider-two-items .owl-stage {
    width: calc(100% - 25px)!important;
  }
  
.video-slider-two-items  .owl-carousel  {
        width: 100%!important;
  }
  
  .video-slider-three-items .owl-carousel  {
        width: 100%!important;
  }
  
  .video-slider-three-items .owl-stage {
    width: calc(100% - 25px)!important;
  }

    .video-slider.video-slider-two-items .owl-item {
               min-width: calc(33% - 30pX) !important;
    }

  
.video-slider.video-slider-three-items .owl-item {
     min-width: calc(33% - 30pX) !important;
    }
    .video-slider.video-slider-two-items .img_box {
        width: 100%!important;
    }

      .video-slider.video-slider-three-items .img_box {
        width: 100%!important;
    }
  
    .video-slider.video-slider-two-items .thumb>img {
        width: 100%!important;
        height: auto!important;
        max-height: none!important;
    }
  
   
    .video-slider.video-slider-three-items .thumb>img {
        width: 100%!important;
        height: auto!important;
        max-height: none!important;
    }
  

    .video-section .container{
    max-width: 1340px;
}
}
  
  .video-slider-loading {
    opacity: 0;
    transform: translateY(18px);
    visibility: hidden;
}

.video-slider-ready {
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
    transition: opacity 0.6s ease, transform 0.6s ease;
}
</style>

<script>
jQuery(function ($) {
    $('.video-slider').each(function (index) {
        var $slider = $(this);
        var delay = 150 + index * 120;

        function showSlider() {
            setTimeout(function () {
                $slider
                    .removeClass('video-slider-loading')
                    .addClass('video-slider-ready');
            }, delay);
        }

        function setSliderItemsClass() {
            var videoSliderItems = $slider.find('.owl-stage > .owl-item:not(.cloned)').length;

            if (videoSliderItems === 2) {
                $slider.addClass('video-slider-two-items');
            }

            if (videoSliderItems === 3) {
                $slider.addClass('video-slider-three-items');
            }
        }

        $slider.on('initialized.owl.carousel refreshed.owl.carousel', function () {
            setSliderItemsClass();
            showSlider();
        });

        setTimeout(function () {
            setSliderItemsClass();
            showSlider();
        }, 800);
    });
});
</script>

<?php get_footer(); ?>