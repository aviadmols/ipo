<?php
/**
 * New Theme Artist Plan Template
 * This template is used when the new-theme ACF field is checked
 */


// Get language strings
$read_more_str = 'קרא עוד';
$the_program_str = 'התכנית';
$artists_str = 'האמנים';
$buy_tickets_str = 'לרכישת כרטיסים';
$tickets_str = 'לרכישה';
$event_passed_str = 'האירוע עבר';
$intermission_str = 'הפסקה';
$program_number_str = 'תכנית מס\'';
$read_less_str = 'קרא פחות';

if(ICL_LANGUAGE_CODE == 'en') {
    $read_more_str = 'Read More';
     $program_number_str = 'Concert no.';
    $read_less_str = 'Read Less';
    $the_program_str = 'The Program';
    $artists_str = 'The Artists';
    $buy_tickets_str = 'Order Tickets';
    $tickets_str = 'Tickets';
    $event_passed_str = 'Event Passed';
    $intermission_str = 'Intermission';
}

// Banner title color
$banner_title_style = '';
if ($new_theme_banner_title_color) {
    $banner_title_style = 'style="color:' . $new_theme_banner_title_color . ';"';
}

?>

  <style>
    @font-face {
      font-family: 'mandatory';
      src: url('<?php echo esc_url( ipo_theme_uri( "assets/fonts/MandatoryVariable.woff2" ) ); ?>') format('woff2'),
    }
  </style>

<style>
/* Custom Fonts */
@font-face {
    font-family: 'Mandatory Variable';
      src: url('<?php echo esc_url( ipo_theme_uri( "assets/fonts/MandatoryVariable.woff2" ) ); ?>') format('woff2'),

}

  
 [lang="en-US"] .new-theme-event-title,  [lang="en-US"]  .new-theme-section-title , [lang="en-US"]  .new-theme-info-item p, [lang="en-US"] .about-program-text{
        text-align: left!important;
  }
  
    [lang="en-US"]  .new-theme-program-list li span {
    text-align: right !important;
}

   [lang="en-US"]     .new-theme-event-details {
        text-align: left!important;
  }

   [lang="en-US"]  .new-theme-program-list li img {
      margin-left: 0px!important;
  margin-right: 10px;
  }
  
   [lang="en-US"]     .new-theme-event-readmore {
        float: right !important;
  }
  
  .new-theme-event-readmore {
    display: none!important;
  }
  
  .new-theme-banner-title,  .new-theme-event-title, .new-theme-event-details, .new-theme-section-title {
    font-variation-settings: "wght" 400, "wdth" 20;
  }
  
/* New Theme Styles */
.new-theme-banner {
    position: relative;

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
       align-items: flex-start;
    justify-content: center;
}
  
  .max-width-30d {
    max-width: 30%;
  }
  
  .banner-image {
    width: 100%;
     top: 0px;
    bottom: 0px;
}

  .border-div-d {
        font-weight: 100!important;
  }
.new-theme-banner-content {
      text-align: center;
    z-index: 2;
    position: relative;
    position: absolute;
    bottom: 0px;
}
  
  .new-theme-program-list li span {
        text-align: left;
  }

.new-theme-banner-title {
    font-family: 'Mandatory Variable', sans-serif;
    font-weight: 356;
    font-style: normal;
    font-size: 100px;
    line-height: 100%;
    letter-spacing: 8px;
    text-align: center;
    margin-bottom: 90px;
}

.new-theme-banner-readmore {
    display: inline-block;
    padding: 12px 30px;
    background: transparent;
    border: 2px solid #fff;
    color: #fff;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.new-theme-banner-readmore:hover {
    background: #fff;
    color: #000;
}

.new-theme-event-item {
    margin-bottom: 4rem;
    box-shadow: 0px 4px 35px 0px #00000026;
    border-radius: 0px;
       margin-top: 8rem;
}

  
  .div-theme-tit {
    
  }
.new-theme-event-header {
    position: relative;
    padding: 30px 25px 24px 25px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 200px;
}

.new-theme-event-image {
      position: absolute;
    bottom: 0px;
  width: 35%;
  z-index: 1;
      object-fit: contain;
    max-height: 144%;
    object-position: right;
    right: 0px;
    left: auto;
}
  
  .div-theme-tit {
        z-index: 1;
        margin-right: 35%;
  }

.new-theme-event-title {
      margin-bottom: 14px;
    font-family: 'Mandatory Variable', sans-serif;
text-align: right;
z-index: 1;
font-size: 100px;
font-style: normal;
font-weight: 640;
line-height: 0.8; /* 81.87px */
letter-spacing: 8px;
    color: #FFD700;
}

