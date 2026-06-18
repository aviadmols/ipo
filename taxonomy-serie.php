<?php

/* Template Name: Contents  */ 

get_header();

// Get title of term
$term = get_queried_object();
$title = $term->name;
$title_override = get_field('title_override',$term);
if($title_override) {
    $title = $title_override;
}

$subtitle = get_field('subtitle',$term);

$banner_image = get_field('banner_image',$term);
$banner_image = new wpstack_image($banner_image);

global $theme;

// Get all posts of post_type 'program' that are in the current term
$programs = get_posts(array(
    'post_type' => 'program',
    'numberposts' => 100,
    'tax_query' => array(
        array(
            'taxonomy' => 'serie',
            'field' => 'slug',
            'terms' => $term->slug
        )
    )
));

?>



<!-- =============== Hero area start =============== -->

<section class="hero_area-content"
    style="background-image: url(<?php echo $banner_image->get_src(); ?>); z-index: -1; background-size: cover !important; background-position: center; max-height: 770px;">
    <div class="container">
        <div class="content">
            <h1>
                <?php echo $title; ?>
            </h1>
            <p>
                <?php echo $subtitle; ?>
            </p>
        </div>
    </div>
    <div class="gradient-top"></div>
</section>



<section class="events-Contents">

    <div class="container max-1440">

    <?php 
    foreach($programs as $program):
        $program = new ipo_program($program);
        if($program->get_events()){
            $theme->the_part('loop-program-in-serie',$program->post->ID);
        }
    endforeach; 
    ?>

    </div>

</section>

<?php get_footer();?>


<style>
    .hero_area-content {

        height: 380px;
        background-size: contain !important;
        max-height: 770px;
        background-repeat: no-repeat !important;
        display: flex;

        align-items: flex-end;
        background: rgba(239, 163, 139, 1);
    }

    .hero_area-content p {
        font-family: 'SimplerPro-Light';
        font-size: 28px;
        font-family: 'Simpler';
        font-weight: 300;
        line-height: 1.8;
        margin-right: 1rem;
        color: #fff;
    }

    .hero_area-content>.container {
        padding-bottom: 40px;
    }


    .img_box.img_Contents {
        justify-content: space-between;
        display: flex;

        padding-top: 5rem;
        padding-bottom: 5rem;
        border-bottom: 1px solid rgba(206, 206, 206, 1);
        margin-bottom: 5rem;
        margin-top: 5rem;
        align-items: end;
    }


    .img_box.img_Contents ul {
        display: flex;
        justify-content: space-between;
    }

    .img_Content .img_box a:not(.link) {
        display: inline-flex;
        align-items: center;
        text-align: center;
        flex-flow: wrap;
        justify-content: center;
        align-items: center;
    }

    .img_box.img_Contents li {
        margin-left: 20px;
    }


    .img_box.img_Contents li p,
    .img_box.img_Contents li .location,
    .img_box.img_Contents li div {
        width: 100% !important;
        justify-content: center;
        font-size: 2rem;
        text-align: center !important;
        margin-left: 0px !important;
        margin-right: 0px !important;
    }

    .img_box.img_Contents li {
        height: 112px;
        width: 162px;
        border: 1px solid rgba(179, 179, 179, 1) !important;
    }

    .img_box.img_Contents li .location {
        margin-bottom: 5px;
    }

    .additionalDates {
        position: relative;
        font-weight: 900;
        max-width: 75px;
        line-height: 1;
        display: flex;
        font-size: 20px;
        height: 40px;
    }


    .additionalDates img {
        position: absolute;
        bottom: 0px;
        left: 0px;
    }

    @media (max-width: 1500px) {
        .img_box .position-relative {
            margin-left: 25px;
        }
    }
</style>