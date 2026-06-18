<?php 

// Load parent functions

global $theme;


 //  add_filter( 'https_ssl_verify', '__return_false' );

require_once(get_template_directory().'/functions.php'); 






    require_once get_stylesheet_directory() . '/includes/class-ipo-calendar.php';
   require_once get_stylesheet_directory() . '/includes/class-ipo-event.php';
    require_once get_stylesheet_directory() . '/includes/class-ipo-program.php';
    require_once get_stylesheet_directory() . '/includes/custom-functions.php';

  // require_once get_stylesheet_directory() . '/includes/events-api-class.php';
    require_once get_stylesheet_directory() . '/includes/taxonomy-radio-buttons.php';

  //  require_once get_stylesheet_directory() . '/includes/create-event.php';
  //  require_once get_stylesheet_directory() . '/includes/program-create-events.php';
   // require_once get_stylesheet_directory() . '/includes/ipo-create-events-custom-functions.php';

    require_once get_stylesheet_directory() . '/includes/wp_insert_attachment_from_url.php';

    require_once get_stylesheet_directory() . '/includes/event-permalink.php';
    require_once get_stylesheet_directory() . '/includes/ipo-shortcodes.php';
    require_once get_stylesheet_directory() . '/includes/ipo-bidirectional.php';
    require_once get_stylesheet_directory() . '/includes/serie-category-programs.php';
 //  require_once get_stylesheet_directory() . '/includes/acf-event-field.php';

    // AJAX modules that might rely on ACF
    require_once get_stylesheet_directory() . '/includes/ajax/ajax_get_events/ajax_get_events.php';
    require_once get_stylesheet_directory() . '/includes/ajax/ajax_get_calendar_events/ajax_get_calendar_events.php';
    require_once get_stylesheet_directory() . '/includes/ajax/ajax_get_month/ajax_get_month.php';
    require_once get_stylesheet_directory() . '/includes/ajax/ajax_import_batch/ajax_import_batch.php';
    require_once get_stylesheet_directory() . '/includes/ajax/ajax_process_posts/ajax_process_posts.php';




add_theme_support( 'title-tag' );

/**
 * Theme assets base URL (child theme). Use for images/icons in includes/images.
 */
if ( ! function_exists( 'ipo_theme_uri' ) ) {
	function ipo_theme_uri( $path = '' ) {
		$base = get_stylesheet_directory_uri();
		return $path ? $base . '/' . ltrim( $path, '/' ) : $base;
	}
}

/**
 * Left-arrow icon URL in uploads (portable, no hardcoded domain).
 */
if ( ! function_exists( 'ipo_arrow_icon_url' ) ) {
	function ipo_arrow_icon_url() {
		return esc_url( content_url( 'uploads/2022/06/left-arrow.png' ) );
	}
}

// Base – must run at load time so parent theme includes these scripts in output.
// (Registering on wp_enqueue_scripts caused Slick / isScrolledIntoView to be missing.)
if (isset($theme) && is_object($theme)) {
	$theme->add_script('scrollspy');
	$theme->add_script('wow');
	$theme->add_script('anim-base');
	$theme->add_script('anim-reveal');

	// Theme Scripts
	$theme->add_script('isotope.pkgd.min');
	$theme->add_script('plugins');
	$theme->add_script('main');
	$theme->add_script('scripts-admin',array('admin'=>true));
	$theme->add_script('splide.min');
	$theme->add_script('sliders-splide');

	// Calendar scripts: load everywhere so .calendar_area works on any page (not only front page).
	$theme->add_script('calendar-behavior');
	$theme->add_script('calendar-filter');
	// Loader (full calendar) only on front page or calendar templates
	if ( is_front_page() || is_page_template( 'template-calendar-full.php' ) || is_page_template( 'template-calendar-full-ajax.php' ) ) {
		$theme->add_script('loader');
	}
	$theme->add_script('slick.min');
	$theme->add_script('hc-sticky');
	$theme->add_script('scripts-custom');

	//$theme->add_script('fancybox.umd');
	$theme->add_parent_script('fancybox.umd');

	// Theme Styles
	$theme->add_style('bootstrap.min');
	$theme->add_style('owl.carousel.min');
	$theme->add_style('magnific-popup');
	$theme->add_style('style');
	$theme->add_style('responsive');
	$theme->add_style('style-admin',array('admin'=>true));
	$theme->add_style('style-sagi');
	$theme->add_style('slick');
	$theme->add_style('style-shojib');
	$theme->add_style('splide-core.min');
	$theme->add_style('splide.min');
	$theme->add_style('animate');
	//$theme->add_style('fancybox');
	$theme->add_parent_style('fancybox');

	$theme->add_nav_menu('locations-menu','Locations Menu');
	$theme->add_nav_menu('mobile-menu','Mobile Menu');
	$theme->add_nav_menu('mobile-cta','Mobile CTA');

	// Fonts
	$theme->allow_svg();
	$theme->allow_json();
} else {
	error_log('Expected $theme to be an object, but it is not set or not an object.');
}


// register new image sizes
add_action( 'init', 'ipo_image_sizes' );
function ipo_image_sizes(){
	add_image_size( 'blog_featured_image', 400, 400, false); 
	add_image_size( 'loop-artist-team', 195, 245, false);
	add_image_size( 'loop-artist-team-large', 290, 370, false);
}

// Register taxonomy category_program for post type program
add_action('init', 'register_category_program_taxonomy');

function register_category_program_taxonomy() {
	$labels = array(
		'name'              => 'קטגוריות תוכניות',
		'singular_name'     => 'קטגוריית תוכנית',
		'search_items'      => 'חפש קטגוריות',
		'all_items'         => 'כל הקטגוריות',
		'parent_item'       => 'קטגוריה אב',
		'parent_item_colon' => 'קטגוריה אב:',
		'edit_item'         => 'ערוך קטגוריה',
		'update_item'       => 'עדכן קטגוריה',
		'add_new_item'      => 'הוספת קטגוריה חדשה',
		'new_item_name'     => 'שם קטגוריה חדשה',
		'menu_name'         => 'קטגוריות תוכניות',
	);

	$args = array(
		'hierarchical'        => true,
		'labels'              => $labels,
		'show_ui'             => true,
		'show_admin_column'   => true,
		'show_in_quick_edit'  => true,
		'query_var'           => true,
		'rewrite'             => array('slug' => 'category_program'),
	);

	register_taxonomy('category_program', array('program'), $args);
}