.new-theme-event-details {
    font-family: 'Mandatory Variable', sans-serif;
text-align: right;
font-family: "Mandatory Variable";
font-size: 64px;
font-style: normal;
font-weight: 356;
line-height: normal;
letter-spacing: 3px;
    color: #FFB6C1;
}

.new-theme-event-readmore {
    position: relative;
    z-index: 2;
    background: transparent;
    border: 1px solid currentColor;
    color: inherit;
    padding: 10px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: none; /* Hide by default */
}

.new-theme-event-readmore:hover {
    background: currentColor;
    color: #fff;
}

.new-theme-event-content {

  background: white;
    padding: 65px 90px;
}
  
  .border-div {
        display: block;
    width: 1px;
    background: #000;
  }

.new-theme-content-grid{
    display: flex
;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    margin-bottom: 80px;
}

  
   .footer_section{
    display: flex;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    margin-bottom: 3rem;
}
  
.new-theme-section-title {
    font-family: 'Mandatory Variable', sans-serif;
    font-weight: 300;
    font-style: normal;
    font-size: 36px;
    line-height: 100%;
    letter-spacing: 0;
    text-align: right;
    margin-bottom: 2rem;
    color: #333;
    padding-bottom: 0px;
}

.new-theme-program-list {
    list-style: none;
    padding: 0;
}

.new-theme-program-list li {
font-family: 'Simpler', sans-serif;
    font-weight: 700;
    display: flex;
    font-size: 18px;
    line-height: 140%;
    letter-spacing: 0px;
    text-align: right;
     justify-content: space-between;
       padding: 14px 0px;
    border-bottom: 1px solid #000;
}

.new-theme-program-list li:last-child {
    border-bottom: none;
}

.new-theme-program-list li strong {
    display: block;
    margin-bottom: 0.3rem;
    font-weight: 700;
    color: #333;
}

.new-theme-program-list li span {
    font-weight: 400;
    color: #666;
}

.new-theme-artists-section {
    padding-right: 2rem;
      width: 50%;
}
  

    .width50 {
    width: 50%;
    display: flex;
    align-items: flex-end;
}


  

.new-theme-program-section {
      width: 50%;
    padding-left: 2rem;
}

.new-theme-artist-item {
font-family: 'Simpler', sans-serif;
    font-weight: 700;
    display: flex;
    font-size: 20px;
    line-height: 140%;
    letter-spacing: 0px;
    text-align: right;
     justify-content: space-between;
       padding: 14px 0px;
    border-bottom: 1px solid #000;
}

.new-theme-artist-name {
    font-family: 'Simpler', sans-serif;
    font-weight: 700;
    font-size: 20px;
    line-height: 120%;
    letter-spacing: 2%;
    text-align: right;
    margin-bottom: 0.5rem;
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.new-theme-artist-name:hover {
    color: #666;
    text-decoration: underline;
}

.new-theme-artist-role {
    font-family: 'Simpler', sans-serif;
    font-weight: 400;
    font-size: 20px;
    line-height: 120%;
    letter-spacing: 2%;
    text-align: right;
    color: #666;
}

.new-theme-event-info {
    display: flex;
  width: 100%;
    align-items: flex-start;
    gap: 3rem;
}

.new-theme-info-item {
     display: grid;
    align-items: flex-start;
    gap: 0.8rem;
    flex: 1;
}
  
  .new-theme-info-item img {
    width: 20px;
  }

.new-theme-info-item .icon {
    width: 24px;
    height: 24px;
    margin-top: 2px;
    flex-shrink: 0;
}

.new-theme-info-item p {
    font-family: 'Simpler', sans-serif;
    font-weight: 400;
    font-size: 18px;
      position: relative;
    line-height: 140%;
    letter-spacing: 2%;
    text-align: right;
    margin: 0;
    color: #333;
}

.new-theme-info-item p .location {
    display: block;
    font-weight: 600;
    margin-bottom: 0.3rem;
}

.new-theme-info-item p .hall {
    font-weight: 400;
    color: #666;
      bottom: -20px;
      position: absolute;
}

.new-theme-buy-button {
    display: inline-block;
    padding: 15px 40px;
    background: currentColor;
    color: inherit;
  color: #fff;
    border: 2px solid currentColor;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 600;
    font-size: 18px;
}
  
  .new-theme-buy-button span {
    color: #fff;
  }



.new-theme-buy-button:hover {
    background: currentColor;
    color: inherit;
}

.new-theme-buy-button.passed {
    background: #ccc;
    cursor: not-allowed;
    box-shadow: none;
}

.new-theme-buy-button.passed:hover {
    transform: none;
}

/* About Program Button and Content */
.about-program-wrapper {
    margin-bottom: 3rem;
    text-align: center;
}

.about-program-toggle {
    display: inline-block;
    padding: 12px 40px;
    background: transparent;
    border: 2px solid #333;
    color: #333;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.about-program-toggle:hover {
    background: #333;
    color: #fff;
}

.about-program-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease;
    margin-top: 1.5rem;
}

