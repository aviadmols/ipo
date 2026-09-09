<?php
/**
 * Settings screen for the related-programs module, under the Event Table menu.
 *
 * The program pickers hold ~750 options each, so they are plain multi-selects
 * with a filter box above them rather than three copies of the full list in a
 * select2-style widget. No external assets, nothing to enqueue.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'event-table',
			'מודול תוכניות מקושרות',
			'תוכניות מקושרות',
			'manage_options',
			'ipo-related-programs',
			'ipo_related_programs_admin_page'
		);
	},
	20
);

/**
 * Every published program, newest first, as id => label.
 *
 * Titles repeat across languages and cities, so the language code and ID go in
 * the label — otherwise a list of six "Animals and Other Animals" rows is
 * impossible to pick from.
 */
function ipo_related_programs_admin_choices() {
	$programs = get_posts(
		array(
			'post_type'        => 'program',
			'posts_per_page'   => -1,
			'post_status'      => 'publish',
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
		)
	);

	$choices = array();

	foreach ( $programs as $program ) {
		$language = apply_filters( 'wpml_post_language_details', null, $program->ID );
		$code     = is_array( $language ) && ! empty( $language['language_code'] ) ? $language['language_code'] : '';

		$choices[ $program->ID ] = sprintf(
			'%s%s (#%d)',
			$program->post_title,
			$code ? ' [' . $code . ']' : '',
			$program->ID
		);
	}

	return $choices;
}

/**
 * A filterable multi-select. Selected values stay listed even if the filter
 * hides everything else, so a save never silently drops a pick.
 */
