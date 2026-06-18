<?php
global $theme;


if (!function_exists('ipo_parse_event_date_timestamp')) {
    function ipo_parse_event_date_timestamp($date) {
        if (!$date) {
            return PHP_INT_MAX;
        }

        if (preg_match('/^(\d{2})[\/\.-](\d{2})[\/\.-](\d{2})$/', $date, $matches)) {
            return strtotime($matches[1] . '-' . $matches[2] . '-20' . $matches[3]);
        }

        if (preg_match('/^(\d{2})[\/\.-](\d{2})[\/\.-](\d{4})$/', $date, $matches)) {
            return strtotime($matches[1] . '-' . $matches[2] . '-' . $matches[3]);
        }

        $timestamp = strtotime($date);

        return $timestamp ?: PHP_INT_MAX;
    }
}

if (!function_exists('ipo_expand_events_by_program_dates')) {
    function ipo_expand_events_by_program_dates($event_ids) {
        if (empty($event_ids) || !is_array($event_ids)) {
            return $event_ids;
        }

        $expanded_events = [];
        $handled_programs = [];

        foreach ($event_ids as $event_id) {
            $event = new ipo_event($event_id);
            $program_id = $event->get_program();

            if (!$program_id) {
                continue;
            }

            if (in_array($program_id, $handled_programs, true)) {
                continue;
            }

            $handled_programs[] = $program_id;

            $program_event_ids = get_posts([
                'post_type'      => get_post_type($event_id),
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ]);

            $program_events = [];

            foreach ($program_event_ids as $program_event_id) {
                $program_event = new ipo_event($program_event_id);

                if ((int) $program_event->get_program() !== (int) $program_id) {
                    continue;
                }

                if ($program_event->is_passed()) {
                    continue;
                }

                $program_events[] = $program_event_id;
            }

            usort($program_events, function($a, $b) {
                $event_a = new ipo_event($a);
                $event_b = new ipo_event($b);

                return ipo_parse_event_date_timestamp($event_a->get_date()) <=> ipo_parse_event_date_timestamp($event_b->get_date());
            });

            foreach ($program_events as $program_event_id) {
                $expanded_events[] = $program_event_id;
            }
        }

        return array_values(array_unique($expanded_events));
    }
}

get_header();

$subtitle = get_field('subtitle');
$events = get_field('events');

// When "display programs by category" is enabled, replace the manually selected
// events with the upcoming programs from the chosen category_program term.
$use_category_programs  = get_field('use_category_programs');
$category_programs_term = get_field('category_programs_term');
if ($use_category_programs && $category_programs_term) {
    $events = ipo_get_upcoming_event_ids_by_program_category($category_programs_term);
    $events = ipo_expand_events_by_program_dates($events);
}

$banner_image = get_field('banner_image');
$banner_image = new wpstack_image($banner_image);

$hide_banner_content = get_field('hide_banner_content');
$description = get_field('description');
$text_bottom = get_field('text_bottom');
$story_time = get_field('story_time');

$white = get_field('white');
$main_heading_color = get_field('main_heading_color');




$story_time_num_str = "שעת סיפור מס'";
$concert_num_str = "קונצרט מס'";

$passed_event_str = 'אירוע עבר';
$more_info_str = 'למידע ורכישת כרטיסים';

if(defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'en'){
    $story_time_num_str = 'Story time';
    $concert_num_str = 'Concert';
    $passed_event_str = 'Event passed';
    $more_info_str = 'More info and tickets';
}

$program_banner_image_mobile  = get_field('program_banner_image',$post_id);



$class = '';
?>



        <!-- =============== Hero area start =============== -->
	<?php
// קבלת הערך של שדה ACF בשם 'SVG_PC'
$svg_pc = get_field('SVG_PC');