.about-program-content.show {
    max-height: 2000px;
}

.about-program-text {
    font-family: 'Simpler', sans-serif;
    font-size: 18px;
    line-height: 160%;
    color: #333;
    padding: 30px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0px 2px 15px 0px rgba(0,0,0,0.1);
    text-align: right;
}

/* Ultra Wide Desktop Styles (1920px and up) */
@media (min-width: 1920px) {
    .new-theme-event-content {
        padding: 100px 150px;
    }
    
    .new-theme-event-title {
        font-size: 140px;
        letter-spacing: 5px;
    }
    
    .new-theme-event-details {
        font-size: 84px;
    }
    
    .new-theme-section-title {
          letter-spacing: 2px;
        font-size: 50px;
    }
    
    .new-theme-program-list li,
    .new-theme-artist-item {
        font-size: 24px;
        padding: 18px 0px;
    }
}

  @media (min-width: 1440px) and (max-width: 1850px) {
    .new-theme-banner-title {    
        font-size: 80px;
          letter-spacing: 8px;
          margin-bottom: 60px;
    }
    
   .new-theme-event-title {
          font-size: 90px;
          letter-spacing: 6px;
    }
    
    .new-theme-event-details {
        letter-spacing: 4px;
        font-size: calc(90px * 0.67)!important;
    }
  }
  
  
    @media (min-width: 1100px) and (max-width: 1440px) {
    .new-theme-banner-title {    
          letter-spacing: 6px;
        font-size: 80px;
          margin-bottom: 50px;
    }
      .new-theme-event-title {
        font-size: 80px; 
           letter-spacing: 6px;
      }
      
       .new-theme-event-details {
           letter-spacing: 4px;
        font-size: calc(80px * 0.67)!important;
    }
  }
  
  
    @media (min-width: 920px) and (max-width: 1100px) {
    .new-theme-banner-title {    
        font-size: 70px;
          letter-spacing: 5px;
          margin-bottom: 50px;
    }
      .new-theme-info-item p {
  
    font-size: 14px;
      }

      .new-theme-event-title {
         font-size: 70px;
           letter-spacing: 6px;
      }
        
       .new-theme-event-details {
            letter-spacing: 4px;
        font-size: calc(70px * 0.67)!important;
    }
  }
  
  
      @media (min-width: 768px) and (max-width: 920px) {
        
             .new-theme-info-item p {
  
    font-size: 16px;
      }
        
        
    .new-theme-banner-title {    
      
        margin-bottom: 50px;
        font-size: 65px;
          letter-spacing: 1px;
    }
        
        .new-theme-event-title {
           font-size: 65px;
        }
        
         .new-theme-event-details {
        font-size: calc(65px * 0.67);
    }
  }
  
/* Large Desktop Styles (1440px - 1919px) */
@media (min-width: 1440px) and (max-width: 1919px) {
    .new-theme-event-content {
        padding: 80px 120px;
    }
    


    
    .new-theme-section-title {
        font-size: 45px;
          letter-spacing: 2px;
    }
    
    .new-theme-program-list li,
    .new-theme-artist-item {
        font-size: 22px;
        padding: 16px 0px;
    }
}

/* Desktop Styles (1200px - 1439px) */
@media (max-width: 1439px) and (min-width: 1200px) {
    .new-theme-event-content {
        padding: 70px 100px;
    }
    

   
}

/* Laptop Styles (992px - 1199px) */
@media (max-width: 1199px) and (min-width: 992px) {
    .new-theme-event-content {
        padding: 60px 80px;
    }
    


    
    .new-theme-section-title {
        font-size: 40px;
          letter-spacing: 2px;
    }
    
    .new-theme-program-list li {
        font-size: 18px;
    }
    
    .new-theme-artist-item {
        font-size: 18px;
    }
}

