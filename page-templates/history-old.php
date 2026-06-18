<?php 
/*
* Template Name: Content sections with sidebar
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
$subtitle = get_field('subtitle');
?>


<section class="hero_page-section hide-pc dispaly_for_mobile" style="" >

<div class="gradient-bottom" > </div>
  <div class="gradient-top"> </div>
        <div class="banner_image"> 
            <?php 

$banner_image_mobile  = get_field('banner_image_mobile_main');
           echo '<img src="'.$bannerImage[0].'" />';

if ($banner_image_mobile) {
 
 echo '<style>
 @media (max-width: 768px){
 .hero-section{
     min-height: 265px;
background-image: url(' . wp_get_attachment_url($banner_image_mobile) .')!important;
}
.hero-section {
    padding: 0px!important;
    }
    
.hero-section>.container {
    position: absolute;
    bottom: 10px;
}

}
</style>'; 
}

?>
      <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
        </div>      
<div class="container flex-pc max-1440" style="background: #fff; padding-top: 15px;">
   <div class="content width-25">
<h1 class="title-pages" style="    font-size: 60px!important; color: #000000; margin-right: 0px!important; margin-left: 0px!important; letter-spacing: 3px!important;">
<?php echo get_the_title(); ?></h1>
       
                </div>
</div>
</section>

    <section class="hero-section" style="background-image: url(
 <?php if($bannerImage[0]): ?>
<?php echo $bannerImage[0]; ?>)
  <?php else: ?>
<?php echo esc_url( ipo_theme_uri( 'includes/images/bg/1.jpg' ) ); ?>)<?php endif; ?>;">

             
<div class="gradient-bottom" style="max-height: 50%;">
                                    </div>
          <div class="gradient-top" style="max-height: 25%;">
                                    </div>
                <div class="container">
                    <div class="hero-text">
                        <h1 class="header-title pb-25"><?php echo get_the_title(); ?></h1>
                      	
                      	<?php if($subtitle) : ?>
                        <h3 class="sub-title-simpler white-text"><?php echo $subtitle;?></h3>
                      	<?php endif;?>
                      
                    </div><!-- /.hero-text -->
                </div><!-- /.container -->
            </section><!-- /.hero-section -->


		

<section class="content_rows">
    <div class="container max-1440 mt-100 mb-100">
        <div class="main content flex-100">


                <?php
                
                // get repeater field 'section'
                $sections = get_field('section');
          $z = 1;
                $section_nav = [];
                if($sections){
                    foreach($sections as $section){


                        // Replace spaces with dashes
                        $section_anchor = urldecode(sanitize_title( $section['section_title']));
                        $section_nav[] = ['title' => $section['section_title'], 'anchor' => $section_anchor];
$current_question = isset($_GET['question']) ? intval($_GET['question']) : null;
                        $image = '';
                        if($section['image']){
                            $image = new wpstack_image($section['image'],['e_class' => 'floating-image']);
                        }

                        echo '<div class="content-section mb-25 ">';
                        echo '<div class="anchor" id="question'.$z .'"></div>';
echo '<div class="faq_div"><input id="question'.$z .'" class="questions" name="q" type="checkbox"';
 if($section['open'] || $current_question === $z ){
echo ' checked ';
 }
echo '/>
<div class="plus"><img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></div><label class="question" for="question'.$z .'">'.$section['section_title'].'</label><div class="answers"><div class="text">'.$image.$section['section_content'].'</div></div>
</div>';
$z++;
 
                        echo '</div>';
                    }
                }

                ?>


        </div>
        <div class="sidebar content" style="display: none;">
            <ul class="sections-nav">
                <?php foreach($section_nav as $nav): ?>
                    <li><a href="#<?php echo $nav['anchor']; ?>"><?php echo $nav['title']; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>


<script>
  
  
  document.addEventListener('DOMContentLoaded', () => {
    const urlHash = window.location.hash; 

    if (urlHash.startsWith('#question')) {
        const questionId = urlHash.substring(1); 
        const checkbox = document.getElementById(questionId);
        const section = document.querySelector(`#${questionId}`).closest('.content-section');

        if (checkbox) {
            checkbox.checked = true; 
        }

        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    }

    const labels = document.querySelectorAll(".faq_div label.question");

    labels.forEach((label) => {
        label.addEventListener("click", function (e) {
            const inputId = this.getAttribute("for");
            const inputElements = document.querySelectorAll(`#${inputId}`);

            if (inputElements) {
                const isChecked = inputElements[0].checked;

                document.querySelectorAll(".faq_div input.questions").forEach((input) => {
                    input.checked = false;
                });

                inputElements.forEach((input) => {
                    input.checked = !isChecked;
                });
            }

            e.preventDefault();
        });
    });
});


</script>

<style>

</style>
<?php get_footer(); ?>