// בדיקה אם השדה 'SVG_PC' מכיל תוכן
if (!empty($svg_pc)) {
    // אם קיים תוכן, הצג את השורטקוד
    echo do_shortcode('[display_svg]');
} else {
    // אחרת, הצג את ה-HTML החלופי
    ?>
    <section class="hero_area-content" style="background-image: url(<?php echo $banner_image->get_src(); ?>); z-index: -1; position: relative; height: 30vw; max-height: 380px;">
        <div class="gradient-bottom" style="max-height: 50%;"></div>
        <div class="gradient-top" style="max-height: 25%;"></div>

        <div class="container">
            <?php
            if ($program_banner_image_mobile) {
                echo '<style> 
                    @media(max-width: 768px) { 
                        .hero_area-content { 
                            background-image: url(' . wp_get_attachment_url($program_banner_image_mobile) . ') !important; 
                        }
                        section.hero_area-content {
                            min-height: 70vw !important;
                            background-position: bottom center !important;
                        }
                        .hero_area-content .content {
                            padding-bottom: 15px !important;
                        }
                        .hero_area-content h1 {
                            font-size: 7rem !important;
                        }
                    }
                </style>';
            }

            if ($white) {
                $class = 'class="white-text"';
            }
            ?>

            <?php if ($hide_banner_content != true): ?>
                <div class="content">
                    <h1 style="color:<?php echo $main_heading_color; ?> !important"><?php the_title(); ?></h1>
                    <?php if (!empty($subtitle)): ?> 
                        <?php if (empty($main_heading_color)) {
                            $main_heading_color = '#fff';
                        } ?>
                        <p style="color:<?php echo $main_heading_color; ?> !important"> <?php echo $subtitle; ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="gradient-top"></div>
    </section>
    <?php
}
?>


<style>
  
  @media (min-width: 769px) {
    .category-event-card-content {
        flex-flow: column;
        display: flex;
        justify-content: space-around;
    }
}
</style>
    


    <?php if($description): ?>
		<section class="description">
            <div class="container max-1440">
                <div class="row">
                    <div class="col-md-12">
                        <div class="content">
                            <?php echo $description; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
		<?php endif; ?>

		<section class="series-Contents pt-10" >
		
		    <div class="container max-1440">
                <?php $i=1; foreach($events as $post_id) : ?>

                    <?php 
                        $event = new ipo_event($post_id);
                        $event_day = $event->get_day_name();

                        $event_date = $event->get_date();

                        //echo get_field('event_date_time', $post_id);

                        $event_time = $event->get_time();
                        $location = $event->get_city();

                        $program_id = $event->get_program();
                        $post = get_post($program_id);
                        $program = new ipo_program($program_id);


                        //$permalink = get_the_permalink($post_id);
                        //$permalink = get_permalink($post_id);
                        //$program_link = get_permalink($program_id);

                        //$permalink = $program_link . '?event_id=' . $post_id;
                        $permalink = $event->get_link();

                        if($program_id && $post){

                            $text_series = $program->gf('text_series');

                            $program_title = $program->get_title();
                            $program_subtitle = $program->get_subtitle();
                            $program_image = $program->get_image();
                            
    
                            $program_artists = $program->gf('program_artists');
                            $preview_text  = $program->gf('preview_text');
                            $program_artists = $program->gf('program_artists');

                        } else {
 $text_series = $event->gf('text_series');
                            // Get event 'event_featured_name' field
                            $program_title = $event->gf('event_featured_name');
                            $program_subtitle = '';
                            $program_image = $program->get_image();
                            $program_artists = '';
                            $preview_text  = '';
                            $program_artists = '';

                        }
                        
                        $program_image = new wpstack_image($program_image);



$delay = 100;

                    ?>

                <div class="row session rtl event-<?php echo $post_id;?>"  data-aos="fade-in" data-aos-duration="20" data-aos-delay="<?php echo $delay; $delay = $delay + 100; ?>" >  
                    
                    <div class="col-md-4 col-sm-4 col-xs-12" data-aos="fade-in" data-aos-duration="20" data-aos-delay="<?php echo $delay; $delay = $delay + 100; ?>">
                    <h3>
    <a href="<?php echo $permalink; ?>" role="link">
        <?php if ($use_category_programs && $category_programs_term): ?>
            <?php echo $program_title; ?>
        <?php else: ?>
            <?php
            if ($story_time) {
                echo $story_time_num_str . ' ';
            } else {
                echo $concert_num_str . ' ';
            }
            ?>
            <?php echo $i; ?>
        <?php endif; ?>
    </a>
