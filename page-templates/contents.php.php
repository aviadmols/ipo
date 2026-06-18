<?php

/* Template Name: Contents  */ 

get_header();

$title = get_the_title();
$title_override = get_field('title_override');
if($title_override) {
    $title = $title_override;
}

$subtitle = get_field('subtitle');

$banner_image = get_field('banner_image');
$banner_image = new wpstack_image($banner_image);

?>



        <!-- =============== Hero area start =============== -->
		
	    <section class="hero_area-content" style="background-image: url(<?php echo $banner_image->get_src(); ?>); z-index: -1;     max-height: 770px;" >
            <div class="container">
                <div class="content">
                    <h1>><?php echo $title; ?></h1>
                    <p><?php echo $subtitle; ?></p>
                </div>
            </div>
			<div class="gradient-top"></div>
        </section>
		
		
		
		<section class="events-Contents" >
		
		            <div class="container max-1440">
		    <div class="img_box img_Contents">
                                          <div class="position-relative">
<a href="#" class="link img-link ">
                                <img src="/wp-content/uploads/2022/06/upcoming-img-5.png" class="w-100 inner-img" alt="">
</a>
                                <a class="playlist" href="#"><img src="/wp-content/uploads/2022/08/small_icons.svg" class="w-100" alt=""></a>

                            </div>
<a href="#" class="link ">
                            <h4>להב שני מנצח,<br>דניס טריפונוב פסנתרן</h4>
                            <p class="text">פרוקופייב, רחמנינוב ועוד</p>
</a>
                            <ul>
                                <li>
								 <a href="#" class="event-link">
								 
								 <span class="location" >תל אביב</span>
                                      <div>

                                        <p class="date">26.02.22</p>
                                        <p class="d-flex"><span>יום ה׳</span><span>18:30</span></p>

                                    </div>
									               <div>
                                       

                                    </div>
									</a>
                                </li>
                                  <li>
								 <a href="#" class="event-link">
								 <span class="location" >תל אביב</span>
                                      <div>

                                        <p class="date">26.02.22</p>
                                        <p class="d-flex"><span>יום ה׳</span><span>18:30</span></p>

                                    </div>
									               <div>
                                       
                                    </div>
									</a>
                                </li>
                               
                            </ul>
                            <a class="additionalDates" href="#"><span>תאריכים
                                    נוספים</span> <img src="/wp-content/uploads/2022/06/left-arrow.png" class="arrow" alt=""></a>
                        </div>
		
		
		 <div class="img_box img_Contents">
                                          <div class="position-relative">
<a href="#" class="link img-link ">
                                <img src="/wp-content/uploads/2022/06/upcoming-img-5.png" class="w-100 inner-img" alt="">
</a>
                                <a class="playlist" href="#"><img src="/wp-content/uploads/2022/08/small_icons.svg" class="w-100" alt=""></a>

                            </div>
<a href="#" class="link ">
                            <h4>להב שני מנצח,<br>דניס טריפונוב פסנתרן</h4>
                            <p class="text">פרוקופייב, רחמנינוב ועוד</p>
</a>
                            <ul>
                                <li>
								 <a href="#" class="event-link">
								 
								 <span class="location" >תל אביב</span>
                                      <div>

                                        <p class="date">26.02.22</p>
                                        <p class="d-flex"><span>יום ה׳</span><span>18:30</span></p>

                                    </div>
									               <div>
                                       

                                    </div>
									</a>
                                </li>
                                  <li>
								 <a href="#" class="event-link">
								 <span class="location" >תל אביב</span>
                                      <div>

                                        <p class="date">26.02.22</p>
                                        <p class="d-flex"><span>יום ה׳</span><span>18:30</span></p>

                                    </div>
									               <div>
                                       
                                    </div>
									</a>
                                </li>
                               
                            </ul>
                            <a class="additionalDates" href="#"><span>תאריכים
                                    נוספים</span> <img src="/wp-content/uploads/2022/06/left-arrow.png" class="arrow" alt=""></a>
                        </div>
		
		
		</div>
		
		</section>
		
		        <?php get_footer();?>
				
				
				<style>
				
				.hero_area-content {
					
					height: 380px;
					     background-size: contain!important;
    max-height: 770px;
    background-repeat: no-repeat!important;
     display: flex;

    align-items: flex-end;
	background: rgba(239, 163, 139, 1);
				}
				
				.hero_area-content p{
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
				
				
				.img_box.img_Contents{
    justify-content: space-between;
    display: flex;

    padding-top: 5rem;
    padding-bottom: 5rem;
    border-bottom: 1px solid rgba(206, 206, 206, 1);
    margin-bottom: 5rem;
    margin-top: 5rem;
    align-items: end;
}
			
			
		.img_box.img_Contents	ul{
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


.img_box.img_Contents li p , .img_box.img_Contents li .location, .img_box.img_Contents li div{
    width: 100%!important;
	    justify-content: center;
		font-size: 2rem;
	text-align:center!important;
	margin-left: 0px!important;
	margin-right: 0px!important;
 }
 
.img_box.img_Contents li {
    height: 112px;
    width: 162px;
    border: 1px solid rgba(179, 179, 179, 1)!important;
}

.img_box.img_Contents li .location {
    margin-bottom: 5px;
}

 .additionalDates{
     position: relative;
    font-weight: 900;
    max-width: 75px;
    line-height: 1;
    display: flex;
    font-size: 20px;
    height: 40px;
}


  .additionalDates img{
    position: absolute;
    bottom: 0px;
    left: 0px;
}
@media (max-width: 1500px){
.img_box .position-relative {
    margin-left: 25px;
}
}

			</style>