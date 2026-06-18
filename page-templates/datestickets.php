<?php 
/*
* Template Name: Dates & Tickets Page
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' ); 
$bannerTitle = get_field('dateticket_banner_title');
$bannerDescription = get_field('dateticket_banner_description');
?>
<div class="main_body">
  <?php if(!empty($bannerImage)){ ?>
  <div class="plan_banner">
    <div class="container">
      <div class="row">
        <div class=" col-md-12 col-sm-12 col-xs-12"> <img src="<?php echo $bannerImage[0]; ?>"> </div>
      </div>
    </div>
  </div>
  <?php } ?>
  <div class="container">
    <div class="row banner_content">
      <div class=" col-md-12 col-sm-12 col-xs-12">
        <?php if($bannerTitle != ''){ ?><h1 class="page_title"><?php echo $bannerTitle; ?></h1> <?php } ?>
        <div class="plan_desc">
           <?php if($bannerDescription != ''){ echo $bannerDescription;  } ?>
        </div>
      </div>
    </div>
    <div class="seprator"></div>
    <div class="date_heading">
      <h3>
      <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				Dates and tickets
							
			<?php }	 else {	?>
					
					  תאריכים וכרטיסים 
						
				<?php }?>
      
     </h3>
    </div>
    <div class="dateandticket">
      <div class="row">
        <div class="col-md-4 col-sm-4 col-xs-12 ticket">
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
		  <h3>Tuesday, November</h3>
          <p>20:30</p>
          <p>The Haifa Auditorium</p>
							
			<?php }	 else {	?>
					
          <h3>שלישי 12 נובמבר</h3>
          <p>20:30</p>
          <p>אודיטוריום חיפה</p>
						
				<?php }?>
         
         
          
          <div class="Haifa">
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			  <p> Haifa First </p>
							
			<?php }	 else {	?>
					
					  <p> חיפה א׳ </p>
						
				<?php }?>
            
            </div>
          <div class="ticket_more"> <a href="javascript:void(0)">
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			Learn more and purchase
							
			<?php }	 else {	?>
					
					  למידע נוסף ורכישה
						
				<?php }?>
          
        </a> </div>
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12 ticket">
         <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
		  <h3>Monday November 11</h3>
          <p>20:30</p>
          <p>The Haifa Auditorium</p>
							
			<?php }	 else {	?>
					
		  <h3>שני 11 בנובמבר</h3>
          <p>20:30</p>
          <p>אודיטוריום חיפה</p>
						
				<?php }?>
         
         
          
          <div class="Haifa">
             <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			  <p> Haifa First<br>
            Mini Haifa A </p>
							
			<?php }	 else {	?>
			 <p> חיפה א׳ <br>
              מיני חיפה א׳ </p>
				<?php }?>
             
              
              </div>
          <div class="ticket_more sold"> <a href="javascript:void(0)">
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			Out of tickets
							
			<?php }	 else {	?>
					
					  אזלו הכרטיסים 
						
				<?php }?>
          
          </a> </div>
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12 ticket">
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
		  <h3>Tuesday, November</h3>
          <p>20:30</p>
          <p>The Haifa Auditorium</p>
							
			<?php }	 else {	?>
					
		  <h3>שלישי 12 נובמבר</h3>
          <p>20:30</p>
          <p>אודיטוריום חיפה</p>
						
				<?php }?>
          
          
          <div class="Haifa">
            
             <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			<p> Haifa First </p>
							
			<?php }	 else {	?>
					<p> חיפה א׳ </p>
						
				<?php }?>
            </div>
          <div class="ticket_more"> <a href="javascript:void(0)">
           
             <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
		Learn more and purchase
        
			<?php }	 else {	?>
					
                     למידע נוסף ורכישה
						
				<?php }?>
          
          
         </a> </div>
        </div>
      </div>
      <div class="seprator"></div>
      <div class="row">
        <div class="col-md-4 col-sm-4 col-xs-12 ticket">
         <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
		  <h3>Tuesday, November</h3>
          <p>20:30</p>
          <p>The Haifa Auditorium</p>
        
			<?php }	 else {	?>
					
          <h3>שלישי 12 נובמבר</h3>
          <p>20:30</p>
          <p>אודיטוריום חיפה</p>
						
				<?php }?>
        
         
          <div class="Haifa">
           
             <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <p>Haifa First </p>
        
			<?php }	 else {	?>
					
                    <p> חיפה א׳  </p>
						
				<?php }?>
           
           
            
            </div>
          <div class="ticket_more"> <a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Learn more and purchase
        
			<?php }	 else {	?>
					
                     למידע נוסף ורכישה
						
				<?php }?>
         </a> </div>
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12 ticket">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
		  <h3>Monday November 11</h3>
          <p>20:30</p>
          <p>The Haifa Auditorium</p>
        
			<?php }	 else {	?>
					
          <h3>שני 11 בנובמבר</h3>
          <p>20:30</p>
          <p>אודיטוריום חיפה</p>
						
				<?php }?>
          
          
          <div class="Haifa">
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				 <p>Haifa First<br>
             Mini Haifa A </p>
        
			<?php }	 else {	?>
					
             <p> חיפה א׳ <br>
              מיני חיפה א׳ </p>
						
				<?php }?>
            
            
              
              </div>
          <div class="ticket_more"> <a href="javascript:void(0)">
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Learn more and purchase
        
			<?php }	 else {	?>
					
                       למידע נוסף ורכישה
						
				<?php }?>
       </a> </div>
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12 ticket">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
	      <h3>Tuesday, November</h3>
          <p>20:30</p>
          <p>The Haifa Auditorium</p>
        
			<?php }	 else {	?>
					
          <h3>שלישי 12 נובמבר</h3>
          <p>20:30</p>
          <p>אודיטוריום חיפה</p>
						
				<?php }?>
         
          <div class="Haifa">
          
               <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				  <p> Haifa First </p>
        
			<?php }	 else {	?>
					
                      <p> חיפה א׳ </p>
						
				<?php }?>
            </div>
          <div class="ticket_more"> <a href="javascript:void(0)">
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				Learn more and purchase
        
			<?php }	 else {	?>
					
                       למידע נוסף ורכישה
						
				<?php }?>
          
          
        </a> </div>
        </div>
      </div>
    </div>
    <div class="date_heading">
       <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				   <h3> About the concert </h3>
        
			<?php }	 else {	?>
					
                      <h3> על הקונצרט </h3>
						
				<?php }?>
      
   
    </div>
    <div class="row the_concert">
      <div class="col-md-6 col-sm-6 col-xs-12"> <img src="<?php echo get_template_directory_uri(); ?>/image/concert.png"> </div>
      <div class="col-md-6 col-sm-6 col-xs-12 rtl">
        <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				  <h2 class="title">The pianist Danil Trypanov</h2>
        <p>Danil Trypanov arrives this time for a longer than usual visit,
          And will be on the orchestra stage during the last two weeks of the
         In June he will play in Bat Yam, Jerusalem and Haifa in three different programs,
        Under the baton of Vasily Petrenko and David Afekam: </p>
        <p>Pianist Martha Argreich, in an interview with the Financial Times: "Trifonov
         He is everything - and much more ... his incredible agility.
         It has softness but also a demonic element. I never heard music
         Like this "</p>
        
			<?php }	 else {	?>
					<h2 class="title">הפסנתרן דניל טריפונוב</h2>
        <p> דניל טריפונוב מגיע הפעם לביקור ארוך מהרגיל, 
          ויתארח על בימת התזמורת במהלך השבועיים האחרונים של 
          חודש יוני הוא ינגן בת»א, ירושלים וחיפה ב-3 תכניות שונות,
          תחת שרביטם של וסילי פטרנקו ודוד אפקם: </p>
        <p> הפסנתרנית מרתה ארגריך, בראיון לפיננשל טיימס: ״טריפונוב 
          הוא הכל – והרבה יותר... זריזות הידיים שלו לא תאומן. 
          יש לו רכות אבל גם אלמנט דמוני. מעולם לא שמעתי נגינה 
          שדומה לזו״ </p>
				<?php }?>
             
      </div>
    </div>
  </div>
</div>

<?php get_footer(); ?>