function is_sagi(){
	// Get current user id
	$user_id = get_current_user_id();
	// if user is not logged in, return false
	if(!$user_id){
		return false;
	}

	// get user email
	$user_email = get_userdata($user_id)->user_email;
	// check if email is example@gmail.com
	if($user_email == 'sagi@8scope.com'){
		return true;
	} else {
		return false;
	}
}


function custom_admin_css() {
    echo '<style>
    .mce-widget  {
      right: 0px!important;
      overflow: hidden;
      top: 0px!important;
    }
    </style>';
}
add_action('admin_head', 'custom_admin_css');

/**
 * Fix WP 6.9+ "unregistered dependency" notice from GTM Kit.
 *
 * Some setups enqueue `gtmkit-datalayer` with a dependency on `gtmkit-container`
 * even when `gtmkit-container` was not registered yet. Register a "no-src" handle
 * early so WP doesn't trigger the notice. Using `src=false` ensures no <script>
 * tag is printed for this handle (same pattern WP uses for `jquery`).
 */
function ipo_register_gtmkit_container_handle() {
	if ( wp_script_is( 'gtmkit-container', 'registered' ) ) {
		return;
	}
	wp_register_script( 'gtmkit-container', false, array(), null, true );
}
add_action( 'wp_enqueue_scripts', 'ipo_register_gtmkit_container_handle', 0 );
add_action( 'admin_enqueue_scripts', 'ipo_register_gtmkit_container_handle', 0 );

function my_acf_wysiwyg_toolbar( $field ) {
    ?>
    <script type="text/javascript">
    (function($) {
        acf.add_filter('wysiwyg_tinymce_settings', function( mceInit, id ) {
            // בדיקה אם הכפתורים כבר נוספו
            if (mceInit.toolbar1.indexOf('forecolor') === -1) {
                var toolbar = mceInit.toolbar1 + ',forecolor backcolor';
                mceInit.toolbar1 = toolbar;
            }
            return mceInit;
        });
    })(jQuery);
    </script>
    <?php
}
add_action('acf/render_field/type=wysiwyg', 'my_acf_wysiwyg_toolbar');




add_action('add_meta_boxes', 'add_event_update_button_meta_box');
function add_event_update_button_meta_box() {
    add_meta_box(
        'event_update_button',
        __('עדכון אירוע', 'text-domain'),
        'render_event_update_button_meta_box',
        'event',
        'side',
        'high'
    );
}

function render_event_update_button_meta_box($post) {
    echo '<button id="event_update_btnn" class="  button-large">' . __('עדכן אירוע', 'text-domain') . '</button>';
    // Nonce field for security
    //  wp_nonce_field('event_update_action', 'event_update_nonce');
}

add_action('wp_ajax_fetch_presentation_data', 'handle_fetch_presentation_data');
add_action('wp_ajax_nopriv_fetch_presentation_data', 'handle_fetch_presentation_data');

function handle_fetch_presentation_data() {
    if (isset($_POST['apiEventId']) && !empty($_POST['apiEventId'])) {
        $apiEventId = sanitize_text_field($_POST['apiEventId']);
$response = wp_remote_get("https://ipo.pres.global/api/presentations/{$apiEventId}", array('sslverify' => false));

        if (is_wp_error($response)) {
               $error_message = $response->get_error_message();
    wp_send_json_error('Failed to fetch data from API. Error: ' . $error_message);
        } else {
            $body = wp_remote_retrieve_body($response);
            wp_send_json_success(json_decode($body, true));
        }
    } else {
     wp_send_json_success(json_decode($body, true));
    }

    wp_die();
}



add_action('admin_footer', 'mr_admin_footer');
function mr_admin_footer()
{
	global $pagenow;
	if ( $pagenow == 'post.php' ) {

		?>
		<script type="text/javascript">
		jQuery('input[name="icl_minor_edit"]').attr('checked','checked');
		</script>
		<?php
	}
	
}


// Defer on scripts was removed: it caused "slick is not a function", isScrolledIntoView
// undefined, and .play() errors because theme scripts loaded after inline code.

/**
 * On pages without the hero, add placeholder elements for #play_lottie and #speaker_lottie
 * so parent theme scripts that call .play() / .stop() on them do not throw.
 * Run early in footer so we run before other footer scripts.
 */
function ipo_lottie_safe_placeholders() {
	if ( is_admin() ) {
		return;
	}
	?>
<script>
(function(){
	if (document.getElementById('play_lottie')) return;
	var play = document.createElement('div');
	play.id = 'play_lottie';
	play.style.cssText = 'position:absolute;left:-9999px;width:0;height:0;overflow:hidden;pointer-events:none;';
	play.play = function(){};
	play.stop = function(){};
	document.body.appendChild(play);
	var speaker = document.createElement('div');
	speaker.id = 'speaker_lottie';
	speaker.style.cssText = 'position:absolute;left:-9999px;width:0;height:0;overflow:hidden;pointer-events:none;';
	speaker.play = function(){};
	speaker.stop = function(){};
	document.body.appendChild(speaker);
})();
</script>
	<?php
}
add_action( 'wp_footer', 'ipo_lottie_safe_placeholders', 1 );

/**
 * Define soundplay() and pauseVid() after jQuery is ready, and run the "reveal" logic
 * that the parent theme's script would run (add .show to .home, play Lotties).
 * When the parent script runs before jQuery and throws, this ensures the menu and layout still show.
 */
