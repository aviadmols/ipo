<?php 

// Breadcrumbs shortcode
// Home > Parent Page > Current page
// Home > custom term > sub-term > current post (custom post type)
// Each level can be turned off with an internal flag

add_shortcode('ipo-breadcrumbs', 'shortcode_ipo_breadcrumbs');
function shortcode_ipo_breadcrumbs(){

    $home_flag = apply_filters('ipo_breadcrumbs_home_flag', true);
    $parent_flag = apply_filters('ipo_breadcrumbs_parent_flag', true);
    $term_flag = apply_filters('ipo_breadcrumbs_term_flag', true);
    $sub_term_flag = apply_filters('ipo_breadcrumbs_sub_term_flag', true);

    $home_str ='בית';
    if(defined('ICL_LANGUAGE_CODE')){
        if(ICL_LANGUAGE_CODE == 'en'){
            $home_str = 'Home';
        }
    }

    $home_item = '<a href="'.get_home_url().'">'.$home_str.'</a>';

    $sep = '<span class="sep">></span>';

    $html = '<div class="ipo-breadcrumbs-div"><div class="ipo-breadcrumbs-wrapper container">';

    // Is this a page
    if(is_page()){
        $html .= '<div class="ipo-breadcrumbs">';
        if($home_flag){
            $html .= $home_item;
        }
        if($parent_flag){
            $parent = get_post_ancestors(get_the_ID());
            if($parent){
                $parent = array_reverse($parent);
                foreach($parent as $p){
                    $html .= $sep.'<a href="'.get_permalink($p).'">'.get_the_title($p).'</a>';
                }
            }
        }
        $last_item = '<span class="last-item">'.get_the_title().'</span>';

        if($home_flag || $parent_flag){
            $html .= $sep.$last_item;
        } else {
            $html .= $last_item;
        }

        $html .= '</div>';
    }

    // Is this a cpt of type: artist | custom taxonomy of type: artist_cat
    if(is_singular('artist')){
        $html .= '<div class="ipo-breadcrumbs">';
        if($home_flag){
            $html .= $home_item;
        }
        if($term_flag){
            $terms = get_the_terms(get_the_ID(),'artist_cat');
            if($terms){
                $term = array_shift($terms);
                $html .= $sep.'<a href="'.get_term_link($term).'">'.$term->name.'</a>';
            }
        }
        if($sub_term_flag){
            // Get ancestors of the term
            $term_ancestors = get_ancestors($term->term_id,'artist_cat');
            if($term_ancestors){
                $term_ancestors = array_reverse($term_ancestors);
                foreach($term_ancestors as $t){
                    $term = get_term($t,'artist_cat');
                    $html .= $sep.'<a href="'.get_term_link($term).'">'.$term->name.'</a>';
                }
            }
        }
        $last_item = '<span class="last-item">'.get_the_title().'</span>';

        if($home_flag || $term_flag || $sub_term_flag){
            $html .= $sep.$last_item;
        } else {
            $html .= $last_item;
        }

        $html .= '</div>';
    }

    // Is this a cpt of type: artist_plan | custom taxonomy of type: gen_plan_cat
    if(is_singular('artist_plan')){
        $html .= '<div class="ipo-breadcrumbs">';
        if($home_flag){
            $html .= $home_item;
        }
        if($term_flag){
            $terms = get_the_terms(get_the_ID(),'gen_plan_cat');
            if($terms){
                $term = array_shift($terms);
                $html .= $sep.'<a href="'.get_term_link($term).'">'.$term->name.'</a>';
            }
        }
        if($sub_term_flag){
            // Get ancestors of the term
            $term_ancestors = get_ancestors($term->term_id,'gen_plan_cat');
            if($term_ancestors){
                $term_ancestors = array_reverse($term_ancestors);
                foreach($term_ancestors as $t){
                    $term = get_term($t,'gen_plan_cat');
                    $html .= $sep.'<a href="'.get_term_link($term).'">'.$term->name.'</a>';
                }
            }
        }
        $last_item = '<span class="last-item">'.get_the_title().'</span>';

        if($home_flag || $term_flag || $sub_term_flag){
            $html .= $sep.$last_item;
        } else {
            $html .= $last_item;
        }

        $html .= '</div>';
    }


    // Is this a cpt of type: program | custom taxonomy of type: event_type
    if(is_singular('program')){
        $html .= '<div class="ipo-breadcrumbs">';
        if($home_flag){
            $html .= $home_item;
        }
        if($term_flag){
            $terms = get_the_terms(get_the_ID(),'event_type');
            if($terms){
                $term = array_shift($terms);
                $html .= $sep.'<a href="'.get_term_link($term).'">'.$term->name.'</a>';
            }
        }
        if($sub_term_flag){
            // Get ancestors of the term
            $term_ancestors = get_ancestors($term->term_id,'event_type');
            if($term_ancestors){
                $term_ancestors = array_reverse($term_ancestors);
                foreach($term_ancestors as $t){
                    $term = get_term($t,'event_type');
                    $html .= $sep.'<a href="'.get_term_link($term).'">'.$term->name.'</a>';
                }
            }
        }
        $last_item = '<span class="last-item">'.get_the_title().'</span>';

        if($home_flag || $term_flag || $sub_term_flag){
            $html .= $sep.$last_item;
        } else {
            $html .= $last_item;
        }

        $html .= '</div>';
    }
    

    return $html.'</div></div>';
}

