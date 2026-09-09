<?php
/**
 * Settings screen for the program-card zones, under the Event Table menu.
 *
 * One tab per zone. The single-program tab also carries an override per
 * program category, so a children's programme can pull from a different set of
 * categories than a classical one.
 *
 * The site has ~750 published programs. Rendering that list into every picker
 * would run to a megabyte of markup, so it is printed once into a hidden
 * template and cloned per picker in the browser.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'event-table',
			'מודולי תוכניות מקושרות',
			'תוכניות מקושרות',
			'manage_options',
			'ipo-related-programs',
			'ipo_related_programs_admin_page'
		);
	},
	20
);

/**
 * Every published program as id => label.
 *
 * Titles repeat across languages and cities, so the language code and ID go in
 * the label — a list of six "Animals and Other Animals" rows is otherwise
 * impossible to pick from.
 */
function ipo_related_programs_admin_choices() {
	static $choices = null;

	if ( $choices !== null ) {
		return $choices;
	}

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

function ipo_related_programs_admin_categories() {
	$categories = get_terms(
		array(
			'taxonomy'   => 'category_program',
			'hide_empty' => false,
		)
	);

	return is_wp_error( $categories ) ? array() : $categories;
}

/**
 * A program picker. The options are cloned in from the hidden master list, so
 * only the current selection travels in the markup.
 */
function ipo_related_programs_admin_picker( $name, $selected, $description ) {
	$selected = array_map( 'intval', (array) $selected );
	?>
	<p class="description" style="margin:0 0 6px;"><?php echo esc_html( $description ); ?></p>
	<input type="search" class="regular-text ipo-picker-filter" placeholder="סינון לפי שם או מספר…" style="margin-bottom:6px;max-width:100%;">
	<select name="<?php echo esc_attr( $name ); ?>[]"
			multiple
			size="8"
			class="ipo-program-picker"
			data-selected="<?php echo esc_attr( implode( ',', $selected ) ); ?>"
			style="width:100%;max-width:620px;"></select>
	<p class="description">בחירה מרובה: Ctrl (או Cmd) + לחיצה. נבחרו <strong class="ipo-picker-count"><?php echo count( $selected ); ?></strong>.</p>
	<?php
}

/**
 * The rule fields, shared by a zone default and by every category override.
 *
 * @param string $prefix Input name prefix, e.g. zones[single_program][default].
 */
function ipo_related_programs_admin_ruleset_fields( $prefix, $ruleset, $show_source = true ) {
	$categories = ipo_related_programs_admin_categories();
	?>
	<table class="form-table" role="presentation">
		<?php if ( $show_source ) : ?>
			<tr>
				<th scope="row">מאיפה נשלפות התוכניות</th>
				<td>
					<label style="display:block;margin-bottom:4px;">
						<input type="radio" name="<?php echo esc_attr( $prefix ); ?>[mode]" value="same_category" <?php checked( $ruleset['mode'], 'same_category' ); ?>>
						אותה קטגוריה של התוכנית המוצגת
					</label>
					<label style="display:block;margin-bottom:4px;">
						<input type="radio" name="<?php echo esc_attr( $prefix ); ?>[mode]" value="categories" <?php checked( $ruleset['mode'], 'categories' ); ?>>
						הקטגוריות שסומנו כאן
					</label>
					<label style="display:block;">
						<input type="radio" name="<?php echo esc_attr( $prefix ); ?>[mode]" value="manual" <?php checked( $ruleset['mode'], 'manual' ); ?>>
						רק התוכניות שנוספו ידנית
					</label>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<th scope="row">קטגוריות</th>
			<td>
				<?php if ( empty( $categories ) ) : ?>
					<p class="description">לא נמצאו קטגוריות תוכנית.</p>
				<?php else : ?>
					<?php foreach ( $categories as $category ) : ?>
						<label style="display:inline-block;margin:0 0 6px 18px;">
							<input type="checkbox"
								   name="<?php echo esc_attr( $prefix ); ?>[categories][]"
								   value="<?php echo esc_attr( $category->term_id ); ?>"
								   <?php checked( in_array( (int) $category->term_id, $ruleset['categories'], true ) ); ?>>
							<?php echo esc_html( $category->name ); ?>
							<span class="description">(<?php echo (int) $category->count; ?>)</span>
						</label>
					<?php endforeach; ?>
					<p class="description">פעיל כשנבחר &laquo;הקטגוריות שסומנו כאן&raquo;.</p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row">הוספה ידנית</th>
			<td><?php ipo_related_programs_admin_picker( $prefix . '[manual_ids]', $ruleset['manual_ids'], 'תוכניות שיצטרפו גם אם חוקיות הקטגוריה לא מביאה אותן.' ); ?></td>
		</tr>
		<tr>
			<th scope="row">תוכניות מקודמות</th>
			<td><?php ipo_related_programs_admin_picker( $prefix . '[promoted_ids]', $ruleset['promoted_ids'], 'יוצגו ראשונות — רק אם הן עומדות בחוקיות ויש להן תאריך שטרם עבר.' ); ?></td>
		</tr>
		<tr>
			<th scope="row">תוכניות מוחרגות</th>
			<td><?php ipo_related_programs_admin_picker( $prefix . '[exclude_ids]', $ruleset['exclude_ids'], 'לא יוצגו בשום מקרה.' ); ?></td>
		</tr>
		<tr>
			<th scope="row">סדר</th>
			<td>
				<select name="<?php echo esc_attr( $prefix ); ?>[order]">
					<option value="date_asc" <?php selected( $ruleset['order'], 'date_asc' ); ?>>לפי תאריך — מהקרוב לרחוק</option>
					<option value="date_desc" <?php selected( $ruleset['order'], 'date_desc' ); ?>>לפי תאריך — מהרחוק לקרוב</option>
				</select>
				<p class="description">לפי האירוע העתידי הקרוב ביותר של כל תוכנית. מקודמות תמיד לפני השאר.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">אירועים עתידיים בלבד</th>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $prefix ); ?>[require_future_event]" value="1" <?php checked( $ruleset['require_future_event'], 1 ); ?>>
					להסתיר תוכניות שכל התאריכים שלהן עברו
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">מספר כרטיסים מרבי</th>
			<td>
				<input type="number" name="<?php echo esc_attr( $prefix ); ?>[max_items]" min="0" step="1" value="<?php echo esc_attr( $ruleset['max_items'] ); ?>" class="small-text">
				<p class="description">0 = ללא הגבלה.</p>
			</td>
		</tr>
	</table>
	<?php
}

function ipo_related_programs_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'ipo' ) );
	}

	$zones = ipo_related_programs_zones();
	$saved = false;

	if ( isset( $_POST['ipo_related_programs_save'] ) && check_admin_referer( 'ipo_related_programs_save' ) ) {
		$posted = isset( $_POST['zones'] ) && is_array( $_POST['zones'] ) ? wp_unslash( $_POST['zones'] ) : array();
		$store  = array();

		foreach ( $zones as $zone_key => $zone ) {
			$zone_posted = isset( $posted[ $zone_key ] ) && is_array( $posted[ $zone_key ] ) ? $posted[ $zone_key ] : array();

			$store[ $zone_key ] = array(
				'respect_page_pick' => empty( $zone_posted['respect_page_pick'] ) ? 0 : 1,
				'default'           => ipo_related_programs_sanitize_ruleset( isset( $zone_posted['default'] ) ? $zone_posted['default'] : array() ),
				'by_category'       => array(),
			);

			if ( empty( $zone['category_rules'] ) || empty( $zone_posted['by_category'] ) || ! is_array( $zone_posted['by_category'] ) ) {
				continue;
			}

			foreach ( $zone_posted['by_category'] as $term_id => $ruleset ) {
				$term_id = (int) $term_id;

				if ( ! $term_id ) {
					continue;
				}

				// Rules are kept even when switched off, so unticking the box
				// does not throw away a set-up that may be wanted again.
				$store[ $zone_key ]['by_category'][ $term_id ] = array_merge(
					ipo_related_programs_sanitize_ruleset( $ruleset ),
					array( 'enabled' => empty( $ruleset['enabled'] ) ? 0 : 1 )
				);
			}
		}

		update_option( IPO_RELATED_PROGRAMS_OPTION, $store );
		$saved = true;
	}

	$settings   = ipo_related_programs_get_settings();
	$stored     = get_option( IPO_RELATED_PROGRAMS_OPTION, array() );
	$stored     = is_array( $stored ) ? $stored : array();
	$categories = ipo_related_programs_admin_categories();
	$choices    = ipo_related_programs_admin_choices();
	$active     = isset( $_GET['zone'] ) ? sanitize_key( wp_unslash( $_GET['zone'] ) ) : 'single_program';
	$active     = isset( $zones[ $active ] ) ? $active : 'single_program';
	?>
	<div class="wrap" dir="rtl">
		<h1>מודולי תוכניות מקושרות</h1>
		<p class="description" style="max-width:760px;">
			לכל אזור באתר שמציג כרטיסי תוכנית יש כאן לשונית משלו. השמירה משותפת לכל הלשוניות —
			אפשר לערוך כמה אזורים ולשמור פעם אחת.
		</p>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p>ההגדרות נשמרו.</p></div>
		<?php endif; ?>

		<h2 class="nav-tab-wrapper">
			<?php foreach ( $zones as $zone_key => $zone ) : ?>
				<?php // Tabs switch in the browser rather than reloading, so edits made
					// across several zones all survive to the one save. ?>
				<a href="#<?php echo esc_attr( $zone_key ); ?>"
				   class="nav-tab ipo-zone-tab <?php echo $zone_key === $active ? 'nav-tab-active' : ''; ?>"
				   data-zone="<?php echo esc_attr( $zone_key ); ?>">
					<?php echo esc_html( $zone['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</h2>

		<form method="post">
			<?php wp_nonce_field( 'ipo_related_programs_save' ); ?>

			<?php foreach ( $zones as $zone_key => $zone ) : ?>
				<?php $zone_settings = $settings[ $zone_key ]; ?>
				<div class="ipo-zone-panel" data-zone="<?php echo esc_attr( $zone_key ); ?>" <?php echo $zone_key === $active ? '' : 'hidden'; ?>>
					<h2><?php echo esc_html( $zone['label'] ); ?></h2>
					<p class="description"><?php echo esc_html( $zone['description'] ); ?></p>

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">בחירה ידנית בעמוד</th>
							<td>
								<label>
									<input type="checkbox"
										   name="zones[<?php echo esc_attr( $zone_key ); ?>][respect_page_pick]"
										   value="1"
										   <?php checked( $zone_settings['respect_page_pick'], 1 ); ?>>
									לכבד את השדה <code><?php echo esc_html( $zone['page_pick_label'] ); ?></code> כשהוא מלא
								</label>
								<p class="description">
									כשמסומן, בחירה ידנית בעמוד קובעת את הרשימה, והחוקיות כאן רק ממיינת ומסננת אותה.
									כשלא מסומן, ההגדרות כאן קובעות תמיד.
								</p>
							</td>
						</tr>
					</table>

					<h3>חוקיות ברירת מחדל</h3>
					<?php ipo_related_programs_admin_ruleset_fields( 'zones[' . $zone_key . '][default]', $zone_settings['default'] ); ?>

					<?php if ( ! empty( $zone['category_rules'] ) && ! empty( $categories ) ) : ?>
						<h3>חוקיות לפי קטגוריה</h3>
						<p class="description" style="max-width:760px;">
							כשהתוכנית המוצגת שייכת לקטגוריה שסומנה כאן, החוקיות שלה מחליפה את ברירת המחדל.
							למשל: בקטגוריית ילדים אפשר לבחור &laquo;הקטגוריות שסומנו כאן&raquo; ולסמן גם ילדים וגם קאמרי.
							אם התוכנית שייכת לכמה קטגוריות, הראשונה עם חוקיות פעילה היא שקובעת.
						</p>

						<?php foreach ( $categories as $category ) : ?>
							<?php
							$term_id       = (int) $category->term_id;
							$stored_rule   = isset( $stored[ $zone_key ]['by_category'][ $term_id ] ) ? $stored[ $zone_key ]['by_category'][ $term_id ] : array();
							$category_rule = ipo_related_programs_sanitize_ruleset( $stored_rule );
							$enabled       = ! empty( $stored_rule['enabled'] );
							$prefix        = 'zones[' . $zone_key . '][by_category][' . $term_id . ']';
							?>
							<div class="ipo-category-rule" style="border:1px solid #dcdcde;background:#fff;padding:8px 16px;margin-bottom:10px;">
								<h4 style="margin:8px 0;">
									<label>
										<input type="checkbox"
											   class="ipo-category-toggle"
											   name="<?php echo esc_attr( $prefix ); ?>[enabled]"
											   value="1"
											   <?php checked( $enabled ); ?>>
										<?php echo esc_html( $category->name ); ?>
										<span class="description">— חוקיות נפרדת</span>
									</label>
								</h4>
								<div class="ipo-category-body" <?php echo $enabled ? '' : 'hidden'; ?>>
									<?php ipo_related_programs_admin_ruleset_fields( $prefix, $category_rule ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<?php submit_button( 'שמירת כל האזורים', 'primary', 'ipo_related_programs_save' ); ?>
		</form>

		<select id="ipo-program-master" hidden>
			<?php foreach ( $choices as $id => $label ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<script>
	(function () {
		var master = document.getElementById('ipo-program-master');

		if (!master) {
			return;
		}

		document.querySelectorAll('.ipo-program-picker').forEach(function (picker) {
			picker.innerHTML = master.innerHTML;

			var selected = (picker.dataset.selected || '')
				.split(',')
				.filter(function (value) { return value !== ''; });

			selected.forEach(function (value) {
				var option = picker.querySelector('option[value="' + value + '"]');

				// A program that was picked and later unpublished is no longer
				// in the master list; keep it so saving does not drop it.
				if (!option) {
					option = document.createElement('option');
					option.value = value;
					option.textContent = '#' + value + ' (לא פורסם / לא נמצא)';
					picker.appendChild(option);
				}

				option.selected = true;
			});

			var counter = picker.parentNode.querySelector('.ipo-picker-count');

			picker.addEventListener('change', function () {
				if (counter) {
					counter.textContent = picker.selectedOptions.length;
				}
			});
		});

		document.querySelectorAll('.ipo-picker-filter').forEach(function (input) {
			var picker = input.parentNode.querySelector('.ipo-program-picker');

			if (!picker) {
				return;
			}

			input.addEventListener('input', function () {
				var needle = input.value.trim().toLowerCase();

				Array.prototype.forEach.call(picker.options, function (option) {
					// A selected option stays visible whatever the filter says,
					// so it cannot be hidden and then lost on save.
					option.hidden = needle !== '' && !option.selected &&
						option.textContent.toLowerCase().indexOf(needle) === -1;
				});
			});
		});

		document.querySelectorAll('.ipo-zone-tab').forEach(function (tab) {
			tab.addEventListener('click', function (event) {
				event.preventDefault();

				document.querySelectorAll('.ipo-zone-tab').forEach(function (other) {
					other.classList.toggle('nav-tab-active', other === tab);
				});

				document.querySelectorAll('.ipo-zone-panel').forEach(function (panel) {
					panel.hidden = panel.dataset.zone !== tab.dataset.zone;
				});
			});
		});

		document.querySelectorAll('.ipo-category-toggle').forEach(function (toggle) {
			toggle.addEventListener('change', function () {
				var body = toggle.closest('.ipo-category-rule').querySelector('.ipo-category-body');

				if (body) {
					body.hidden = !toggle.checked;
				}
			});
		});
	})();
	</script>
	<?php
}