function ipo_hero_video_lottie_controls() {
	if ( is_admin() ) {
		return;
	}
	?>
<script>
(function() {
	if (typeof jQuery === 'undefined') return;
	jQuery(function() {
		window.soundplay = function() {
			var vid = document.getElementById("main-video");
			var el = document.getElementById("speaker_lottie");
			if (!vid) return;
			if (el && el.play) {
				if (vid.muted) { el.play(); vid.muted = false; } else { el.stop(); vid.muted = true; }
			} else {
				vid.muted = !vid.muted;
			}
		};
		window.pauseVid = function() {
			var vid = document.getElementById("main-video");
			var el = document.getElementById("play_lottie");
			if (!vid) return;
			if (el && el.play) {
				if (vid.paused) { el.play(); vid.play(); } else { el.stop(); vid.pause(); }
			} else {
				if (vid.paused) vid.play(); else vid.pause();
			}
		};

		// Fallback: parent theme script (data URI) often runs before jQuery and fails.
		// Replicate its "reveal" behaviour so header/menu and Lotties show for guests too.
		setTimeout(function() {
			var home = document.querySelector('.home');
			if (home && !home.classList.contains('show')) {
				home.classList.add('show');
			}
			var logoLottie = document.querySelector('.logo-lottie');
			if (logoLottie && typeof logoLottie.play === 'function') { logoLottie.play(); }
			var introLottie = document.getElementById('intor_lottie');
			if (introLottie && typeof introLottie.play === 'function') { introLottie.play(); }
			var speaker = document.getElementById('speaker_lottie');
			if (speaker && typeof speaker.play === 'function') { speaker.play(); }
			var playLottie = document.getElementById('play_lottie');
			if (playLottie && typeof playLottie.play === 'function') { playLottie.play(); }
		}, 1200);
	});
})();
</script>
	<?php
}
add_action( 'wp_footer', 'ipo_hero_video_lottie_controls', 25 );

if (!function_exists('preload_batch_size')) {
    function preload_batch_size( $value ) {
        $value = 30;
        return $value;
    }
}
add_filter( 'rocket_preload_cache_pending_jobs_cron_rows_count', 'preload_batch_size' );

if (!function_exists('preload_cron_interval')) {
    function preload_cron_interval( $interval ) {
        $interval = 120;
        return $interval;
    }
}
add_filter( 'rocket_preload_pending_jobs_cron_interval', 'preload_cron_interval' );

if (!function_exists('preload_requests_delay')) {
    function preload_requests_delay( $delay_between ) {
        $seconds = 0.9;
        $delay_between = $seconds * 1000000;
        return $delay_between;
    }
}
add_filter( 'rocket_preload_delay_between_requests', 'preload_requests_delay' );

if (!function_exists('lzwprlFixBG')) {
    function lzwprlFixBG($Prop){
        $Prop = str_replace("href","data-lzhref",$Prop);
        $Prop = str_replace("stylesheet","lazystyle",$Prop);
        return $Prop;
    }
}

if (!function_exists('replace_font_icons_links_with_data_lzhref')) {
    function replace_font_icons_links_with_data_lzhref( $buffer ) {
        $re = '/<link(?![^>]*\b(prefetch|preload)\b)[^>]+href=["\']([^"\']*(cdnjs|jquery\.com|flaticon)[^"\']*)["\'][^>]*>/i';
        preg_match_all($re, $buffer, $matches, PREG_SET_ORDER, 0);
        $i = 0;
        foreach ($matches as $Match){
            $FixBG = lzwprlFixBG($Match[0]);
            $buffer = str_replace($Match[0],$FixBG,$buffer);
        }
        $buffer = str_replace('</body>', '<script>navigator.userAgent.match(/x86|ptst/i)||document.querySelectorAll("link[data-lzhref]").forEach(e=>{let t=e.dataset.lzhref;t&&(e.href=t,e.rel="stylesheet",e.removeAttribute("data-lzhref"))});</script></body>', $buffer);
        return $buffer;
    }
}

if (!function_exists('replace_font_icons_links_with_data_lzhref_redirect')) {
    function replace_font_icons_links_with_data_lzhref_redirect() {
        // Only run output buffering when explicitly enabled (e.g. pages that use cdnjs/flaticon).
        // Add add_filter( 'ipo_use_lazy_font_icons', '__return_true' ) to enable.
        if ( apply_filters( 'ipo_use_lazy_font_icons', false ) ) {
            ob_start( 'replace_font_icons_links_with_data_lzhref' );
        }
    }
}

add_action( 'template_redirect', 'replace_font_icons_links_with_data_lzhref_redirect' );


if ( ! function_exists( 'ipo_event_has_program' ) ) {
	function ipo_event_has_program( $event_id ) {
		$program = get_field( 'related_to_program', $event_id );
		if ( empty( $program ) ) {
			return false;
		}
		$program_id = is_array( $program ) ? $program[0] : $program;
		return $program_id && get_post_type( $program_id ) === 'program';
	}
}

if ( ! function_exists( 'ipo_filter_events_with_program' ) ) {
	function ipo_filter_events_with_program( array $event_ids ) {
		return array_values( array_filter( $event_ids, 'ipo_event_has_program' ) );
	}
}

function get_related_event_ids($post_id) {

    $args = array(
        'post_type' => 'event',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'related_to_program', // שם השדה ACF
                'value' => $post_id,
                'compare' => '='
            )
        ),
        'fields' => 'ids' // מחזיר רק את ה-IDs של הפוסטים
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        return $query->posts; // מחזיר את ה-IDs של הפוסטים
    } else {
        return array(); // מחזיר מערך ריק אם אין פוסטים מתאימים
    }
}



// Register the admin menu
add_action('admin_menu', 'register_event_table_page');
function register_event_table_page() {
    add_menu_page(
        'Event Table', // Page title
        'Event Table', // Menu title
        'manage_options', // Capability
        'event-table', // Menu slug
        'display_event_table_page', // Callback function
        'dashicons-calendar-alt', // Icon
        6 // Position
    );
}