// Program grid shortcode
add_shortcode('ipo-programs','shortcode_ipo_programs');
function shortcode_ipo_programs($args){

    global $theme;

    // Retrieve shortcode args from the input
    $args = shortcode_atts([
        'ids' => [],
        'limit' => 4,
        'order' => 'DESC',
        // Order by custom acf date field  
        'orderby' => 'meta_value',
        'meta_key' => 'farthest_date',
        'e_class' => '',
    ],$args);

    // if ids is string, make it an array
    if(is_string($args['ids'])){
        $args['ids'] = explode(',',$args['ids']);
    }

    $posts = get_posts([
        'post_type' => 'program',
        'post__in' => $args['ids'],
        'posts_per_page' => $args['limit'],
        'order' => $args['order'],
        'orderby' => $args['orderby'],
        'meta_key' => $args['meta_key'],
        'post_status' => 'publish',
        'fields'    => 'ids',
    ]);

    $html = '<div class="ipo-programs-list '.$args['e_class'].'">';
    foreach($posts as $post){
        $html .= $theme->get_part('loop-program',$post);
    }
    $html .= '</div>';

    return '<div class="ipo-programs-shortcode-wrapper">'.$html.'</div>';

}




function at_popup_shortcode() {
    ob_start();
    ?>
<!-- AT Popup 2023 BEGIN -->
<link href="//cdn-media.web-view.net/popups/style/v1/main_combined-rtl.css?v=2.0.8611.28926" rel="stylesheet" type="text/css" />
<div id="_atPopupSU" class="shown"><div class="bl-template row-fluid bl-content-removable popup-dir-rtl" id="template-body" data-page-width="300" data-new-from-template="false">     <!-- BEGIN TEMPLATE OUTER -->     <div class="bl-template-main-wrapper span12" id="bl_0" valign="top">        <!-- BEGIN TEMPLATE MARGIN (Outside Margin - Padding) -->        <div class="bl-template-margin span12" id="bl_1" valign="top">            <!-- BEGIN TEMPLATE WRAPPER (Background) -->            <div class="template-main-table bl-template-background span12" id="bl_2" valign="top">                <!-- BEGIN TEMPLATE CONTAINER (Border, Inner Padding) -->                <div class="bl-template-border span12" id="bl_3" valign="top">                    <!-- BEGIN ZONES CONTAINER -->                    <!--zone-marked-->                    <div class="bl-zone bl-zone-dropable bl-zone-body row-fluid" id="bl_4" style="margin-top: 0px !important; background-color: transparent;" name="BodyZone" valign="top" height="">					<div class="bl-block bl-block-signuptextpage" id="bl_5" blocktype="signuptextpage" name="signuptextpage" style="width: 300px;"><div class="bl-block-content" contenteditable="false"> <div> <div class="bl-block-content-table bl-block-dir-ltr span12"> <div class="bl-block-content-row bl-block-content-first-row bl-block-content-last-row span12" style="" data-bi=""> <div class="bl-block-content-row-inner span12" style="padding: 12px 35px 20px 24px;"><div class="bl-block-content-column bl-block-content-new-column span12"><div class="bl-padding-columns bl-content-wrapper span12"> <div class="bl-signup-container span12 offset0" at-form-width="12" style="border: 0px solid #191919; border-radius: 5px; padding: 8px 14px; background-color: transparent;">  <div class="bl-block-content-item bl-block-content-item-signupfieldpage bl-content-item-unremovable fields-right" style="text-align: center; margin-bottom: 14px;" data-is-auto-fill="true"><input type="text" maxlength="50" class="signup-field span12 input-ltr first-input" readonly="readonly" data-field-type="text" data-field-source="FirstName" data-mandatory="true" placeholder="שם פרטי*" data-field-validation-msg="הערך שהוכנס בשדה זה אינו תקין" style="font-size: 18px; margin-bottom: 14px; height: 38px; line-height: 18px; font-family: "Open Sans Hebrew"; text-align: right;" data-hidden="false" data-custom-values="" data-input-type="text" data-tag="First Name"><input type="text" maxlength="50" class="signup-field span12 input-ltr" readonly="readonly" data-field-type="text" data-field-source="LastName" data-mandatory="true" placeholder="שם משפחה*" data-field-validation-msg="הערך שהוכנס בשדה זה אינו תקין" style="font-size: 18px; margin-bottom: 14px; height: 38px; line-height: 18px; font-family: "Open Sans Hebrew"; text-align: right;" data-hidden="false" data-custom-values="" data-input-type="text" data-tag="Last Name"><input type="text" maxlength="50" class="signup-field span12 input-ltr" readonly="readonly" data-field-type="email" data-field-source="Email" data-mandatory="true" placeholder="אימייל " data-field-validation-msg="הערך שהוכנס בשדה זה אינו תקין" style="font-size: 18px; margin-bottom: 14px; height: 38px; line-height: 18px; font-family: "Open Sans Hebrew"; text-align: right;" data-hidden="false" data-custom-values="" data-input-type="text" data-tag="undefined"><input type="text" maxlength="50" class="signup-field span12 input-ltr" readonly="readonly" data-field-type="mobile" data-field-source="SMS" data-mandatory="true" placeholder="נייד*" data-field-validation-msg="המספר שגוי או חסר, נא לנסות שנית" style="font-size: 18px; margin-bottom: 14px; height: 38px; line-height: 18px; font-family: "Open Sans Hebrew"; text-align: right;" data-hidden="false" data-custom-values="" data-input-type="text" data-tag="SMS"><input type="text" maxlength="50" class="signup-field span12 input-ltr" readonly="readonly" data-field-type="date" data-field-source="Birthday" data-mandatory="true" placeholder="תאריך לידה" data-field-validation-msg="הערך שהוכנס בשדה זה אינו תקין" style="font-size: 18px; margin-bottom: 14px; height: 38px; line-height: 18px; font-family: "Open Sans Hebrew"; text-align: right;" data-hidden="false" data-custom-values="" data-input-type="text" data-tag="Birthday"><div class="confirm-emails" data-field-validation-msg="נא אשר כדי לקבל דיוור" style="font-family: "Open Sans Hebrew";"> <div class="checkbox rtl"> <label style="cursor: auto;"> <input type="checkbox" disabled="disabled" style="text-align: right;"><label class="confirm-label dir-label" style="font-family: "Open Sans Hebrew"; text-align: right; cursor: auto; font-size: 18px; color: #000000;">אני מאשר לקבל חומר שיווקי ופרסומות מהפילהרמונית הישראלית</label></label></div> </div><div class="confirm-terms hidden" data-field-validation-msg="יש לאשר את תנאי השימוש" style="font-family: "Open Sans Hebrew";"> <div class="checkbox rtl"> <label style="cursor: auto;"> <input type="checkbox" disabled="disabled" style="text-align: right;"><label class="confirm-label dir-label" style="font-family: "Open Sans Hebrew"; text-align: right; cursor: auto; font-size: 18px; color: #000000;">קראתי ואישרתי את תנאי השימוש</label></label></div> </div></div> <div class="bl-padding-columns bl-content-wrapper-columns" style="text-align: center;"> <div class="bl-block-button-content-wrapper" style="display: block; border-radius: 5px; background-color: #0d0d6d;"> <div class="bl-block-button-content-item-wrapper" style="font-size: 16px; padding: 9px;"> <div class="bl-block-content-item bl-block-content-item-button bl-content-item-unremovable" style="min-width: 1px; min-height: 16px; display: block; text-align: center; text-decoration: none;" data-gramm="false"><span style="font-family:'Open Sans Hebrew';"><span style="font-size:22px;"><span style="color:#FFFFFF;"><strong>הירשמו עכשיו</strong></span></span></span></div> </div> </div> </div> </div> </div></div></div> </div> </div> </div> </div></div>                     </div>                    <!-- END ZONES CONTAINER -->                </div>                <!-- END TEMPLATE CONTAINER -->            </div>            <!-- END TEMPLATE WRAPPER -->        </div>        <!-- END TEMPLATE MARGIN -->    </div>    <!-- END TEMPLATE OUTER --></div></div>

<script type='text/javascript'>
	(function () {
		var _atpopq = window._atpopq || (window._atpopq = []);
		window._atpopobj = {};
		if (!_atpopq.loaded) {
			var v = Math.floor((Math.random() * 1000000) + 1);
			var atpopjs = document.createElement('script');
			atpopjs.type = 'text/javascript';
			atpopjs.async = true;
			atpopjs.src = '//cdn-media.web-view.net/popups/lib/v1/loader.min.js?v=' + v;
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(atpopjs, s);
			_atpopq.loaded = true;
		}
		_atpopq.push(['UserId', 'zw3jefdca8w2']);
		_atpopq.push(['PopupId', 'x3fafsa2u2']);
		_atpopq.push(['IsraelCode', '104']);
		_atpopq.push(['CountryCode', '104']);
		_atpopq.push(['IsEmbed', true]);
		_atpopq.push(['IgnoreMainCss', true]);
		_atpopq.push(['OnEventCallback', 'handleATPopupEvent']);
	})();
</script>
<script type="text/javascript">
	//Sample event handler function
	function handleATPopupEvent(ev,args){
		switch(ev){
			case 'display':
				//Do this when the popup is displayed
				break;
			case 'close':
				//Do this when the popup gets closed by the user
				break;
			case 'submit':
				//Do this when popup gets submitted and the user doesn't get redirected to a URL
				break;
		}
	}
</script>
<style>
.bl-block-content * {
        font-family: 'Simpler'!important;
    font-weight: 300;
    font-size: 18px;
}

.bl-block-content input {
    border-radius: 0px!important;
}

  .page_title {
    display: none!important;
  }
  
  .bl-block-button-content-wrapper{    border-radius: 50px!important;
  }
  .bl-block-content-column input {
      color: #000!important;
      border-bottom: 2px solid #000!important;
      box-shadow: none!important;
      border-top: none!important;
      border-right: none!important;
      border-left: none!important;
  }
  
  .bl-block-content-row-inner{
    padding: 12px 0px 20px 0px!important;
  }
  
  .bl-signup-container, .bl-padding-columns, .bl-block-signuptextpage{
   padding: 0px!important; 
  }
  
  
  .bl-block-signuptextpage {
      min-width: 100%!important;
  
  }
  
  #_atPopupSU.shown  {
max-width: 400px!important;
    margin-right: auto!important;
    margin-bottom: 25px!important;
    margin-left: auto!important;
}