/* Tablet Styles (768px - 991px) */
@media (max-width: 991px) and (min-width: 768px) {
    .new-theme-event-content {
        padding: 50px 60px;
    }
  
    
  
    
    .new-theme-section-title {
        font-size: 35px;
          letter-spacing: 2px;
    }
    
    .new-theme-program-list li, .new-theme-artist-name  {
        font-size: 16px;
    }
    
    .new-theme-artist-item {
        font-size: 16px;
    }
    
    .new-theme-content-grid {
        gap: 2rem;
        margin-bottom: 60px;
    }
    
    .footer_section {
        gap: 2rem;
    }
    
    .about-program-toggle {
        font-size: 17px;
        padding: 11px 35px;
    }
    
    .about-program-text {
        font-size: 17px;
        padding: 25px;
    }
}

  @media (min-width: 768px) {

    .new-theme-banner {
      padding-top: 50px;
    }
    
      .hide-pc-01 {
    display: none!important;
  }
    
  }
  

/* Mobile Styles */
@media (max-width: 768px) {
  
  .new-theme-artist-role {
     font-size: 14px!important;
  }
    .new-theme-artist-name {
    font-family: 'Simpler', sans-serif;
    font-weight: 700;
    font-size: 14px!important;
    
  }
  .new-theme-banner-title {
           font-size: 30px !important;
        letter-spacing: 2px;
    }
  
      .new-theme-content-grid {
              margin-bottom: 35px;
        padding-top: 15px;
        padding-bottom: 0px;
    }
  
 .new-theme-event-header {
        min-height: 0!important;
  }
  
  .hide-mobile-01 {
    display: none!important;
  }
  
  .new-theme-banner-title {
            
        letter-spacing: 2px;
        padding-top: 30px;
        margin-bottom: 0px !important;
        padding-bottom: 40px !important;
    font-size: 30px !important;
    line-height: 100%;
    letter-spacing: 0;
            margin-top: 0!important;
  }
  
  .location-div {
    min-width: 100%!important;
  }

  .new-theme-event-info {
    display: flex;
    flex-flow: wrap;
  }
  
  .new-theme-banner-content {
     position: unset;
  }

  .new-theme-banner {
    display: block!important;
  }
  .new-theme-banner-title {
      padding-right: 15px;
    padding-left: 15px;
}
      .new-theme-buy-button {
        width: 100%;
        padding: 10px 40px;
        font-size: 16px;
        text-align: center;
    }
  
  
      .new-theme-event-info {
          gap: 10px;
  }
  .new-theme-info-item p .hall {
    position: unset !important;
}
  
  .new-theme-artist-name {
    font-family: 'Simpler', sans-serif;
    font-weight: 700;
    font-size: 14px;
    color: #333;
  }
  
  .new-theme-artist-name:hover {
    color: #666;
  }
  
  
  .new-theme-event-header {
    position: relative;
    padding: 25px;
    color: white;
    display: inline-block!important	;
    z-index: 8;
  }
  
  .new-theme-event-item {
        margin-top: 0%;
        margin-bottom: 40px;
    box-shadow: none !important;
    border-radius: 0px;
  }
  
  .div-theme-tit {
     z-index: 1;
        padding-top: 20px;
    margin-right: 0;
    position: relative;
}
  
      .new-theme-event-image {
           position: absolute;
        margin-bottom: 0px;
        width: 30%;
        max-height: 250px;
        min-width: 45%!important;
        width: 30%;
        right: -15px;
                bottom: -20px;
  }
 
  .tit-div {
        
    position: relative;
}
  
      .new-theme-event-readmore {
             margin-top: 10px;
        padding: 5px 15px;
        display: inline-block;
        font-size: 12px;
    float: left;
    }
  
    .new-theme-content-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
        display: block;
    }
    
    .new-theme-content-grid.show {
        display: flex;
        flex-direction: column;
    }
    
  
    
    .new-theme-event-header {
        padding: 1.5rem;
    }
    
    .new-theme-event-content {
        padding: 1.5rem;
    }
    
    .new-theme-artists-section,
    .new-theme-program-section {
        width: 100%;
        padding: 0;
    }
    
    .footer_section {
        flex-direction: column;
        gap: 10px;
    }
    
    .width50 {
        width: 100%;
    }
    
      .footer_section {
        margin-bottom: 0px;
  }

     .border-div {
     
        width: 100%;
 
        margin-top: 20px;
    }
    
    .new-theme-section-title {
        font-size: 45px;
        margin-bottom: 1rem;
    }
    
    .new-theme-program-list li {
        font-size: 14px;
        padding: 10px 0px;
    }
    
    .new-theme-artist-item {
        font-size: 14px;
        padding: 10px 0px;
    }
    
    /* Show read more button only on mobile */
    .new-theme-event-readmore {
        display: inline-block;
    }
  
      .new-theme-event-details {

                font-size: 20px;
        line-height: 1.4;
        letter-spacing: 2px;
  }
  
      .new-theme-event-title {

              margin-right: 40%;  
  }
  
  .new-theme-event-details {
        text-align: center !important;
  }

      .div-theme-tit {
            text-align: center!important;
  }

  .new-theme-events-section {
    padding-top: 60px!important;
  }
  
  .about-program-wrapper {
    margin-bottom: 2rem;
  }
  
  .about-program-toggle {
    font-size: 16px;
    padding: 10px 30px;
    width: 90%;
  }
  
  .about-program-text {
    font-size: 16px;
    padding: 20px 15px;
    text-align: right;
  }
}

