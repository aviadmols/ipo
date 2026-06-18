<?php

/* Template Name: series */ 

get_header();

$title = get_the_title();
$title_override = get_field('title_override');
if($title_override) {
    $title = $title_override;
}

$subtitle = get_field('subtitle');

$banner_image = get_field('banner_image');
$banner_image = new wpstack_image($banner_image);

// get events from event type
$events = get_posts(array(
    'post_type' => 'event',
    'numberposts' => 10,
    'fields' => 'ids',
));

$events = ipo_filter_events_with_program( $events );

?>



        <!-- =============== Hero area start =============== -->
		
	    <section class="hero_area-content" style="background-image: url(<?php echo $banner_image->get_src(); ?>); z-index: -1;     max-height: 770px;" >
            <div class="container">
                <div class="content">
                    <h1>קלאסי במוצ”ש</h1>
                    <p><?php echo $subtitle; ?></p>
                </div>
            </div>
			<div class="gradient-top"></div>
        </section>
		
		
		
		<section class="series-Contents" >
		
        <div class="container max-1440">
                <?php $i=1; foreach($events as $post_id) : ?>

                    <?php 
                        $event = new ipo_event($post_id);
                        $event_day = $event->get_day();
                        $permalink = get_the_permalink($post_id);
                        $event_date = $event->get_date();
                        $event_time = $event->get_time();
                        $location = $event->get_location();

                        $program = new ipo_program($event->get_program());

                        $program_title = $program->get_title();
                        $program_subtitle = $program->get_subtitle();
                        $program_image = $program->get_image();
                        $program_image = new wpstack_image($program_image);

                        $program_artists = $program->gf('program_artists');



                    ?>

                <div class="row session rtl event-<?php echo $post_id;?>">  
                    
                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <h3><a href="<?php echo $permalink;?>" role="link">קונצרט מס' <?php echo $i;?></a> </h3>
                        <p class="date"><?php echo $event_date;?></p>
                        <p class="d-flex time"><span><?php echo $event_day;?> </span><span><?php echo $event_time;?></span></p>
                        <p><?php echo $location;?></p>
                    </div>

                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <?php if($program_image) : ?>
                            <img src="<?php echo $program_image->get_src();?>" alt="<?php echo $program_image->get_alt();?>" class="img-responsive">

                        <?php endif; ?>
                        
                    </div>
                    

                    
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="sess_desc ev-30090">
                    
                    <?php if($program_subtitle) : ?>
                        <h4><?php echo $program_subtitle;?></h4>
                    <?php endif; ?>

                    <?php if($program_artists) : ?>
                        <p><strong>אמנים:</strong>
                            <?php foreach($program_artists as $artist) : ?>
                                <?php 

                                $role = get_the_terms($artist,'artist_role');
                                $artist_cat = get_the_terms($artist,'artist_cat');

                                ?>
                                <span class="artist-name"><strong><?php echo get_the_title($artist);?> </strong></span>
                                <?php if($role) : ?>
                                    <span class="artist-role"><?php echo $role[0]->name;?>.</span>
                                <?php endif; ?>
                                <?php if($artist_cat) : ?>
                                    <span class="artist-cat"><?php echo $artist_cat[0]->name;?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                    
                   <strong>באך</strong>: מגניפיקט<br>
                    <strong>פרוקופייב</strong>: סימפוניה מס’ 1 (“קלאסית”)<br>
                    <strong>פרוקופייב</strong>: קונצ’רטו לפסנתר מס’ 3</p>
                                
                </div>
                <div class="sess_desc connected_artist">
                        
                
            
                </div>
                
                <div class="sessdet">
                        </div>
                        
            <a href=" <?php echo $permalink;?>" class="btn series_play mt-25 sersess" role="link">
                                
                            למידע ורכישת כרטיסים
                                
                                </a> 
            
                
            
            </div>
            </div>

            <?php $i++; endforeach; ?>
                    
	    </div>


   
		
		</section>
		
		        <?php get_footer();?>