// Display the event table page
function display_event_table_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $related_programs = get_posts(array(
        'post_type' => 'program',
        'numberposts' => 1000
    ));

    $events = get_posts(array(
        'post_type' => 'event',
        'numberposts' => 1000
    ));

    // Include jQuery UI CSS
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    // Include jQuery and jQuery UI
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-autocomplete');

    // Localize script for autocomplete data
    $programs = array();
    foreach ($related_programs as $program) {
        $programs[] = array(
            'label' => $program->post_title,
            'value' => $program->ID
        );
    }
    wp_localize_script('jquery-ui-autocomplete', 'programList', $programs);

    $eventList = array();
    foreach ($events as $event) {
        $eventList[] = array(
            'label' => $event->post_title,
            'value' => $event->ID
        );
    }
    wp_localize_script('jquery-ui-autocomplete', 'eventList', $eventList);

    ?>
    <div class="wrap">
        <h1>Event Table</h1>
      <div class="ipo_events ipo-api-import-box">
    <label for="ipo_event_search"><strong>חיפוש אירועים מה־API:</strong></label>

    <input
        type="text"
        id="ipo_event_search"
        placeholder="הקלד שם אירוע, מזהה או תאריך..."
        autocomplete="off"
    >

    <div class="ipo-api-events-toolbar">
        <span id="ipo_events_loaded_count">טוען אירועים...</span>
        <span id="ipo_events_selected_count">נבחרו 0 אירועים</span>
    </div>

    <div class="ipo-api-events-actions">
        <button type="button" class="button" id="ipo_select_visible_events">בחר את המוצגים</button>
        <button type="button" class="button" id="ipo_clear_selected_events">נקה בחירה</button>
    </div>

    <div id="ipo_api_events_list" class="ipo-api-events-list"></div>

    <button type="button" id="event_update_btn" class="button button-primary">
        הוספת אירועים נבחרים
    </button>

    <div id="ipo_import_progress" class="ipo-import-progress" style="display:none;">
        <div class="ipo-import-progress-header">
            <strong id="ipo_import_progress_title">מתחיל ייבוא...</strong>
            <span id="ipo_import_progress_counter">0 / 0</span>
        </div>

        <div class="ipo-import-progress-bar">
            <span id="ipo_import_progress_fill"></span>
        </div>

        <ul id="ipo_import_results"></ul>
    </div>
</div>

<style>
    .ipo-api-import-box {
        max-width: 900px;
        background: #fff;
        border: 1px solid #dcdcde;
        padding: 20px;
        margin: 20px 0 30px;
        border-radius: 8px;
    }

    #ipo_event_search {
        width: 100%;
        height: 42px;
        padding: 0 14px;
        margin: 12px 0;
        font-size: 15px;
    }

    .ipo-api-events-toolbar,
    .ipo-api-events-actions,
    .ipo-import-progress-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .ipo-api-events-toolbar {
        margin-bottom: 12px;
        color: #50575e;
    }

    .ipo-api-events-actions {
        justify-content: flex-start;
        margin-bottom: 12px;
    }

    .ipo-api-events-list {
        max-height: 420px;
        overflow-y: auto;
        border: 1px solid #dcdcde;
        background: #fff;
        border-radius: 6px;
        margin-bottom: 16px;
    }

    .ipo-api-event-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid #f0f0f1;
        cursor: pointer;
    }

    .ipo-api-event-item:last-child {
        border-bottom: 0;
    }

    .ipo-api-event-item:hover {
        background: #f6f7f7;
    }

    .ipo-api-event-item input {
        margin-top: 2px;
    }

    .ipo-api-event-item-text {
        line-height: 1.5;
    }

    #event_update_btn {
        min-width: 180px;
        height: 42px;
    }

    .ipo-import-progress {
        margin-top: 20px;
        border-top: 1px solid #dcdcde;
        padding-top: 18px;
    }

    .ipo-import-progress-bar {
        width: 100%;
        height: 12px;
        background: #e5e5e5;
        border-radius: 999px;
        overflow: hidden;
        margin: 12px 0 16px;
    }

    #ipo_import_progress_fill {
        display: block;
        width: 0;
        height: 100%;
        background: #2271b1;
        transition: width .25s ease;
    }

    #ipo_import_results {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    #ipo_import_results li {
        padding: 9px 12px;
        margin-bottom: 8px;
        border-radius: 6px;
        background: #f6f7f7;
    }

    #ipo_import_results li.is-loading {
        background: #fff8e5;
    }

    #ipo_import_results li.is-success {
        background: #edfaef;
    }

    #ipo_import_results li.is-existing {
        background: #eef5ff;
    }

    #ipo_import_results li.is-error {
        background: #fcf0f1;
    }
</style>

        <form method="get" action="" style="direction: ltr;">
            <input type="hidden" name="page" value="event-table">
            <label for="filter_program">Filter by Program:</label>
            <input id="filter_program" name="filter_program" placeholder="Start typing to search..." style="
                width: 350px;
                height: 35px;
                padding: 5px;
                margin-top: 25px;
                margin-bottom: 25px;
            "/>
            <input type="hidden" id="filter_program_id" name="filter_program_id" value="">

            <label for="filter_event">Filter by Event:</label>
            <input id="filter_event" name="filter_event" placeholder="Start typing to search..." style="
                width: 350px;
                height: 35px;
                padding: 5px;
                margin-top: 25px;
                margin-bottom: 25px;
            "/>
            <input type="hidden" id="filter_event_id" name="filter_event_id" value="">

            <input type="submit" value="Filter">

            <button type="button" id="clear_filter">Clear Filter</button>
            <button type="button" id="show_unrelated_events">Show Unrelated Events</button>
        </form>

        <div id="event-table-container">
            <?php load_all_events(); ?>
        </div>
        <input type="hidden" id="event_update_nonce" value="<?php echo wp_create_nonce('event_update_nonce'); ?>">
    </div>

<?php
$current_admin_lang = apply_filters('wpml_current_language', null);
?>

 <script type="text/javascript">