/* Small Tablet Styles (576px - 767px) */
@media (max-width: 767px) and (min-width: 576px) {
    .new-theme-event-content {
              padding: 5px 30px 30px 30px;
    }
    
    .new-theme-event-title {
        font-size: 50px;
      letter-spacing: 2px;
    }
    
    .new-theme-event-details {
      color: #333!important;
        font-size: 36px;
    }
    
    .new-theme-section-title {
             font-size: 35px;
        letter-spacing: 2px;
    }
    
    .new-theme-program-list li,
    .new-theme-artist-item {
        font-size: 14px;
        padding: 8px 0px;
    }
    
      .new-theme-buy-button {
        padding: 10px 40px;
        font-size: 16px;
    }
  
    .new-theme-event-header {
        padding: 20px;
    }
    .new-theme-event-title {
        font-size: 42px;
      
  }
  
  .new-theme-event-details {
             font-size: 20px;
        line-height: 1.4;
        letter-spacing: 2px;
    }
  
  .new-theme-event-header {
        width: 100%;
  }
  
      
  .new-theme-info-item p {
  
    font-size: 16px;
  }
  
  .new-theme-info-item {
    gap: 7px;
  }

      .new-theme-content-grid {
        padding-top: 25px;
  }
  
  .open-search img {
    filter: invert(1);
}
}

/* Extra Small Mobile Styles (up to 575px) */
@media (max-width: 575px) {
    .new-theme-event-content {
           padding: 15px 30px 30px 30px;
    }
  
 

  .open-search img {
    filter: invert(1);
}
    
 
    .new-theme-info-item {
    gap: 7px;
  }
  
  .new-theme-info-item p {
  
    font-size: 16px;
  }

    .new-theme-event-title {
        font-size: 45px;
      letter-spacing: 2px;
        margin-bottom: 10px;
    }
    
    .new-theme-event-details {
          color: #333!important;
        font-size: 30px;
    }
    
    .new-theme-section-title {
               font-size: 30px;
        letter-spacing: 2px;
    }
    
    .new-theme-program-list li,
    .new-theme-artist-item {
        font-size: 14px;
        padding: 10px 0px;
    }
    
    .new-theme-event-header {
                 padding: 5px 15px 10px 15px;
    }
    
    .new-theme-banner-title {
        font-size: 45px;
    }
    
    .new-theme-buy-button {
                padding: 10px 40px;
        font-size: 16px;
    }
}

@media (max-width: 768px) {
  .new-theme-banner-title {
             font-size: 40px !important;
        letter-spacing: 2px;
            margin-bottom: 30px;
            line-height: 1.25;
margin-top: 10px;
  }
  
  
  .new-theme-event-header {  
      width: 100%;
  }

    .new-theme-banner {
        background-image: url('<?php echo wp_get_attachment_url($new_theme_banner_mobile_image); ?>') !important;
    }
}
</style>

<!-- Main Banner -->
<?php 
// Fallback banner image if not set
$banner_bg_image = '';
if ($new_theme_banner_desktop_image) {
    $banner_bg_image = $new_theme_banner_desktop_image;
} else {
    // Use default background gradient if no image
    $banner_bg_image = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
}

// Fallback title text
$banner_title_text = $new_theme_banner_title_text ? $new_theme_banner_title_text : get_the_title($post_id);
?>
<section class="new-theme-banner" style="background: <?php echo $new_theme_banner_color; ?>">
<?php if (is_array($banner_bg_image) && !empty($banner_bg_image['url'])): ?>
    <img src="<?php echo $banner_bg_image['url']; ?>" class="banner-image" />
<?php endif; ?>

    <div class="new-theme-banner-content">
        <h1 class="new-theme-banner-title" <?php echo $banner_title_style; ?>>
            <?php echo $banner_title_text; ?>
        </h1>
     
    </div>
</section>