function ipo_related_programs_admin_picker( $name, $selected, $choices, $description ) {
	$field_id = 'ipo-picker-' . sanitize_key( $name );
	?>
	<p class="description" style="margin-bottom:6px;"><?php echo esc_html( $description ); ?></p>
	<input type="search"
		   class="regular-text ipo-picker-filter"
		   data-target="<?php echo esc_attr( $field_id ); ?>"
		   placeholder="סינון לפי שם או מספר…"
		   style="margin-bottom:6px;">
	<select id="<?php echo esc_attr( $field_id ); ?>"
			name="<?php echo esc_attr( $name ); ?>[]"
			multiple
			size="12"
			style="width:100%;max-width:640px;">
		<?php foreach ( $choices as $id => $label ) : ?>
			<option value="<?php echo esc_attr( $id ); ?>" <?php selected( in_array( (int) $id, $selected, true ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<p class="description">בחירה מרובה: Ctrl (או Cmd) + לחיצה. נבחרו כרגע <strong><?php echo count( $selected ); ?></strong>.</p>
	<?php
}

function ipo_related_programs_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'ipo' ) );
	}

	$saved = false;

	if ( isset( $_POST['ipo_related_programs_save'] ) && check_admin_referer( 'ipo_related_programs_save' ) ) {
		$settings = array(
			'mode'                 => isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : 'same_category',
			'categories'           => isset( $_POST['categories'] ) ? array_map( 'intval', (array) $_POST['categories'] ) : array(),
			'manual_ids'           => isset( $_POST['manual_ids'] ) ? array_map( 'intval', (array) $_POST['manual_ids'] ) : array(),
			'promoted_ids'         => isset( $_POST['promoted_ids'] ) ? array_map( 'intval', (array) $_POST['promoted_ids'] ) : array(),
			'exclude_ids'          => isset( $_POST['exclude_ids'] ) ? array_map( 'intval', (array) $_POST['exclude_ids'] ) : array(),
			'require_future_event' => empty( $_POST['require_future_event'] ) ? 0 : 1,
			'max_items'            => isset( $_POST['max_items'] ) ? absint( $_POST['max_items'] ) : 0,
			'order'                => isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'date_asc',
		);

		update_option( IPO_RELATED_PROGRAMS_OPTION, $settings );
		$saved = true;
	}

	$settings   = ipo_related_programs_get_settings();
	$choices    = ipo_related_programs_admin_choices();
	$categories = get_terms(
		array(
			'taxonomy'   => 'category_program',
			'hide_empty' => false,
		)
	);
	$categories = is_wp_error( $categories ) ? array() : $categories;
	?>
	<div class="wrap" dir="rtl">
		<h1>מודול תוכניות מקושרות</h1>
		<p class="description" style="max-width:720px;">
			ההגדרות כאן קובעות את מודול &laquo;אולי יעניין אותך גם&raquo; בתחתית כל עמוד תוכנית.
			הן גלובליות ומחליפות את ההיגיון הקודם — השדה <code>program_related_programs</code>
			שבתוכנית בודדת כבר לא משפיע.
		</p>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p>ההגדרות נשמרו.</p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'ipo_related_programs_save' ); ?>

			<h2 class="title">מאיפה נשלפות התוכניות</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">חוקיות המקור</th>
					<td>
						<label style="display:block;margin-bottom:4px;">
							<input type="radio" name="mode" value="same_category" <?php checked( $settings['mode'], 'same_category' ); ?>>
							אותה קטגוריה של התוכנית המוצגת
						</label>
						<label style="display:block;margin-bottom:4px;">
							<input type="radio" name="mode" value="categories" <?php checked( $settings['mode'], 'categories' ); ?>>
							קטגוריות קבועות שנבחרו למטה
						</label>
						<label style="display:block;">
							<input type="radio" name="mode" value="manual" <?php checked( $settings['mode'], 'manual' ); ?>>
							רק התוכניות שנוספו ידנית
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">קטגוריות קבועות</th>
					<td>
						<?php if ( empty( $categories ) ) : ?>
							<p class="description">לא נמצאו קטגוריות תוכנית.</p>
						<?php else : ?>
							<?php foreach ( $categories as $category ) : ?>
								<label style="display:block;margin-bottom:4px;">
									<input type="checkbox"
										   name="categories[]"
										   value="<?php echo esc_attr( $category->term_id ); ?>"
										   <?php checked( in_array( (int) $category->term_id, $settings['categories'], true ) ); ?>>
									<?php echo esc_html( $category->name ); ?>
									<span class="description">(<?php echo (int) $category->count; ?>)</span>
								</label>
							<?php endforeach; ?>
							<p class="description">רלוונטי רק כשנבחרה החוקיות &laquo;קטגוריות קבועות&raquo;.</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<h2 class="title">תוכניות ספציפיות</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">הוספה ידנית</th>
					<td><?php ipo_related_programs_admin_picker( 'manual_ids', $settings['manual_ids'], $choices, 'תוכניות שיצטרפו למודול גם אם חוקיות הקטגוריה לא מביאה אותן.' ); ?></td>
				</tr>
				<tr>
					<th scope="row">תוכניות מקודמות</th>
					<td><?php ipo_related_programs_admin_picker( 'promoted_ids', $settings['promoted_ids'], $choices, 'יוצגו ראשונות — אבל רק אם הן עומדות בחוקיות שהוגדרה למעלה ויש להן תאריך שטרם עבר.' ); ?></td>
				</tr>
				<tr>
					<th scope="row">תוכניות מוחרגות</th>
					<td><?php ipo_related_programs_admin_picker( 'exclude_ids', $settings['exclude_ids'], $choices, 'לא יוצגו במודול בשום מקרה.' ); ?></td>
				</tr>
			</table>

			<h2 class="title">תצוגה</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">סדר</th>
					<td>
						<select name="order">
							<option value="date_asc" <?php selected( $settings['order'], 'date_asc' ); ?>>לפי תאריך — מהקרוב לרחוק</option>
							<option value="date_desc" <?php selected( $settings['order'], 'date_desc' ); ?>>לפי תאריך — מהרחוק לקרוב</option>
						</select>
						<p class="description">המיון לפי האירוע העתידי הקרוב ביותר של כל תוכנית. תוכניות מקודמות תמיד לפני השאר.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">אירועים עתידיים בלבד</th>
					<td>
						<label>
							<input type="checkbox" name="require_future_event" value="1" <?php checked( $settings['require_future_event'], 1 ); ?>>
							להסתיר תוכניות שכל התאריכים שלהן עברו
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">מספר כרטיסים מרבי</th>
					<td>
						<input type="number" name="max_items" min="0" step="1" value="<?php echo esc_attr( $settings['max_items'] ); ?>" class="small-text">
						<p class="description">0 = ללא הגבלה.</p>
					</td>
				</tr>
			</table>

			<?php submit_button( 'שמירת הגדרות', 'primary', 'ipo_related_programs_save' ); ?>
		</form>
	</div>

	<script>
	(function () {
		document.querySelectorAll('.ipo-picker-filter').forEach(function (input) {
			var select = document.getElementById(input.dataset.target);

			if (!select) {
				return;
			}

			input.addEventListener('input', function () {
				var needle = input.value.trim().toLowerCase();

				Array.prototype.forEach.call(select.options, function (option) {
					// A selected option stays visible whatever the filter says,
					// so it cannot be hidden and then lost on save.
					option.hidden = needle !== '' && !option.selected &&
						option.textContent.toLowerCase().indexOf(needle) === -1;
				});
			});
		});
	})();
	</script>
	<?php
}