jQuery(document).ready(function($) {
    let ipoApiEvents = [];

    $('#clear_filter').on('click', function() {
        window.location.href = '<?php echo admin_url('admin.php?page=event-table'); ?>';
    });

    $('#show_unrelated_events').on('click', function() {
        window.location.href = '<?php echo admin_url('admin.php?page=event-table&unrelated=1'); ?>';
    });

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function updateSelectedEventsCount() {
        const selectedCount = $('.ipo-api-event-checkbox:checked').length;
        $('#ipo_events_selected_count').text('נבחרו ' + selectedCount + ' אירועים');
    }

    function renderApiEvents(events) {
        const container = $('#ipo_api_events_list');

        if (!events.length) {
            container.html('<div style="padding:16px;">לא נמצאו אירועים.</div>');
            $('#ipo_events_loaded_count').text('0 אירועים');
            updateSelectedEventsCount();
            return;
        }

        const html = events.map(function(event) {
            const label = event.label || '';
            const searchValue = label.toLowerCase();

            return `
                <label class="ipo-api-event-item" data-search="${escapeHtml(searchValue)}">
                    <input
                        type="checkbox"
                        class="ipo-api-event-checkbox"
                        value="${escapeHtml(event.id)}"
                        data-label="${escapeHtml(label)}"
                    >
                    <span class="ipo-api-event-item-text">${escapeHtml(label)}</span>
                </label>
            `;
        }).join('');

        container.html(html);
        $('#ipo_events_loaded_count').text(events.length + ' אירועים נטענו');
        updateSelectedEventsCount();
    }

    function loadEventsFromAPI() {
        $('#ipo_api_events_list').html('<div style="padding:16px;">טוען אירועים...</div>');

        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'load_events_from_api'
            },
            success: function(response) {
                if (!response.success) {
                    $('#ipo_api_events_list').html('<div style="padding:16px;">טעינת האירועים נכשלה.</div>');
                    alert('טעינת האירועים נכשלה: ' + response.data);
                    return;
                }

                ipoApiEvents = response.data.events || [];
                renderApiEvents(ipoApiEvents);
            },
            error: function(xhr, status, error) {
                $('#ipo_api_events_list').html('<div style="padding:16px;">טעינת האירועים נכשלה.</div>');
                alert('שגיאה בטעינת האירועים: ' + error);
            }
        });
    }

    function updateVisibleEventsBySearch() {
        const searchValue = $('#ipo_event_search').val().trim().toLowerCase();

        $('.ipo-api-event-item').each(function() {
            const item = $(this);
            const searchText = String(item.data('search') || '');

            item.toggle(searchText.includes(searchValue));
        });
    }

    function updateImportProgress(current, total) {
        const percent = total > 0 ? Math.round((current / total) * 100) : 0;

        $('#ipo_import_progress_counter').text(current + ' / ' + total);
        $('#ipo_import_progress_fill').css('width', percent + '%');
    }

    function addProgressItem(eventId, label) {
        $('#ipo_import_results').append(`
            <li id="ipo-import-item-${escapeHtml(eventId)}" class="is-loading">
                <strong>${escapeHtml(label)}</strong><br>
                <span>בתהליך יצירה...</span>
            </li>
        `);
    }

    function updateProgressItem(eventId, type, label, message, editLink) {
        const item = $('#ipo-import-item-' + eventId);

        item
            .removeClass('is-loading is-success is-existing is-error')
            .addClass(type);

        let html = `
            <strong>${escapeHtml(label)}</strong><br>
            <span>${escapeHtml(message)}</span>
        `;

        if (editLink) {
            html += ` — <a href="${escapeHtml(editLink)}" target="_blank">פתח עריכה</a>`;
        }

        item.html(html);
    }


async function importSelectedEvents(selectedEvents) {
    const nonce = $('#event_update_nonce').val();
    const total = selectedEvents.length;

    let completed = 0;
    let created = 0;

    let failed = 0;

    $('#event_update_btn')
        .prop('disabled', true)
        .text('מוסיף אירועים...');

    $('#ipo_import_results').empty();
    $('#ipo_import_progress').show();
    $('#ipo_import_progress_title').text('ייבוא האירועים מתבצע...');
    updateImportProgress(0, total);

    for (const event of selectedEvents) {
        addProgressItem(event.id, event.label);

        try {
            const response = await $.ajax({
                url: ajaxurl,
                type: 'post',
            data: {
    action: 'add_event_from_api',
    nonce: nonce,
    apiEventId: event.id,
    lang: '<?php echo esc_js($current_admin_lang); ?>'
}
            });

            if (!response.success) {
                failed++;

                updateProgressItem(
                    event.id,
                    'is-error',
                    event.label,
                    response.data || 'יצירת האירוע נכשלה.'
                );
            } else {
                const editLink = response.data.edit_link || '';
                const status = response.data.status || 'created';

                if (status === 'existing') {
                    updateProgressItem(
                        event.id,
                        'is-existing',
                        event.label,
                        'האירוע כבר קיים במערכת.',
                        editLink
                    );
                } else {
                    created++;

                    updateProgressItem(
                        event.id,
                        'is-success',
                        event.label,
                        'האירוע נוצר בהצלחה.',
                        editLink
                    );
                }
            }
        } catch (error) {
            failed++;

            updateProgressItem(
                event.id,
                'is-error',
                event.label,
                'אירעה שגיאת תקשורת בזמן יצירת האירוע.'
            );
        }

        completed++;
        updateImportProgress(completed, total);

        await new Promise(function(resolve) {
            setTimeout(resolve, 250);
        });
    }

    $('#ipo_import_progress_title').text(
        'הייבוא הסתיים: ' +
        created + ' נוצרו, ' +
failed + ' נכשלו'
    );

    $('#event_update_btn')
        .prop('disabled', false)
        .text('הוספת אירועים נבחרים');
}
   
    loadEventsFromAPI();

    $('#ipo_event_search').on('input', function() {
        updateVisibleEventsBySearch();
    });

    $(document).on('change', '.ipo-api-event-checkbox', function() {
        updateSelectedEventsCount();
    });

    $('#ipo_select_visible_events').on('click', function() {
        $('.ipo-api-event-item:visible .ipo-api-event-checkbox').prop('checked', true);
        updateSelectedEventsCount();
    });

    $('#ipo_clear_selected_events').on('click', function() {
        $('.ipo-api-event-checkbox').prop('checked', false);
        updateSelectedEventsCount();
    });

    $('#event_update_btn').on('click', function(e) {
        e.preventDefault();

        const selectedEvents = $('.ipo-api-event-checkbox:checked').map(function() {
            return {
                id: $(this).val(),
                label: $(this).data('label')
            };
        }).get();

        if (!selectedEvents.length) {
            alert('יש לבחור לפחות אירוע אחד.');
            return;
        }

        importSelectedEvents(selectedEvents);
    });

    $('#filter_program').autocomplete({
        source: programList,
        select: function(event, ui) {
            $('#filter_program').val(ui.item.label);
            $('#filter_program_id').val(ui.item.value);
            return false;
        }
    });

    $('#filter_event').autocomplete({
        source: eventList,
        select: function(event, ui) {
            $('#filter_event').val(ui.item.label);
            $('#filter_event_id').val(ui.item.value);
            return false;
        }
    });
});
</script>
    <?php
}