<!-- Events Section -->
<section id="events" class="new-theme-events-section" style="padding: 4rem 0; background: #f8f9fa;">
    <div class="container">
        <?php 
        // Get about_pro content from current page
        $about_pro_content = get_field('about_pro');
        
        if (!empty($about_pro_content)): 
        ?>
        <div class="about-program-wrapper">
            <button class="about-program-toggle" data-target="about-program-main">
                <?php echo (ICL_LANGUAGE_CODE == 'en') ? 'About the Program' : 'על התכנית'; ?>
            </button>
            <div class="about-program-content" id="about-program-main">
                <div class="about-program-text">
                      <?php echo wpautop($about_pro_content); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($new_theme_events_repeater): ?>
            <?php foreach ($new_theme_events_repeater as $index => $event_item): ?>
                <?php
                
                $event_id = $event_item['event_id'];
                $event_image = $event_item['event_image'];
                $event_background_color = $event_item['event_background_color'];
                $event_text_color = $event_item['event_text_color'];
      $event_text_colorMAIN = $event_item['event_text_colorMAIN'];
                $event_hover_color = $event_item['event_hover_color'];
                
                // Fix event_id if it's an array (ACF Post Object sometimes returns array)
                if (is_array($event_id)) {
                    $event_id = $event_id[0]; // Get the first (and should be only) ID
                }
                
                // Fix event_id if it's a WP_Post object
                if (is_object($event_id) && isset($event_id->ID)) {
                    $event_id = $event_id->ID; // Get the ID from WP_Post object
                }
                
                
                if (!$event_id) {
                    continue;
                }
                
                // Get event data
                try {
                    $event_post = get_post($event_id);
                    
                    if (!$event_post) {
                        continue;
                    }
                    
                    // Check if ipo_event class exists
                    if (!class_exists('ipo_event')) {
                        continue;
                    }
                    
                    $event = new ipo_event($event_id);
                    $event_date_time = get_field('event_date_time', $event_id);
                } catch (Exception $e) {
                    continue;
                }
                
                if (!$event_date_time) {
                    continue;
                }
                
                $event_date = date('d/m', strtotime($event_date_time));
                $event_day_raw = $event->get_day();
                $event_time = $event->get_time();
                $event_city = $event->get_city();
                
                // Format day of week
               if(ICL_LANGUAGE_CODE == 'en') {
                    // English day names
                    $day_names = array(
                        'א' => 'Sunday',
                        'ב' => 'Monday', 
                        'ג' => 'Tuesday',
                        'ד' => 'Wednesday',
                        'ה' => 'Thursday',
                        'ו' => 'Friday',
                        'ש' => 'Saturday'
                    );
                    $event_day = $day_names[$event_day_raw] ?? $event_day_raw;
                } else {
                    // Hebrew day names
                    if ($event_day_raw == 'ש') {
                        $event_day = 'יום שבת';
                    } else {
                        $event_day = 'יום ' . $event_day_raw . "'";
                    }
                }
                // Check if event is passed
                $is_passed = $event->is_passed();
                
                // Get program data
                $related_program_id = get_field('related_to_program', $event_id);
                $program_plan = array();
                $program_artists = array();
                
                if ($related_program_id) {
                    // Check if related_program_id is an object or array and extract ID
                    if (is_object($related_program_id) && isset($related_program_id->ID)) {
                        $related_program_id = $related_program_id->ID;
                    } elseif (is_array($related_program_id) && isset($related_program_id[0])) {
                        $related_program_id = $related_program_id[0];
                    }
                    
                    // Get program post object
                    $program_post = get_post($related_program_id);
                    
                    if ($program_post) {
                        $program_plan = get_field('program_plan', $related_program_id);
                        $program_artists = get_field('program_artists', $related_program_id);
                    }
                }
                
                // Get event details
                $event_venue = get_field('event_venue', $event_id);
                $event_hall = get_field('event_hall', $event_id);
                $imported_hall = get_field('imported_hall', $event_id);
             $imported_location = '';