#template-body {
    text-align: center!important;
}

#_atPopupSU .bl-template .bl-block-button-content-wrapper {
    background: #000!important;
}
</style>
<!-- AT Popup END -->
    <?php
    return ob_get_clean();
}
add_shortcode( 'subscribe_contact', 'at_popup_shortcode' );




function custom_contact_form_shortcode() {
      $locale = get_locale();
    $is_english = (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en');
  
    ob_start(); // Begin output buffering

    ?>
<style>
  
@media (max-width: 768px) {
  .form_season {
    padding: 15px!important;
    width: 100%!important;
  }

  .text_cta {
    width: 100%!important;
  }

  .form_season h3 {
    text-align: center!important;
    color: rgba(0, 0, 0, 1);
    font-family: 'Mandatory PH';
    font-weight: 400;
  }

  .form_season .sub-title-simpler {
    font-family: 'Simpler';
  }

  .contact_cta {
    width: 100%;
    margin-top: 15px;
  }
}
  
 [lang="en-US"]   .text_cta {
        max-width: 400px;
  }
 [lang="en-US"] .section.description .content h3 {
        text-align: left !important;
  }

.form_season .gform_confirmation_message {
  text-align: center!important;
}

@media (min-width: 768px) {
  .form_season .gravity-theme {
    margin-top: 0px!important;
  }

  .form_season .sub-title-simpler {
    font-family: 'Simpler';
    font-size: 22px!important;
    margin-top: -10px!important;
    padding-right: 5px!important;
  }

  .form_season h3 {
    text-align: right!important;
  }

  .form_season .gform_fields > div {
    width: calc(50% - 35px);
  }

  .form_season .gform_footer {
    width: 25%;
  }

  .form_season .gform_body {
    width: 75%!important;
  }

  #gform_submit_button_9,
  #gform_submit_button_10 {
    max-width: 90%;
    margin-right: auto;
  }

  .contact_cta {
    width: calc(100% - 420px);
  }

  #gform_fields_9,
  #gform_fields_10 {
    display: flex;
  }

  .form_season form {
    display: flex;
    align-items: center;
  }

  .form_season {
    padding: 25px;
    margin-top: 25px;
    justify-content: space-between;
    align-items: center;
  }
}

