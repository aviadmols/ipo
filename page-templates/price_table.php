<?php 
/*
* Template Name: Price Table
*/

get_header(); 

$history = get_field('IPO_history');

$bannerImage = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), 'full' );
$default_banner = get_field('pages_placeholder_image', 'option');
$banner_image_mobile  = get_field('banner_image_mobile_main');

if ($banner_image_mobile) {
 
 echo '<style>
 @media (max-width: 768px){
 .hero_page-section{
     min-height: 265px;
background-image: url(' . wp_get_attachment_url($banner_image_mobile) .')!important;
}
.hero_page-section {
    padding: 0px!important;
    }
    
.hero_page-section>.container {
    position: absolute;
    bottom: 10px;
}
.hero_page-section img{
display: none!important;
}
}
</style>'; 
}

?>

<section class="hero_page-section" >
      <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
<div class="gradient-bottom" > </div>
  <div class="gradient-top"> </div>
        <div class="banner_image"> 
            <?php 

                if($bannerImage) {
                    echo $bannerImage;
                }elseif($default_banner) {
                    $default_banner = new wpstack_image($default_banner);
                    echo $default_banner->get_img();
                }

            ?>
        </div>      

   
</section>


		
       
        <!-- =============== Hero area end =============== -->
<style>
  
  .table_price table {
    width: 100%;
  }
  
  td.price-level,  p.price-level {
    font-family: 'Mandatory PH';
    font-weight: 400;
     font-size: 60px;
    line-height: 1.2;
    letter-spacing: 4px;
}

 

 .table_price  thead {
  
  }
  .table_price th {
     width: 170px;
    padding-left: 25px;
    border-bottom: 1px solid #ef3125;
    padding-bottom: 10px;
    margin-bottom: 30px !important;
    text-align: inherit;

    vertical-align: text-top;
  }


  .table_price table {
    display: block;
}

 .table_price  th {
    border-bottom: 1px solid #ef3125;

    margin-bottom: 30px !important;
  }


.price {
    direction: ltr;
    color: #000;
    font-weight: 400 !important;
}
  
  .info_tit {
display: block!important;
    font-family: 'Simpler';
    font-weight: 400;
    font-size: 20px;
    letter-spacing: 0px;
}
  
.table_price table {
    display: block;
    overflow-x: auto;
}

.table_price table::-webkit-scrollbar {
    display: none;
}

.table_price table {
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
}

.open-search img {
    filter: invert(1)!important;
}

.table_price th {
    min-width: 170px;
  }

@media (max-width: 768px){
  td.price-level, p.price-level {
    font-family: 'Mandatory PH';
    margin-right: -5px!important;
    font-weight: 400;
    font-size: 50px;
    line-height: 1.2;
    letter-spacing: 5px;
}


.table_price th {
    min-width: 130px!important;
  }
  }

</style>

        <!-- =============== Section area start =============== -->
        <section class="about_area" style="margin-top: 0px;background: #f8b2b0;">
            <div class="container max-1440 pt-100 pb-100">           
          
<div class="table_price">
  <p><strong> 
    מחירון
    </strong></p>        
                <h1 style="color: #ef3125!important;">סדרות המנויים 
                        <br>
עונה 88</h1>
 <h1>24/25</h1>

<table class="pb-100 pt-100">
    <thead>
        <tr>
            <th class="price-level">רמת מחיר</th>
            <th>גאלה/קלאסי 
<br>
במוצ"ש</th>
            <th>פילוקלאסיקה
 <br>
קלאסית קלה</th>
            <th>השמיניות 