$terms = get_the_terms($event_id, 'location');
if (!empty($terms) && !is_wp_error($terms)) {
    $best = null;
    $bestDepth = -1;

    foreach ($terms as $t) {
        $depth = 0;
        $p = (int) $t->parent;

        while ($p) {
            $parent = get_term($p, 'location');
            if (!$parent || is_wp_error($parent)) {
                break;
            }
            $depth++;
            $p = (int) $parent->parent;
        }

        if ($depth > $bestDepth) {
            $bestDepth = $depth;
            $best = $t;
        }
    }

    if ($best) {
        $chain = [];
        $current = $best;

        while ($current && !is_wp_error($current)) {
            $chain[] = $current->name;

            $parentId = (int) $current->parent;
            if (!$parentId) {
                break;
            }

            $current = get_term($parentId, 'location');
        }

        $hier = array_reverse($chain);

        if (isset($hier[1]) && $hier[1] !== '') {
            $imported_location = $hier[1];
        } elseif (!empty($hier)) {
            $imported_location = end($hier);
        }
    }
}

                $event_length = get_field('event_length_concert', $event_id);
                $event_price_range = get_field('event_price_range', $event_id);
                $purchase_link = $event->get_purchase_link(true);
                
                // Get program length if event length is empty
                if (!$event_length && $related_program_id) {
                    $event_length = get_field('program_length_concert', $related_program_id);
                }
                ?>
                
                <div class="new-theme-event-item">
                    <!-- Event Header -->
                  <div style="position: relative;">
                 
                    <div class="new-theme-event-header" style="background-color: <?php echo $event_background_color; ?>;">
                    
                        
                        <div class="div-theme-tit">
                          <div class="tit-div">
                            <h2 class="new-theme-event-title" style="color: <?php echo $event_text_colorMAIN; ?>;">
                          <?php 
                            // Display custom title if exists, otherwise display program number
                            if (!empty($event_item['title_for_the_program'])) {
                                echo $event_item['title_for_the_program'];
                            } else {
                                echo $program_number_str . ' ' . ($index + 1);
                            }
                          ?>
                            </h2>
     <?php if ($event_image): ?>
                            <img class="new-theme-event-image" src="<?php echo $event_image['url']; ?>"/ >
                          </div>
                        <?php endif; ?>
                            <div class="new-theme-event-details hide-mobile-01" style="color: <?php echo $event_text_color; ?>;">
                              <?php echo $event_city; ?> <span class="border-div-d">|</span> <?php echo $event_date; ?> <span class="border-div-d">|</span> <?php echo $event_day; ?> <span class="border-div-d">|</span> <?php echo $event_time; ?>
                            </div>
                        </div>
                        
                      
                        <a href="#event-<?php echo $event_id; ?>" class="new-theme-event-readmore" 
                           style="color: <?php echo $event_text_color; ?>; border-color: <?php echo $event_text_color; ?>;"
                           data-hover-color="<?php echo $event_background_color ? $event_background_color : '#fff'; ?>">
                            <?php echo $read_more_str; ?>
                        </a>
                    </div>
                  </div>
                    <!-- Event Content -->
                    <div class="new-theme-event-content" id="event-<?php echo $event_id; ?>">
                      
                         <div class="new-theme-event-details hide-pc-01" style="color: <?php echo $event_background_color; ?>;">
                              <?php echo $event_city; ?> <span class="border-div-d">|</span> <?php echo $event_date; ?> <span class="border-div-d">|</span> <?php echo $event_day; ?> <span class="border-div-d">|</span> <?php echo $event_time; ?>
                            </div>
                        <div class="new-theme-content-grid">
                            <!-- Program -->
                            <?php if ($program_plan): ?>
                                <div class="new-theme-program-section">
                                    <h3 class="new-theme-section-title"><?php echo $the_program_str; ?></h3>
                                    <ul class="new-theme-program-list">
                                        <?php foreach ($program_plan as $program_item): ?>
                                            <?php if (!$program_item['break']): ?>
                                                <li>
                                                    <strong><?php echo $program_item['title']; ?></strong>
                                                    <span><?php echo $program_item['subtitle']; ?></span>
                                                </li>
                                            <?php else: ?>
                                                <li>
                                                  <div style=" display: flex; align-items: center;  justify-content: center;">
                                                    <img src="<?php echo esc_url( ipo_theme_uri( 'assets/images/Icon_mug-hot.svg' ) ); ?>" alt="" style="width: 20px; height: 20px; margin-left: 10px; vertical-align: middle;">
                                                    <?php echo $intermission_str; ?>
                                                  </div>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                          <div class="border-div"></div>
                            <!-- Artists -->
                            <?php if ($program_artists): ?>
                                <div class="new-theme-artists-section">
                                    <h3 class="new-theme-section-title"><?php echo $artists_str; ?></h3>
                                    <div class="new-theme-program-artists-mobile">
                                        <?php foreach ($program_artists as $artist_id): ?>
                                            <?php if (is_numeric($artist_id)): ?>
                                                <div class="new-theme-artist-item">
                                                    <a href="<?php echo get_permalink($artist_id); ?>" target="_blank" class="new-theme-artist-name"><?php echo get_the_title($artist_id); ?></a>
                                                    <?php
                                                    $artist_role = get_the_terms($artist_id, 'artist_role');
                                                    if ($artist_role && !is_wp_error($artist_role)) {
                                                        echo '<div class="new-theme-artist-role">' . $artist_role[0]->name . '</div>';
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Event Info -->
<div class="footer_section">
 <div class="width50">
                        <div class="new-theme-event-info">
                            <div class="new-theme-info-item location-div">
                                <div class="icon">
                                    <img src="<?php echo esc_url( ipo_theme_uri( 'assets/images/icons_location.svg' ) ); ?>" alt="">
                                </div>
                                <p>
                                    <span class="location"><?php 
                                        // Use imported_location if available, otherwise use city
                                        if ($imported_location) {
                                            echo $imported_location;
                                        } else {
                                            echo $event_city;
                                        }
                                    ?></span>
                                    <?php if ($imported_hall): ?>
                                        <span class="hall"><?php echo $imported_hall; ?></span>
                                    <?php elseif ($event_hall): ?>
                                        <span class="hall"><?php echo $event_hall; ?></span>
                                    <?php elseif ($event_venue): ?>
                                        <span class="hall"><?php echo $event_venue; ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <?php if ($event_length): ?>
                                <div class="new-theme-info-item hide-mobile-01 max-width-30d">
                                    <div class="icon">
                                        <img src="/wp-content/uploads/2022/08/icons_time-2x-3-e1661392161999.png" alt="">
                                    </div>
                                    <p><?php echo $event_length; ?></p>
                                </div>
                            <?php endif; ?>
                            
                                            
                            <?php if ($event_length): ?>
                                <div class="new-theme-info-item hide-pc-01">
                                    <div class="icon">
                                        <img src="/wp-content/uploads/2022/08/icons_time-2x-3-e1661392161999.png" alt="">
                                    </div>
                                    <p><?php echo $event_length; ?></p>
                                </div>
                            <?php endif; ?>
                          
                                   <?php if ($event_price_range): ?>
                                <div class="new-theme-info-item hide-pc-01">
                                    <div class="icon">
                                        <img src="/wp-content/uploads/2022/08/icons_price-2x-3-e1661392095708.png" alt="">
                                    </div>
                                    <p><?php echo $event_price_range; ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                            </div>
  <div class="border-div"></div>
                      <div class="width50">
                                <?php if ($event_price_range): ?>
                                <div class="new-theme-info-item hide-mobile-01">
                                    <div class="icon">
                                        <img src="/wp-content/uploads/2022/08/icons_price-2x-3-e1661392095708.png" alt="">
                                    </div>
                                    <p><?php echo $event_price_range; ?></p>
                                </div>
                            <?php endif; ?>
                            
                  
                        <!-- Buy Button -->
                        <?php if ($is_passed): ?>
                            <div class="new-theme-buy-button passed"><?php echo $event_passed_str; ?></div>
                        <?php else: ?>
                            <a href="<?php echo $purchase_link; ?>" class="new-theme-buy-button" 
                               style="color: <?php echo $event_background_color; ?>; border-color: <?php echo $event_background_color; ?>;"
                               data-hover-color="<?php echo $event_hover_color ? $event_background_color : '#fff'; ?>" target="_blank">
                                <span class="hide-mobile-01"><?php echo $buy_tickets_str; ?></span>
                                <span class="hide-pc-01"><?php echo $tickets_str; ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                          </div>
                </div>
                   </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // About Program Toggle functionality
    const aboutProgramToggles = document.querySelectorAll('.about-program-toggle');
    
    aboutProgramToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const content = document.getElementById(targetId);
            
            if (content) {
                if (content.classList.contains('show')) {
                    content.classList.remove('show');
                    this.textContent = <?php echo json_encode((ICL_LANGUAGE_CODE == 'en') ? 'About the Program' : 'על התכנית'); ?>;
                } else {
                    content.classList.add('show');
                    this.textContent = <?php echo json_encode((ICL_LANGUAGE_CODE == 'en') ? 'Close' : 'סגור'); ?>;
                }
            }
        });
    });
    
    // Mobile functionality for read more
    const readMoreButtons = document.querySelectorAll('.new-theme-event-readmore');
    
    readMoreButtons.forEach(function(button) {
        // Add hover effects for custom colors
        const hoverColor = button.getAttribute('data-hover-color') || '#fff';
        
        button.addEventListener('mouseenter', function() {
            this.style.background = this.style.color;
            this.style.color = hoverColor;
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.background = 'transparent';
            this.style.color = this.style.borderColor;
        });
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const contentGrid = targetElement.querySelector('.new-theme-content-grid');
                
                if (contentGrid) {
                    if (contentGrid.classList.contains('show')) {
                        contentGrid.classList.remove('show');
                        this.textContent = '<?php echo $read_more_str; ?>';
                    } else {
                        contentGrid.classList.add('show');
                        this.textContent = 'קרא פחות';
                    }
                }
            }
        });
    });
    
    // Add hover effects for buy buttons
 
});
</script>

<?php get_footer(); ?>