// Function to load all events
function load_all_events() {
    $filter_program = isset($_GET['filter_program_id']) ? intval($_GET['filter_program_id']) : '';
    $filter_event = isset($_GET['filter_event']) ? sanitize_text_field($_GET['filter_event']) : '';
    $unrelated = isset($_GET['unrelated']) ? true : false;

    $args = array(
        'post_type' => 'event',
        'posts_per_page' => 1000,
        'post_status' => array('publish', 'draft'),
    );

    if ($filter_program) {
        $args['meta_query'] = array(
            array(
                'key' => 'related_to_program',
                'value' => $filter_program,
                'compare' => '='
            )
        );
    } elseif ($filter_event) {
        $args['s'] = $filter_event;
    } elseif ($unrelated) {
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key' => 'related_to_program',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => 'related_to_program',
                'value' => '',
                'compare' => '='
            )
        );
    }

    $events = new WP_Query($args);
    $event_data = array();

    if ($events->have_posts()) {
        while ($events->have_posts()) {
            $events->the_post();
            $event_date = get_field('event_date');
            $related_to_program_id = get_field('related_to_program');
            $related_to_program_title = $related_to_program_id ? get_the_title($related_to_program_id) : 'N/A';

            // Convert event_date to a sortable format (YYYYMMDD)
            $event_date_sortable = date('Ymd', strtotime($event_date));

            // Get custom fields
            $event_price_range = get_field('event_price_range');
            $location = wp_get_post_terms(get_the_ID(), 'location', array('fields' => 'names'));
            $location_name = $location ? implode(', ', $location) : 'N/A';

            $edit_link = get_edit_post_link();
            if ($edit_link && function_exists('pll_current_language')) {
                $edit_link = add_query_arg('lang', pll_current_language(), $edit_link);
            }

            $event_data[] = array(
                'title' => get_the_title(),
                'event_date' => $event_date,
                'event_date_formatted' => date('d-m-Y', strtotime($event_date)),
                'event_date_sortable' => $event_date_sortable,
                'related_to_program_title' => $related_to_program_title,
                'event_price_range' => $event_price_range,
                'location' => $location_name,
                'edit_link' => $edit_link,
            );
        }
        wp_reset_postdata();

        // Sort events by event_date_sortable in descending order
        usort($event_data, function($a, $b) {
            return $b['event_date_sortable'] - $a['event_date_sortable'];
        });

        // Display the sorted events in a table
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Event Name</th><th>Event Date</th><th>Related To Program</th><th>Price Range</th><th>Location</th><th>Edit Link</th></tr></thead>';
        echo '<tbody>';
        foreach ($event_data as $event) {
            echo '<tr>';
            echo '<td>' . esc_html($event['title']) . '</td>';
            echo '<td>' . esc_html($event['event_date_formatted']) . '</td>';
            echo '<td>' . esc_html($event['related_to_program_title']) . '</td>';
            echo '<td>' . esc_html($event['event_price_range']) . '</td>';
            echo '<td>' . esc_html($event['location']) . '</td>';
            echo '<td><a href="' . esc_url($event['edit_link']) . '" target="_blank">Edit</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No events found.</p>';
    }
}


