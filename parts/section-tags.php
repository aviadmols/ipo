<?php 

$tags_lottie_code = get_field('tags_lottie_code');
$tags_cloud = get_field('tags_cloud');

?>



<section class="links_area" data-load-on-view data-aos="fade-in" data-aos-offset="0" data-aos-duration="500">
            <div class="container">
                <div class="row align-items-center" >
            
                    <div class="col-lg-6 text-left ">
                        <?php echo $tags_lottie_code; ?>
                    </div>

                    <div class="col-lg-6 text-center">

                        <div class="inner-wrapper">

                            <?php $delay = 200; $tags_cloud = array_chunk($tags_cloud, 2); foreach($tags_cloud as $tag){

                                echo '<div class="col-12">';

                                foreach($tag as $item){

                                    $link = $item['tag'];


                                    $link_url = $link['url'];
                                    $link_title = $link['title'];
                                    $link_target = $link['target'] ? $link['target'] : '_self';
                                                                
                                    echo '<a class="btn" target="'.esc_attr( $link_target ).'" style="border: none!important;background:transparent!important;  color: transparent!important; position:relative;" href="'.esc_url( $link_url ).'"
data-aos="zoom-in" data-aos-offset="0" data-aos-duration="500"  data-aos-delay="'. $delay .'"
 >

                                    <span class="btn one slide-in-fwd-center">'.esc_html( $link_title ).' </span>'.esc_html( $link_title ).' </a>';
                                  $delay = $delay + 200;
                                }

                                echo '</div>';


                            } ?>

                        </div>

                    </div>


<?php /*
<div class="col-lg-6 text-center  ">
<div class="row">
<div class="col-12">
<a class="btn" style="border: none!important;background:transparent!important;  color: transparent!important; position:relative;" href="https://ipo.ussl.co/%d7%9c%d7%95%d7%97-%d7%a9%d7%a0%d7%94/">

<span class="btn one slide-in-fwd-center" style="    position: absolute;

margin: 0px;
">לוח קונצרטים</span>לוח קונצרטים</a>
<a class="btn" style="border: none!important; background:transparent!important;  color: transparent!important; position:relative;" href="https://ipo.ussl.co/%d7%99%d7%9c%d7%93%d7%99%d7%9d/">
<span class="btn two slide-in-fwd-center" style="    position: absolute;

margin: 0px;">
עולם הילדים	</span> עולם הילדים</a>
</div>
<div class="col-12">
<a class="btn" style="border: none!important; background:transparent!important;  color: transparent!important; position:relative;" href="#">
<span class="btn three slide-in-fwd-center" style="    position: absolute;
margin: 0px;
">לשמוע קונצרט מהבית</span>
לשמוע קונצרט מהבית</a>
<a class="btn" style="max-width: 170px; border: none!important; background:transparent!important;  color: transparent!important; position:relative;" href="https://ipo.ussl.co/%d7%a1%d7%93%d7%a8%d7%95%d7%aa-%d7%94%d7%a2%d7%95%d7%a0%d7%94-%d7%94-86/">
<span class="btn four slide-in-fwd-center" style="    position: absolute;
max-width: 170px; 
margin: 0px;
">סדרות</span>
סדרות</a>
</div>
<div class="col-12">
<a class="btn" style="border: none!important; background:transparent!important;  color: transparent!important; position:relative;" href="https://ipo.ussl.co/%d7%a7%d7%91%d7%9c%d7%aa-%d7%a7%d7%94%d7%9c-%d7%98%d7%9c%d7%a4%d7%95%d7%a0%d7%99%d7%9d-%d7%95%d7%93%d7%a8%d7%9b%d7%99-%d7%94%d7%aa%d7%a7%d7%a9%d7%a8%d7%95%d7%aa/">
<span class="btn five slide-in-fwd-center " style="    position: absolute; margin: 0px;">מידע שימושי
</span>
מידע שימושי</a>
<a class="btn" style="border: none!important; background:transparent!important;  color: transparent!important; position:relative;" href="#">
<span class="btn six slide-in-fwd-center" style="    position: absolute;

margin: 0px;
">פודקאסטים</span>
פודקאסטים</a>
</div>
</div>
*/ ?>

                    </div>
                </div>
            </div>
        </section>