.form_season {
  padding: 25px;
  margin-top: 25px;
  background-color: #eee;
  display: flex;
  flex-flow: wrap;
}

.form_season .gravity-theme {
  max-width: 100%;
  margin-top: 0px;
  display: block;
  margin-left: auto;
  margin-right: auto;
}

.form_season input.gform_button {
  background: #2e2e81!important;
  color: rgb(249 195 197)!important;
  border: #2e2e81!important;
  width: 100%!important;
}

.form_season label {
  display: none!important;
}

.form_season input:not(.gform_button) {
  background: transparent!important;
}

.form_season input::placeholder {
  font-size: 18px;
  color: #000;
}

  </style>
   <div class="form_season">
      <div class="text_cta">
        <?php if ($is_english): ?>
          <h3 style="display: block; width: 100%; font-family: 'Mandatory PH'!important; font-size: 7rem; letter-spacing: 0.7rem; font-weight: 300 !important; margin-bottom: 0px!important; padding-bottom: 0px!important;">Want to know more?</h3>
          <h3 class="sub-title-simpler" style="font-weight: normal!important; margin-bottom: 0px!important; display: block; width: 100%;">Leave your details and our team will contact you</h3>
          <h3 class="sub-title-simpler" style="font-weight: bold!important; margin-top: 4px!important; margin-bottom: 0px!important; display: block; width: 100%;">Or call us at <a href="tel:3766*" style="">3766*</a></h3>
        <?php else: ?>
          <h3 style="display: block; width: 100%; font-family: 'Mandatory PH'!important; font-size: 7rem; letter-spacing: 0.7rem; font-weight: 300 !important; margin-bottom: 0px!important; padding-bottom: 0px!important;">רוצים לשמוע עוד?</h3>
          <h3 class="sub-title-simpler" style="font-weight: normal!important; margin-bottom: 0px!important; display: block; width: 100%;">השאירו פרטים ונציג יחזור אליכם</h3>
          <h3 class="sub-title-simpler" style="font-weight: bold!important; margin-top: 4px!important; margin-bottom: 0px!important; display: block; width: 100%;">או התקשרו <a href="tel:3766*" style="">3766*</a></h3>
        <?php endif; ?>
      </div>
      <div class="contact_cta">
        <?php echo do_shortcode($is_english ? '[gravityform id="10" title="false"]' : '[gravityform id="9" title="false"]'); ?>
      </div>
    </div>
    <?php

    return ob_get_clean(); // End output buffering and return the output
}
add_shortcode('custom_contact_form', 'custom_contact_form_shortcode');