function ipo_get_api_presentations() {
    $cache_key = 'ipo_api_presentations_cache';
    $cached_events = get_transient($cache_key);

    if (is_array($cached_events)) {
        return $cached_events;
    }

    $api_url = 'https://ipo.presglobal.store/api/presentations/';

    $response = wp_remote_get($api_url, array(
        'timeout'   => 20,
        'sslverify' => false,
    ));

    if (is_wp_error($response)) {
        error_log('API Request Error: ' . $response->get_error_message());
        return array();
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if (empty($data) || empty($data->presentations) || !is_array($data->presentations)) {
        error_log('Invalid API Response: ' . print_r($body, true));
        return array();
    }

    set_transient($cache_key, $data->presentations, 5 * MINUTE_IN_SECONDS);

    return $data->presentations;
}

function ipo_get_api_event_by_id($api_event_id) {
    $events = ipo_get_api_presentations();

    foreach ($events as $event) {
        if (!empty($event->id) && (int) $event->id === (int) $api_event_id) {
            return $event;
        }
    }

    return false;
}



function ipo_get_event_admin_edit_link($post_id) {
    $edit_link = get_edit_post_link($post_id, 'edit');

    if ($edit_link && function_exists('pll_current_language')) {
        $edit_link = add_query_arg('lang', pll_current_language(), $edit_link);
    }

    return $edit_link;
}

add_action('wp_ajax_load_events_from_api', 'load_events_from_api');
function load_events_from_api() {
    $presentations = ipo_get_api_presentations();

    if (empty($presentations)) {
        wp_send_json_error('No valid events found.');
    }

    $events = array();

    foreach ($presentations as $event) {
        if (empty($event->id)) {
            continue;
        }

        $feature_name = !empty($event->featureName) ? $event->featureName : 'Unknown Feature';
        $date_time = !empty($event->dateTime) ? $event->dateTime : 'Unknown Date';

        $events[] = array(
            'id'    => (int) $event->id,
            'label' => '#' . $event->id . ' | ' . $feature_name . ' | ' . $date_time,
        );
    }

    if (empty($events)) {
        wp_send_json_error('No valid events found.');
    }

    wp_send_json_success(array(
        'events' => $events,
    ));
}

add_action('wp_ajax_add_event_from_api', 'add_event_from_api');
function add_event_from_api() {
    check_ajax_referer('event_update_nonce', 'nonce');

    $api_event_id = isset($_POST['apiEventId']) ? absint($_POST['apiEventId']) : 0;

    if (!$api_event_id) {
        wp_send_json_error('Missing event ID.');
    }



    $event_data = ipo_get_api_event_by_id($api_event_id);

    if (!$event_data) {
        wp_send_json_error('Failed to fetch event data from API.');
    }

    $current_lang = ipo_get_current_admin_language();
    $existing = ( new ipo_event() )->get_event_by_api_id( $api_event_id, $current_lang );
    if ( $existing ) {
        wp_send_json_success( array(
            'status'    => 'existing',
            'edit_link' => ipo_get_event_admin_edit_link( $existing ),
        ) );
    }

    $post_id = wp_insert_post(array(
        'post_title'  => sanitize_text_field(($event_data->featureName ?? '') . ' | ' . ($event_data->dateTime ?? '')),
        'post_type'   => 'event',
        'post_status' => 'publish',
    ), true);

    if (is_wp_error($post_id)) {
        wp_send_json_error('Failed to create event post.');
    }

do_action('wpml_set_element_language_details', array(
	'element_id'           => $post_id,
	'element_type'         => 'post_event',
	'trid'                 => false,
	'language_code'        => $current_lang,
	'source_language_code' => null,
));

    update_field('event_date_time', $event_data->dateTime ?? '', $post_id);
    update_field('event_date', !empty($event_data->dateTime) ? date('Y-m-d', strtotime($event_data->dateTime)) : '', $post_id);
    update_field('event_api_id', $event_data->id ?? '', $post_id);
    update_field('event_featured_name', $event_data->featureName ?? '', $post_id);
    update_field('imported_location', trim(($event_data->venueLocation ?? '') . ', ' . ($event_data->venueCity ?? ''), ', '), $post_id);
    update_field('imported_hall', $event_data->venueName ?? '', $post_id);

    if (!empty($event_data->priceLevels) && is_array($event_data->priceLevels)) {
        $min_prices = array();
        $max_prices = array();

        foreach ($event_data->priceLevels as $price_level) {
            if (isset($price_level->minPrice)) {
                $min_prices[] = (float) $price_level->minPrice;
            }

            if (isset($price_level->maxPrice)) {
                $max_prices[] = (float) $price_level->maxPrice;
            }
        }

        if (!empty($min_prices) && !empty($max_prices)) {
            $min_price = min($min_prices);
            $max_price = max($max_prices);

            $price_display = $min_price === $max_price
                ? $min_price . ' nis'
                : $min_price . '-' . $max_price . ' nis';

            update_field('event_price_range', $price_display, $post_id);
        }
    }

    wp_send_json_success(array(
        'status'    => 'created',
        'edit_link' => ipo_get_event_admin_edit_link($post_id),
    ));
}


// Class to handle the API requests
class ipo_new_events_api {
    public $url;
    public $events;

    function __construct() {
        $this->url = 'https://ipo.pres.global/api/presentations';
    }

    public function init($debug=false){
        $ch = curl_init();
        $options = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $this->url,
            CURLOPT_TIMEOUT        => 10
        ];
        curl_setopt_array($ch, $options);
        $json = json_decode(curl_exec($ch));
        $this->events = isset($json->presentations) ? $json->presentations : [];

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        curl_close($ch);

        if ($error_msg || empty($json)) {
            $this->events = get_option('ipo_new_events');
        } else {
            update_option('ipo_new_events', $json);
        }

        if ($debug) {
            print_r($error_msg);
            print_r($json);
        }
    }

    public function get_event($event_id) {
        if (is_array($this->events)) {
            foreach ($this->events as $event) {
                if (intval($event->id) == $event_id) {
                    return $event;
                }
            }
        }
        return false;
    }

    public function get_event_data_url($event_id, $lang = false) {
        if ($lang == false) {
            $lang = ICL_LANGUAGE_CODE;
        }
        return $this->url . '/' . $event_id . '?lang=' . $lang;
    }

    public function get_price_range($event_id) {
        $url = $this->get_event_data_url($event_id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return 'Error: ' . curl_error($ch); // Handle error properly
        }
        curl_close($ch);

        $json = json_decode($response);
        if (!isset($json->presentation->priceLevels)) {
            return 'No price levels available';
        }

        $min_price = null;
        $max_price = null;

        foreach ($json->presentation->priceLevels as $level) {
            if ($min_price === null || $level->minPrice < $min_price) {
                $min_price = $level->minPrice;
            }
            if ($max_price === null || $level->maxPrice > $max_price) {
                $max_price = $level->maxPrice;
            }
        }

        if ($min_price !== null && $max_price !== null) {
            return $min_price . '-' . $max_price;
        }

        return '';
    }
}


