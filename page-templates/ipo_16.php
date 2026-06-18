<?php 
/*
* Template Name: IPO 16 Page
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' ); ?>
<div class="main_body">
  <div class="container">
    <h1 class="page_title"><?php echo get_the_title(); ?></h1>
    <?php if(!empty($bannerImage)){ ?>
        <div class="row">
          <div class=" col-md-12 col-sm-12 col-xs-12 banner_image"> <img src="<?php echo $bannerImage[0]; ?>"> </div>
        </div>
    <?php } ?>
    <div class="row banner_content">
      <div class=" col-md-6 col-sm-6 col-xs-12">
       <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				 <p>He is the first violinist of the Israeli quartet (1978), with whom he frequently performs in Israel. As part of his extensive activity in chamber music, he performed several times at the Israel Festival, </p>
        <p>Among others, playing Mozart's violin and piano sonatas with pianist Jonathan Zack, and performing at the Kfar Blum Festival and international festivals abroad, where he also teaches master classes in violin and chamber music. He has performed several times as soloist with the Israel Philharmonic Orchestra, among others under Zubin Mehta.</p>
							
			<?php }	 else {	?>
					
				  <p>הוא כנר ראשון של הרביעייה הישראלית (מ-1978), שעמה הוא מרבה להופיע בארץ,במסגרת פעילותו הענפה במוסיקה קאמרית הופיע מספר פעמים בפסטיבל ישראל, </p>
        <p>בין השאר בנגינת כל הסונטות לכינור ולפסנתר מאת מוצרט עם הפסנתרן יונתן זק, וכן הופיע בפסטיבל כפר בלום ובפסטיבלים בינלאומיים בחו»ל, שבהם הוא גם מדריך בכיתות אמן בכינור ובמוסיקה קאמרית. הוא הופיע פעמים אחדות כסולן עם התזמורת הפילהרמונית הישראלית, בין היתר תחת שרביטו של זובין מהטה.</p>
						
				<?php }?>
      
      </div>
      <div class=" col-md-6 col-sm-6 col-xs-12">
      <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <p>Joshua Bell, principal player of the Israel Philharmonic Orchestra, was born in Israel and holds an artist's degree from the Rubin Academy of Music at Tel Aviv University, where he studied with Professor Levov and Prof. Bondarenko. He won first prize in the Academy's violinist competition and in the Paganini international competition.</p>
        <p>In 1987, he joined the Israel Philharmonic Orchestra as a principal player, after serving for five years in the Jerusalem Symphony Orchestra. Prior to that he was a principal player of the Israel Chamber Orchestra and of the Israel Sinfonietta Beer Sheva.</p>
							
			<?php }	 else {	?>
					
					 <p>ג›ושוע בל, נגן ראשי של התזמורת הפילהרמונית הישראלית, נולד בישראל והוא בעל תואר אמן של האקדמיה למוסיקה ע»ש רובין באוניברסיטת תל אביב, שם למד אצל פרופ› שבלוב ופרופ› בונדרנקו. הוא זכה בפרס הראשון בתחרות הכנרים של האקדמיה ובדיפלומה בתחרות הבינלאומית ע»ש פגניני.</p>
        <p>ב-1987 הצטרף לתזמורת הפילהרמונית הישראלית כנגן ראשי, לאחר שכיהן בתפקיד זה במשך חמש שנים בתזמורת הסימפונית ירושלים. קודם לכן היה נגן ראשי של התזמורת הקאמרית הישראלית ושל הסינפונייטה הישראלית ב״ש.</p>
						
				<?php }?>
      
       
      </div>
    </div>
    <div class="sessionguest sidebar_desktop">
      <h3 class="sessiontitle">
      <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				And Joshua Bell will appear with us this season in the following programs
							
			<?php }	 else {	?>
					
					  ג›ושוע בל יופיע עמנו העונה בתוכניות הבאות 
						
				<?php }?>
     </h3>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
            
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			 <p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
			 <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b>Rachmaninoff: </b>Piano Concerto No. 2
							
			<?php }	 else {	?>
					
					 <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2 
						
				<?php }?>
          </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					 <b> צ’ייקובסקי: </b> סימפוניה מס’ 4 
						
				<?php }?>
          
          </p>
          <a href="#" class="session_more">
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					  Learn and purchase tickets
							
			<?php }	 else {	?>
					
					  למידע ורכישת כרטיסים 
				<?php }?>
         </a> </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				Plan 1
							
			<?php }	 else {	?>
					
					 תוכנית 1 
				<?php }?>
          </a> </h3>
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
		 <h4>Thursday <br>
          16 October</h4>
          <p> 19:00 </p>
          <p>Charles Bronfman Hall of Culture</p>
							
			<?php }	 else {	?>
					
		 <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
				<?php }?>          
         </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				<p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
			<p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
            
            
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				  <b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
				  <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2
						
				<?php }?>
          </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				   <b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
				   <b> צ’ייקובסקי: </b> סימפוניה מס’ 4 
						
				<?php }?>
         </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				Learn and purchase tickets
							
			<?php }	 else {	?>
					
				 למידע ורכישת כרטיסים 
						
				<?php }?>
          </a> </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				Plan 2
							
			<?php }	 else {	?>
					
				  תוכנית 2
						
				<?php }?>
          </a> </h3>
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			<h4> Thursday <br>
            16 October </h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
				<h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
          
        </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
		    <p> Vasily Petrenko, conductor </p>
            <p> Victoria Yesterbova, soprano </p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
	     	 <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
           
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
					 <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2
						
				<?php }?>
           </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					  <b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					  <b> צ’ייקובסקי: </b> סימפוניה מס’ 4 
						
				<?php }?>
         </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 Learn and purchase tickets
							
			<?php }	 else {	?>
					
					  למידע ורכישת כרטיסים 
						
				<?php }?>
         </a> </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Plan 3
							
			<?php }	 else {	?>
					
					  תוכנית 3
						
				<?php }?>
          </a> </h3>
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
		 <h4> Thursday <br>
           16 October </h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
		 <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
         
        </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			 <p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
		   <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
           
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
					<b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2
						
				<?php }?>
            </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<b>Tchaikovsky:  </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					<b> צ’ייקובסקי: </b> סימפוניה מס’ 4
						
				<?php }?>
            </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				Learn and purchase tickets 
							
			<?php }	 else {	?>
					
					 למידע ורכישת כרטיסים 
						
				<?php }?>
          </a> </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			Plan 4
							
			<?php }	 else {	?>
					
				 תוכנית 4
						
				<?php }?>
          
           </a> </h3>
           
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			<h4> Thursday <br>
           16 October</h4>
          <p> 19:00 </p>
          <p>Charles Bronfman Hall of Culture</p>
							
			<?php }	 else {	?>
					
			<h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
        </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
           <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				<p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
		   <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
           
            
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
					 <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2
						
				<?php }?>
           </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					<b> צ’ייקובסקי: </b> סימפוניה מס’ 4
						
				<?php }?>
            </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 Learn and purchase tickets
                     		
			<?php }	 else {	?>
					
					  למידע ורכישת כרטיסים 
						
				<?php }?>
         </a> </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Plan 5
							
			<?php }	 else {	?>
					
					  תוכנית 5
						
				<?php }?>
          </a> </h3>
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				   <h4>Thursday<br>
           16 October </h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
					   <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
       
        </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					  <p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
					  <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
          
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
					 <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2 
						
				<?php }?>
          </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					<b> צ’ייקובסקי: </b> סימפוניה מס’ 4 
						
				<?php }?>
           </p>
          <a href="#" class="session_more"> 
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 Learn and purchase tickets
							
			<?php }	 else {	?>
					
					  למידע ורכישת כרטיסים 
						
				<?php }?>
        </a> </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Plan 6 </a> </h3>
          <h4> Thursday <br>
            16 October </h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
					 תוכנית 6 </a> </h3>
          <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
        </div>
      </div>
    </div>
    <div class="sessionguest sidebar_mobile">
      <h3 class="sessiontitle">
      <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					And Joshua Bell will appear with us this season in the following programs
							
			<?php }	 else {	?>
					
					 ג›ושוע בל יופיע עמנו העונה בתוכניות הבאות 
						
				<?php }?>
      </h3>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 Plan 1 </a> </h3>
          <h4> Thursday <br>
           16 October</h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
					  תוכנית 1 </a> </h3>
          <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
         
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
					 <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
           
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
				 <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2 
						
				<?php }?>
          </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					 <b> צ’ייקובסקי: </b> סימפוניה מס’ 4
						
				<?php }?>
           </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
			Learn and purchase tickets
							
			<?php }	 else {	?>
					
					 למידע ורכישת כרטיסים 
						
				<?php }?>
          </a> </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Plan 2 </a> </h3>
          <h4> Thursday <br>
           16 October </h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
					 תוכנית 2 </a> </h3>
          <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
          
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
					 <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
           
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
					<b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2
						
				<?php }?>
            </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					 <b> צ’ייקובסקי: </b> סימפוניה מס’ 4
						
				<?php }?>
           </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Learn and purchase tickets
							
			<?php }	 else {	?>
					
					למידע ורכישת כרטיסים 
						
				<?php }?>
           </a> </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				Plan 3 </a> </h3>
          <h4> Thursday <br>
           16 October </h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
				תוכנית 3 </a> </h3>
          <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
          
           
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				 <p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
			 <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
          
           
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
					 <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2 
						
				<?php }?>
          </p>
          <p class="sessdet"> 
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					 <b> צ’ייקובסקי: </b> סימפוניה מס’ 4
						
				<?php }?>
          </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 Learn and purchase tickets
							
			<?php }	 else {	?>
					
					 למידע ורכישת כרטיסים 
						
				<?php }?>
          </a> </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Plan 4 </a> </h3>
          <h4> Thursday <br>
           16 October </h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
					 תוכנית 4 </a> </h3>
          <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
					 <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
           
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				 <b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
				 <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2 
						
				<?php }?>
          </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					 <b> צ’ייקובסקי: </b> סימפוניה מס’ 4 
						
				<?php }?>
          </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Learn and purchase tickets
							
			<?php }	 else {	?>
					
					  למידע ורכישת כרטיסים 
						
				<?php }?>
         </a> </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)"> 
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Plan 5 </a> </h3>
          <h4> Thursday <br>
            16 October </h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
				 תוכנית 5 </a> </h3>
          <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
         
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
					<p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
            
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
					 <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2
						
				<?php }?>
           </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					<b> צ’ייקובסקי: </b> סימפוניה מס’ 4 
						
				<?php }?>
           </p>
          <a href="#" class="session_more"> 
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Learn and purchase tickets
							
			<?php }	 else {	?>
					
					 למידע ורכישת כרטיסים 
						
				<?php }?>
         </a> </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)"> 
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Plan 6 </a> </h3>
          <h4> Thursday <br>
           16 October</h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
					תוכנית 6 </a> </h3>
          <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					  <p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
					  <p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
          
          
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
					 <b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2
						
				<?php }?>
           </p>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<b>Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					<b> צ’ייקובסקי: </b> סימפוניה מס’ 4
						
				<?php }?>
            </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Learn and purchase tickets
							
			<?php }	 else {	?>
					
					 למידע ורכישת כרטיסים 
						
				<?php }?>
          </a> </div>
      </div>
      <div class="row session rtl">
        <div class="col-md-4 col-sm-4 col-xs-12">
          <h3><a href="javascript:void(0)">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
				 Plan 7</a> </h3>
          <h4> Thursday <br>
           16 October </h4>
          <p> 19:00 </p>
          <p> Charles Bronfman Hall of Culture </p>
							
			<?php }	 else {	?>
					
					  תוכנית 7 </a> </h3>
          <h4> יום חמישי <br>
            16 לאוקטובר </h4>
          <p> 19:00 </p>
          <p> היכל התרבות ע»ש צ›רלס ברונפמן </p>
						
				<?php }?>
          
         
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12"> <img src="image/img6.png"> </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
          <div class="sess_desc">
            <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<p>Vasily Petrenko, conductor</p>
            <p>Victoria Yesterbova, soprano</p>
            <p>Misha Didik, tenor</p>
            <p>Alexander Winogradov, bass</p>
							
			<?php }	 else {	?>
					
					<p>וסילי פטרנקו, מנצח</p>
            <p>ויקטוריה יסטרבובה, סופרן</p>
            <p>מישה דידיק, טנור</p>
            <p>אלכסנדר וינוגרדוב, באס</p>
						
				<?php }?>
            
            
          </div>
          <p class="sessdet">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					<b>Rachmaninoff: </b> Piano Concerto No. 2
							
			<?php }	 else {	?>
					
				<b>רחמנינוב: </b> קונצ’רטו לפסנתר מס’ 2
						
				<?php }?>
            </p>
          <p class="sessdet"> 
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					 <b> Tchaikovsky: </b> Symphony No. 4
							
			<?php }	 else {	?>
					
					 <b> צ’ייקובסקי: </b> סימפוניה מס’ 4 
						
				<?php }?>
         </p>
          <a href="#" class="session_more">
          <?php if(ICL_LANGUAGE_CODE=='en'){ ?>
					
					Learn and purchase tickets	
							
			<?php }	 else {	?>
					
					 למידע ורכישת כרטיסים 
						
				<?php }?>
          </a> </div>
      </div>
    </div>
  </div>
</div>
<?php get_footer(); ?>
