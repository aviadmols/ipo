<?php
/*
* Template Name: New Series Page
*/

get_header();

global $post, $theme;

$page_id = get_queried_object_id();
$current_post = get_post($page_id);

if (!$current_post) {
    get_footer();
    return;
}

if (!function_exists('ipo_newseries_normalize_link')) {
    function ipo_newseries_normalize_link($link) {
        if (empty($link)) {
            return null;
        }

        if (is_array($link)) {
            $url = !empty($link['url']) ? $link['url'] : '';

            if (empty($url)) {
                return null;
            }

            return [
                'url' => $url,
                'title' => !empty($link['title']) ? $link['title'] : $url,
                'target' => !empty($link['target']) ? $link['target'] : '_self',
            ];
        }

        if (is_string($link)) {
            $url = trim($link);

            if (empty($url)) {
                return null;
            }

            return [
                'url' => $url,
                'title' => $url,
                'target' => '_self',
            ];
        }

        return null;
    }
}

if (!function_exists('ipo_newseries_normalize_posts')) {
    function ipo_newseries_normalize_posts($items) {
        if (empty($items)) {
            return [];
        }

        if (is_array($items)) {
            return array_values(array_filter($items));
        }

        if ($items instanceof WP_Post || is_numeric($items)) {
            return [$items];
        }

        return [];
    }
}

if (!function_exists('ipo_newseries_image_html')) {
    function ipo_newseries_image_html($image, $size = 'full') {
        if (empty($image)) {
            return '';
        }

        if (is_numeric($image)) {
            return wp_get_attachment_image(absint($image), $size);
        }

        if (is_array($image)) {
            if (!empty($image['ID'])) {
                return wp_get_attachment_image(absint($image['ID']), $size);
            }

            if (!empty($image['id'])) {
                return wp_get_attachment_image(absint($image['id']), $size);
            }

            if (!empty($image['url'])) {
                $alt = !empty($image['alt']) ? $image['alt'] : '';

                return sprintf(
                    '<img src="%s" alt="%s">',
                    esc_url($image['url']),
                    esc_attr($alt)
                );
            }
        }

        if (is_string($image)) {
            $url = trim($image);

            if (!empty($url)) {
                return sprintf(
                    '<img src="%s" alt="">',
                    esc_url($url)
                );
            }
        }

        return '';
    }
}

if (!function_exists('ipo_newseries_arrow_html')) {
    function ipo_newseries_arrow_html() {
        if (!function_exists('ipo_arrow_icon_url')) {
            return '';
        }

        $url = ipo_arrow_icon_url();

        if (empty($url)) {
            return '';
        }

        return sprintf(
            '<img src="%s" alt="">',
            esc_url($url)
        );
    }
}

$link_banner0 = ipo_newseries_normalize_link(get_field('linkbanner01', $page_id));
$link_banner_020 = ipo_newseries_normalize_link(get_field('linkbanner02', $page_id));

$subscribe_title = get_field('subscribe_title', $page_id);
$subscribe_text = get_field('subscribe_text', $page_id);
$subscribe_link = ipo_newseries_normalize_link(get_field('subscribe_link', $page_id));

$program_title = get_field('program_title', $page_id);
$program_subtitle = get_field('program_subtitle', $page_id);
$program_text = get_field('program_text', $page_id);
$program_link = ipo_newseries_normalize_link(get_field('program_link', $page_id));
$program_image = get_field('program_image', $page_id);

$is_en = defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en';

$tab_titles = $is_en
    ? [
        __('Tel Aviv', 'ipo'),
        __('Jerusalem', 'ipo'),
        __('Haifa', 'ipo'),
        __('Zucker', 'ipo'),
        __('Rehovot', 'ipo'),
    ]
    : [
        __('תל אביב', 'ipo'),
        __('ירושלים', 'ipo'),
        __('חיפה', 'ipo'),
        __('צוקר', 'ipo'),
        __('רחובות', 'ipo'),
    ];

$tab_fields = [
    'new_series_1',
    'new_series_2',
    'new_series_3',
    'new_series_4',
    'new_series_5',
];

$tabs = [];

foreach ($tab_fields as $index => $field_name) {
    $tab_content = ipo_newseries_normalize_posts(get_field($field_name, $page_id));

    if (!empty($tab_content)) {
        $tabs[] = [
            'title' => $tab_titles[$index],
            'content' => $tab_content,
        ];
    }
}

$content = apply_filters('the_content', $current_post->post_content);

$banner_image = wp_get_attachment_image(get_post_thumbnail_id($page_id), 'full');
$default_banner = get_field('pages_placeholder_image', 'option');

if (empty($banner_image) && !empty($default_banner)) {
    $banner_image = ipo_newseries_image_html($default_banner);
}

$program_image_html = ipo_newseries_image_html($program_image);

$cta_buttons = array_filter([
    $link_banner0,
    $link_banner_020,
]);

