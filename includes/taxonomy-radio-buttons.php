<?php 

/**
 * Use radio inputs instead of checkboxes for term checklists in specified taxonomies.
 * https://wordpress.stackexchange.com/questions/139269/wordpress-taxonomy-radio-buttons
 */
function term_radio_checklist( $args ) {
    if (  ! empty( $args['taxonomy'] ) && ( $args['taxonomy'] === 'event_type' || $args['taxonomy'] === 'location' || $args['taxonomy'] === 'gen_plan_cat'  )  ) {
        if ( empty( $args['walker'] ) || is_a( $args['walker'], 'Walker' ) ) { // Don't override 3rd party walkers.
            if ( ! class_exists( 'Category_Radio_Checklist' ) ) {
                class Category_Radio_Checklist extends Walker_Category_Checklist {
                    function walk( $elements, $max_depth, ...$args ) {
                        $output = parent::walk( $elements, $max_depth, ...$args );
                        $output = str_replace(
                            array( 'type="checkbox"', "type='checkbox'" ),
                            array( 'type="radio"', "type='radio'" ),
                            $output
                        );

                        return $output;
                    }
                }
            }

            $args['walker'] = new Category_Radio_Checklist;
        }
    }

    return $args;
}

add_filter( 'wp_terms_checklist_args', 'term_radio_checklist' );