function display_whatsapp_images() {
    $whatsapp_image = get_field('whatsapp_image');
    $whatsapp_image_mobile = get_field('whatsapp_image_mobile');
    $whatsapp_link = get_field('WhatsApp_link');

    if ($whatsapp_image_mobile) {
        ob_start();
        ?>
        <div class="whatsapp-banner">
            <a href="<?php echo esc_url($whatsapp_link); ?>">
                <img src="<?php echo esc_url($whatsapp_image_mobile); ?>" alt="WhatsApp Mobile Banner">
            </a>
        </div>
        <div class="whatsapp-banner-desktop">
            <a href="<?php echo esc_url($whatsapp_link); ?>">
                <img src="<?php echo esc_url($whatsapp_image); ?>" alt="WhatsApp Desktop Banner">
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
}

add_shortcode('whatsapp_banner', 'display_whatsapp_images');


function display_svg_with_background() {
    $svg_pc = get_field('SVG_PC');
    $svg_mobile = get_field('SVG_Mobile');
    $background_color = get_field('background_color');
    
    $svg_pc_img = get_field('SVG_pc_img');
    $svg_mobile_img  = get_field('SVG_Mobile_img'); 
    if (!$svg_pc && !$svg_mobile &&  !$svg_pc_img  && !$svg_mobile_img ){ 
        return '';
    }
    if ($svg_pc_img  && $svg_mobile_img ) {
    $background_color_pc = $background_color;
    $background_color_mobile = $background_color;
return '

    <div class="svg-container">' . do_shortcode('[ipo-breadcrumbs]') . '
        <div class="svg-pc" style="background-color: ' . esc_attr($background_color_pc) . ';"><img src="' . esc_url(is_array($svg_pc_img) ? $svg_pc_img['url'] : $svg_pc_img) . '"></div>
        <div class="svg-mobile" style="background-color: ' . esc_attr($background_color_mobile) . ';"><img src="' . esc_url(is_array($svg_mobile_img) ? $svg_mobile_img['url'] : $svg_mobile_img) . '"></div>
    </div>';

}


    

    $background_color_pc = $background_color;
    $background_color_mobile = $background_color;

    return '
    <div class="svg-container">' . do_shortcode('[ipo-breadcrumbs]') . '
        <div class="svg-pc" style="background-color: ' . esc_attr($background_color_pc) . ';">' . $svg_pc . '</div>
        <div class="svg-mobile" style="background-color: ' . esc_attr($background_color_mobile) . ';">' . $svg_mobile . '</div>
    </div>';
}

add_shortcode('display_svg', 'display_svg_with_background');


function increase_image_dimensions($sizes) {
    $sizes['large'] = array(
        'width'  => 4000,
        'height' => 4000,
        'crop'   => false,
    );
    return $sizes;
}
add_filter('big_image_size_threshold', '__return_false');
add_filter('intermediate_image_sizes_advanced', 'increase_image_dimensions');


add_action('wp_footer', function () {
    if (is_admin()) return;
    if (!empty($_COOKIE['rg_privacy_consent']) && $_COOKIE['rg_privacy_consent'] === '1') return;

    $lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : substr(get_locale(), 0, 2);
    $is_he = in_array(strtolower($lang), ['he','he-il','he_il']);
    $dir = $is_he ? 'rtl' : 'ltr';
    $policy_url = 'https://www.ipo.co.il/privacy-policy/';

    $text = $is_he
        ? 'עדכנו את מדיניות הפרטיות שלנו. המדיניות המעודכנת תיכנס לתוקף ב־28 באוגוסט 2025. שימוש מתמשך בשירות מהווה הסכמה לתנאים החדשים.'
        : 'We have updated our Privacy Policy. The revised policy will take effect on August 28, 2025. Continued use of the service constitutes acceptance of the new terms.';

    $link_label = $is_he ? 'תקנות האתר ומדיניות פרטיות' : 'View Privacy Policy';
    $accept_label = $is_he ? 'מאשר' : 'Accept';
    ?>
    <div id="rg-privacy-wrap" style="position:fixed;left:0;right:0;bottom:0;z-index:99999">
      <div style="direction:<?php echo esc_attr($dir); ?>;background:#1f1f22;color:#fff;border-radius:0;padding:14px 18px;display:flex;flex-flow:wrap;align-items:center;gap:12px;box-shadow:0 -2px 10px rgba(0,0,0,.35);width:100%;margin:0;font-family:'Simpler',sans-serif;font-size:14px">
        <div style="flex:1 1 100%;margin-bottom:6px"><?php echo esc_html($text); ?></div>
        <a href="<?php echo esc_url($policy_url); ?>" style="display:inline-flex;align-items:center;justify-content:center;border:1px solid #fff;color:#fff;text-decoration:none;border-radius:9999px;padding:10px 22px;white-space:nowrap"><?php echo esc_html($link_label); ?></a>
        <button id="rg-privacy-accept" style="border:0;background:#6c4eff;color:#fff;font-weight:700;border-radius:9999px;padding:10px 24px;cursor:pointer;white-space:nowrap"><?php echo esc_html($accept_label); ?></button>
      </div>
    </div>
    <script>
      (function () {
        if (document.cookie.indexOf('rg_privacy_consent=1') !== -1) {
          var w = document.getElementById('rg-privacy-wrap'); if (w) w.remove();
          return;
        }
        var btn = document.getElementById('rg-privacy-accept');
        if (!btn) return;
        btn.addEventListener('click', function () {
          var expires = new Date(Date.now() + 365*24*60*60*1000).toUTCString();
          document.cookie = 'rg_privacy_consent=1; expires=' + expires + '; path=/; SameSite=Lax';
          var w = document.getElementById('rg-privacy-wrap'); if (w) w.remove();
        });
      })();
    </script>
    <?php
});


add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }

    $redirect_ids = [41563, 28593];

    if (is_singular() && in_array((int) get_queried_object_id(), $redirect_ids, true)) {
        wp_redirect('https://donate.ipo.co.il/', 301);
        exit;
    }
}, 1);

function ipo_get_current_admin_language() {
	if (!empty($_REQUEST['lang'])) {
		return sanitize_key($_REQUEST['lang']);
	}

	$lang = apply_filters('wpml_current_language', null);

	if (!empty($lang)) {
		return $lang;
	}

	return apply_filters('wpml_default_language', null) ?: 'he';
}