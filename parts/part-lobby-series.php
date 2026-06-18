<?php 


$part = new wpstack_part('lobby-series');

$part->data['e_class'][] = 'recommended-section pt-100 pb-100';

$part->data['e_class_container'][] = '';



global $theme;
$part->build_opening_tag();

$series_title = $part->gf('series_title');
$subtitle = $part->gf('subtitle');

$series = $part->gf('series');




?>

<style>
.recommended-slider {
    display: flex;
    justify-content: space-between;
}

.recommended {
        padding-left: 40px;
}

</style>



<div class="title pb-50">
    <h1><?php echo $series_title;?></h1>
</div>
<div class="slider-content pt-25 pb-25">
    <div class=" recommended-slider">
            <?php foreach($series as $serie) : ?> 
            <div class="recommended">
                        <div class="thumb">
                            
                                <img src="<?php echo  $serie['image'];?>" alt="Img" class="img-fluid">
                        </div><!-- /.thumb -->
                        <div class="rd-text">
                            <div class="text">
                                <h4><a href="<?php echo  $serie['link'];?>"><?php echo  $serie['title'];?></a></h4>
                        </div>
                            <div class="link">
                                <a href="<?php echo  $serie['link'];?>"><?php echo  $serie['location'];?> </a>
                            </div>
                        
                        </div>
                    <div>
                        <p>
                            <a href="<?php echo  $serie['link'];?>"><?php echo  $serie['subtitle'];?></a>
                        </p>
                    </div>
            </div>
        <?php endforeach;?>
                        
                
</div><!-- /.recommended-content -->



<?php

$part->build_closing_tag(); 

?>