<br>
הקלאסיות</th>
            <th>שבע ב-7:00</th>
        </tr>
    </thead>
    <tbody>
        <tr>
   <td class="price-level">01</td>
      <td class="price">₪ 4,790</td>
      <td class="price">₪ 2,750</td>
      <td class="price">₪ 3,590</td>
      <td class="price">₪ 3,180</td>

        </tr>
        <tr>
             <td class="price-level">02</td>
      <td class="price">₪ 3,850</td>
      <td class="price">₪ 2,215</td>
      <td class="price">₪ 2,890</td>
      <td class="price">₪ 2,550</td>
        </tr>
        <tr>
             <td class="price-level">03</td>
      <td class="price">₪ 2,915</td>
      <td class="price">₪ 1,680</td>
      <td class="price">₪ 2,185</td>
      <td class="price">₪ 1,935</td>
        </tr>
        <tr>
   <td class="price-level">04</td>
      <td class="price">₪ 1,995</td>
      <td class="price">₪ 1,135</td>
      <td class="price">₪ 1,485</td>
      <td class="price">₪ 1,315</td>
        </tr>
    </tbody>
</table>

<table class="pb-100">
    <thead>
        <tr>
            <th class="price-level">רמת מחיר</th>
            <th>שישי ב-2</th>
            <th>מיני מוצ"ש</th>
            <th>אינטרמצו</th>
            <th>הפילהרמונית
<br>
 בג'ינס</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="price-level">01</td>
                <td class="price">₪ 2,480</td>
      <td class="price">₪ 2,320</td>
      <td class="price">₪ 2,200</td>
      <td class="price">₪ 2,070</td>
        </tr>
        <tr>
            <td class="price-level">02</td>
                <td class="price">₪ 1,995</td>
      <td class="price">₪ 1,870</td>
      <td class="price">₪ 1,785</td>
      <td class="price">₪ 1,660</td>
        </tr>
        <tr>
            <td class="price-level">03</td>
            <td class="price">₪ 1,510</td>
      <td class="price">₪ 1,420</td>
      <td class="price">₪ 1,380</td>
      <td class="price">₪ 1,260</td>
        </tr>
        <tr>
            <td class="price-level">04</td>
          <td class="price">₪ 1,030</td>
      <td class="price">₪ 955</td>
      <td class="price">₪ 970</td>
      <td class="price">₪ 860</td>
        </tr>
    </tbody>
</table>

<table class="pb-100">
    <thead>
        <tr>
            <th class="price-level">רמת מחיר</th>
            <th>ירושלים/
<br>
חיפה א'</th>
            <th>חיפה קלאסית
<br>
קלה
</th>
            <th>ירושלים 6</th>
            <th>ירושלים 4</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="price-level">01</td>
              <td class="price">₪ 3,820</td>
      <td class="price">₪ 2,760</td>
      <td class="price">₪ 2,395</td>
      <td class="price">₪ 1,630</td>
        </tr>
        <tr>
            <td class="price-level">02</td>
                <td class="price">₪ 2,940</td>
      <td class="price">₪ 2,120</td>
      <td class="price">₪ 1,840</td>
      <td class="price">₪ 1,250</td>
        </tr>
        <tr>
            <td class="price-level">03</td>
           <td class="price">₪ 2,050</td>
      <td class="price">₪ 1,480</td>
      <td class="price">₪ 1,280</td>
      <td class="price">₪ 870</td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>הפילהרמונית 
<br>
לילדים</th>
            <th class="price-level"></th>
        </tr>
    </thead>
    <tbody>
        <tr>
 <td class="price-level">מבוגר</td>
     <td class="price">₪ 525</td>
           
        </tr>
        <tr>
    <td class="price-level">ילד
      <span class="info_tit">(עד גיל 18)</span>
</td>
             <td class="price">₪ 263</td>
        
        </tr>
    </tbody>
</table>

<p class="price-level pt-100" style="border-bottom: 1px solid #ef3125;margin-bottom: 10px !important; display: inline-block; line-height: 1;">הסדרה 
<br>
הקאמרית </p>
<p><span class="price">1,070 ₪</span></p>

  <p class=" pt-50"><strong>לתשומת לבכם</strong> 
 המחירים המוצגים הינם מחירי ברוטו ואינם כוללים את ההנחות השונות.</p>
<p>למידע על הנחות והטבות, ניתן לעמודים לשירותכם:</p>
<p>info@ipo.co.il - במייל <br>
              </div>

							<?php  echo the_content();?>
						</div>					
                </div>
        </section>
        <!-- =============== about area end =============== -->




	<?php get_footer(); ?>