</h3>
                        <p class="date"><?php echo $event_date;?></p>
                        <p class="time"><span><strong><?php 

                            // If hebrew date, add 'יום' before day name
                            if(defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'he'){

                                if($event_day == 'שבת') {
                                    $event_day = 'שבת';
                                } else {
                                    $event_day = 'יום '.$event_day;
                                }

                            } else {

                                switch($event_day) {
                                    case 'ראשון':
                                        $event_day = 'Sunday';
                                        break;
                                    case 'שני':
                                        $event_day = 'Monday';
                                        break;
                                    case 'שלישי':
                                        $event_day = 'Tuesday';
                                        break;
                                    case 'רביעי':
                                        $event_day = 'Wednesday';
                                        break;
                                    case 'חמישי':
                                        $event_day = 'Thursday';
                                        break;
                                    case 'שישי':
                                        $event_day = 'Friday';
                                        break;
                                    case 'שבת':
                                        $event_day = 'Saturday';
                                        break;
                                }

                            }

                            echo $event_day;
                        
                        ?></strong></span> | <span><strong><?php 
                        
                            echo $event_time;
                        
                        ?></strong></span></p>
                        <p><?php echo $location; ?></p>
                    </div>

                    <div class="col-md-4 col-sm-4 col-xs-12" data-aos="fade-in" data-aos-duration="500" data-aos-delay="<?php echo $delay; $delay = $delay + 100; ?>">
                      <a href="<?php echo $permalink;?>" role="link">
                        <?php if($program_image):

                            echo $program_image->get_bg_img();
                        
                        /* ?>
                            <img src="<?php echo $program_image->get_src();?>" alt="<?php echo $program_image->get_alt();?>" class="img-responsive">

                        <?php */ endif; ?>
                      </a>
                    </div>
                    

                    
  <div class="col-md-4 col-sm-4 col-xs-12<?php echo ($use_category_programs && $category_programs_term) ? ' category-event-card-content' : ''; ?>" data-aos="fade-in" data-aos-duration="500" data-aos-delay="<?php echo $delay; $delay = $delay + 100; ?>">
                <div class="sess_desc ev-30090">
                    
                  <?php if($program_title && !($use_category_programs && $category_programs_term)) : ?>
    <h2 data-aos="fade-in" data-aos-duration="500" data-aos-delay="<?php echo $delay; $delay = $delay + 50; ?>"><?php echo $program_title; ?></h2>
<?php endif; ?>
                    
                    <?php if($program_subtitle) : ?>
                        <h4 data-aos="fade-in" data-aos-duration="500"  data-aos-delay="<?php echo $delay; $delay = $delay + 50; ?>"><?php echo $program_subtitle;?></h4>
                    <?php endif; ?>

            <?php if($text_series) : ?>
                        <h4 data-aos="fade-in" data-aos-duration="500"  data-aos-delay="<?php echo $delay; $delay = $delay + 50; ?>"><?php echo $text_series;?></h4>
                    <?php endif; ?>
                    <?php if($program_artists) : ?>
                            <?php foreach($program_artists as $artist) : ?>
                                <?php 

                                $role = get_the_terms($artist,'artist_role');
                                $artist_cat = get_the_terms($artist,'artist_cat');

                                $artist_cat = $artist_cat[0]->name;
                                $artist_role = $role[0]->name;

                                $artist_role_override = get_field('artist_role_override',$artist);
                                $artist_cat_override = get_field('artist_cat_override',$artist);

                                if($artist_role_override) {
                                    $artist_role = $artist_role_override;
                                }

                                if($artist_cat_override) {
                                    $artist_cat = $artist_cat_override;
                                }

                                

                                ?>
                                <div class="artist" data-post_id="<?php echo $artist; ?>" style="display: inline-block;" data-aos="fade-in" data-aos-duration="500"  data-aos-delay="<?php echo $delay; $delay = $delay + 50; ?>">

                                    <span class="artist-name" style=""><strong><?php echo get_the_title($artist);?> </strong></span>
                                    <?php if($role) : ?><span class="artist-role"><?php echo $artist_role;?> | </span>
                                    <?php endif; ?>
                                    <?php if($artist_cat) : ?>
                                        <span class="artist-cat"><?php echo $artist_cat;?></span>
                                    <?php endif; ?>

                                </div>

                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if($preview_text) : ?>
                        <p><?php echo $preview_text;?></p>
                    <?php endif; ?>
                                
                </div>
                <div class="sess_desc connected_artist">
                        
                
            
                </div>
                
                <div class="sessdet">
                        </div>
                            <?php if($event->is_passed()): 
                                                               
                                ?>
			<a class="btn series_play mt-25 sersess disabled" href="#">
<?php echo $passed_event_str; ?>
</a>
		<?php else: ?>
      <a href="<?php echo $permalink;?>" class="btn series_play mt-25 sersess" role="link" data-aos="fade-in" data-aos-duration="500"  data-aos-delay="<?php echo $delay; $delay = $delay + 50; ?>">
                                
                            <?php echo $more_info_str; ?>
                                
                                </a> 
		<?php endif; ?>
      
            
                
            
            </div>
            </div>

            <?php $i++; endforeach; ?>
                    
	    </div>


		
        </section>


 <?php

  if($text_bottom): ?>
		<section class="description">
            <div class="container max-1440">
                <div class="row">
                    <div class="col-md-12">
                        <div class="content">
                            <?php echo $text_bottom; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
		<?php endif; ?>


		
<?php get_footer();?>