$has_subscribe_section = !empty($subscribe_title) || !empty($subscribe_text) || !empty($subscribe_link);
$has_program_section = !empty($program_title) || !empty($program_subtitle) || !empty($program_text) || !empty($program_link) || !empty($program_image_html);
?>

<section class="hero_page-section" data-aos="fade-in" data-aos-duration="500" data-aos-delay="100">
    <div class="banner_image">
        <?php echo $banner_image; ?>
    </div>
    <div class="container"></div>
    <div class="gradient-top"></div>
</section>

<?php if (!empty($content)) : ?>
    <section class="section-content">
        <?php echo $content; ?>
    </section>
<?php endif; ?>

<?php if (!empty($tabs)) : ?>
    <section class="series-tabs no-margin" data-aos="fade-in" data-aos-duration="500" data-aos-delay="100">
        <div class="about_area container max-1440">
            <h1 class="text-center pb-25">
                <?php echo esc_html(get_the_title($page_id)); ?>
            </h1>

            <div class="tabs-nav">
                <?php foreach ($tabs as $index => $tab) : ?>
                    <?php $tab_id = $index + 1; ?>
                    <div class="tab-nav" data-tab-id="tab-<?php echo esc_attr($tab_id); ?>">
                        <a href="#anchor-tab-<?php echo esc_attr($tab_id); ?>">
                            <?php echo esc_html($tab['title']); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="tabs-content">
                <?php foreach ($tabs as $index => $tab) : ?>
                    <?php $tab_id = $index + 1; ?>

                    <div class="tab-content" id="tab-<?php echo esc_attr($tab_id); ?>">
                        <div class="anchor" id="anchor-tab-<?php echo esc_attr($tab_id); ?>"></div>

                        <h3 class="headline">
                            <?php echo esc_html($tab['title']); ?>
                        </h3>

                        <ul class="list-series">
                            <?php foreach ($tab['content'] as $series_post) : ?>
                                <?php
                                if (isset($theme) && is_object($theme) && method_exists($theme, 'the_part')) {
                                    $theme->the_part('loop-serie', $series_post);
                                }
                                ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <style>
            .tabs-nav {
                display: flex;
                flex-flow: wrap;
            }

            @media (max-width: 768px) {
                .about_area h1:not(.title-pages),
                .main_body h1:not(.title-pages) {
                    font-size: 70px !important;
                }
            }
        </style>
    </section>
<?php endif; ?>

<?php if ($has_subscribe_section) : ?>
    <section class="subscribe-section pt-100 pb-100">
        <div class="container">
            <div class="subscribe-content">
                <div class="title">
                    <?php if (!empty($subscribe_title)) : ?>
                        <h2 class="lette-sapce-10">
                            <?php echo wp_kses_post($subscribe_title); ?>
                        </h2>
                    <?php endif; ?>
                </div>

                <div class="sb-text">
                    <?php echo wp_kses_post($subscribe_text); ?>

                    <?php if (!empty($subscribe_link)) : ?>
                        <div class="view-more pt-50">
                            <a class="white-color" href="<?php echo esc_url($subscribe_link['url']); ?>" target="<?php echo esc_attr($subscribe_link['target']); ?>">
                                <?php echo esc_html($subscribe_link['title']); ?>
                                <?php echo ipo_newseries_arrow_html(); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($has_program_section) : ?>
    <section class="key-plan pt-100 pb-100">
        <div class="container">
            <div class="row align-items-center row-reverse">
                <div class="col-lg-6">
                    <div class="text">
                        <div class="title">
                            <?php if (!empty($program_title)) : ?>
                                <h2 class="lette-sapce-10">
                                    <?php echo wp_kses_post($program_title); ?>
                                </h2>
                            <?php endif; ?>

                            <?php if (!empty($program_subtitle)) : ?>
                                <h3 class="sub-title-simpler">
                                    <?php echo wp_kses_post($program_subtitle); ?>
                                </h3>
                            <?php endif; ?>
                        </div>

                        <?php echo wp_kses_post($program_text); ?>

                        <?php if (!empty($program_link)) : ?>
                            <div class="view-more">
                                <a href="<?php echo esc_url($program_link['url']); ?>" target="<?php echo esc_attr($program_link['target']); ?>">
                                    <?php echo esc_html($program_link['title']); ?>
                                    <?php echo ipo_newseries_arrow_html(); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($program_image_html)) : ?>
                    <div class="col-lg-6">
                        <div class="thumb">
                            <?php echo $program_image_html; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($cta_buttons)) : ?>
    <section class="cta-section text-center pt-100 pb-100">
        <div class="container">
            <div class="buttons">
                <?php foreach ($cta_buttons as $button) : ?>
                    <a class="btn" href="<?php echo esc_url($button['url']); ?>" target="<?php echo esc_attr($button['target']); ?>">
                        <?php echo esc_html($button['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php get_footer(); ?>