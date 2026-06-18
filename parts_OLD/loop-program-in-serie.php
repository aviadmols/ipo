<?php 



global $theme;
if(!isset($post_id))
    $post_id = get_the_ID();

$program = new ipo_program($post_id);
$title = get_the_title($post_id);
$permalink = get_the_permalink($post_id);
$program_banner_image = get_field('program_banner_image',$post_id);
$ipo_created_events = get_field('ipo_created_events',$post_id);
$subtitle = get_field('subtitle',$post_id);

$events = $program->get_events();
//print_r(get_field('ipo_created_events',$program->post->ID));

if(!$program_banner_image){
    $program_banner_image = get_post_thumbnail_id($post_id);
}

?>


<div class="img_box img_Contents" data-post_id="<?php echo $post_id; ?>">

<div class="name-contents">
    <div class="position-relative">
        <?php if( $program_banner_image ) : ?>
		 <a href="<?php echo $permalink;?>" class="link img-link ">
            <?php echo wp_get_attachment_image( $program_banner_image, array('500', '140')); ?></a>
        <?php endif; ?>
     
        <a class="playlist"
            href="#"><img src="/wp-content/uploads/2022/08/small_icons.svg" class="w-100" alt=""></a>

    </div>
    <a href="<?php echo $permalink;?>" class="link name-title">
        <?php if($title){ ?>
            <h4><?php echo $title;?></h4>
        <?php } ?>
        <p class="text"><?php echo $subtitle; ?></p>
    </a>
	</div
    <ul>
        <?php 
        $count = count($events);
        $limit = 1;
        if($count > $limit){
            $count = $limit;
        }

        
        $i = 0;
        foreach($events as $event): 

            if($i>=$count){
                break;
            }
            $theme->the_part('loop-serie-event',$event);
            $i++; 

        endforeach; 
        
        ?>

    </ul>
    <?php 
    if($count == $limit){
        echo '
        <a class="additionalDates" href="'.$program->get_link().'"><span>תאריכים<br>נוספים</span>
            <img src="/wp-content/uploads/2022/06/left-arrow.png" class="arrow" alt="">
        </a>'; 
    }
    ?>
</div>