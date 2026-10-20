<?php
/**
 * Content settings screen.
 *
 * Everything the site draws from an option — the organisation's contact and
 * bank details, the impact counters, the giving tiers, the navigation — was
 * seeded into the database but had no way to edit it. This screen provides
 * that, driven by a schema so adding a field means adding one array entry.
 *
 * The design is not editable here by design: layout and markup live in the
 * templates, so a change made here can never break the site's appearance.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of every editable option.
 *
 * Each section maps to one option. `fields` describes the shape of a single
 * row; a section without `repeatable` holds one row, otherwise one row per
 * item in the option's array.
 *
 * Types: text, textarea, url, tel, email, number, int, select.
 *
 * @return array<string,array>
 */
function mmm_admin_schema() {
	$accent = array(
		'label'   => __( 'Accent', 'mmm' ),
		'type'    => 'select',
		'choices' => array(
			'blue'    => __( 'Blue', 'mmm' ),
			'navy'    => __( 'Navy', 'mmm' ),
			'magenta' => __( 'Magenta', 'mmm' ),
			'cyan'    => __( 'Cyan', 'mmm' ),
		),
		'default' => 'blue',
	);

	return array(

		'org' => array(
			'option'  => 'mmm_org',
			'title'   => __( 'Organisation', 'mmm' ),
			'desc'    => __( 'Shown in the masthead, the footer, the contact page and the structured data.', 'mmm' ),
			'fields'  => array(
				'name'        => array( 'label' => __( 'Short name', 'mmm' ), 'type' => 'text', 'default' => 'Muslim Medical Mission' ),
				'legalName'   => array( 'label' => __( 'Legal name', 'mmm' ), 'type' => 'text', 'default' => 'Muslim Medical Mission Foundation' ),
				'short'       => array( 'label' => __( 'Abbreviation', 'mmm' ), 'type' => 'text', 'default' => 'MMM' ),
				'motto'       => array( 'label' => __( 'Motto', 'mmm' ), 'type' => 'text' ),
				'founded'     => array( 'label' => __( 'Founded', 'mmm' ), 'type' => 'int' ),
				'phone'       => array( 'label' => __( 'Phone', 'mmm' ), 'type' => 'tel', 'default' => '+92 321 424 4433' ),
				'phoneHref'   => array( 'label' => __( 'Phone link', 'mmm' ), 'type' => 'text', 'hint' => __( 'The tel: target, e.g. tel:+923214244433', 'mmm' ) ),
				'whatsapp'    => array( 'label' => __( 'WhatsApp', 'mmm' ), 'type' => 'url', 'hint' => __( 'A wa.me link.', 'mmm' ) ),
				'email'       => array( 'label' => __( 'Email', 'mmm' ), 'type' => 'email', 'default' => 'info@mmm.org.pk' ),
				'mobileWallets' => array( 'label' => __( 'Mobile wallets', 'mmm' ), 'type' => 'text', 'hint' => __( 'The JazzCash / EasyPaisa line.', 'mmm' ) ),
			),
		),

		'address' => array(
			'option' => 'mmm_org_address',
			'title'  => __( 'Postal address', 'mmm' ),
			'desc'   => __( 'Each line is rendered separately, so keep them short.', 'mmm' ),
			'fields' => array(
				'line1'    => array( 'label' => __( 'Line 1', 'mmm' ), 'type' => 'text' ),
				'line2'    => array( 'label' => __( 'Line 2', 'mmm' ), 'type' => 'text' ),
				'country'  => array( 'label' => __( 'Line 3', 'mmm' ), 'type' => 'text' ),
			),
		),

		'bank' => array(
			'option' => 'mmm_org_bank',
			'title'  => __( 'Bank details', 'mmm' ),
			'desc'   => __( 'Printed on the donate page and in the bank-transfer block.', 'mmm' ),
			'fields' => array(
				'title'   => array( 'label' => __( 'Account name', 'mmm' ), 'type' => 'text' ),
				'bank'    => array( 'label' => __( 'Bank', 'mmm' ), 'type' => 'text' ),
				'account' => array( 'label' => __( 'Account number', 'mmm' ), 'type' => 'text' ),
				'iban'    => array( 'label' => __( 'IBAN', 'mmm' ), 'type' => 'text' ),
				'branch'  => array( 'label' => __( 'Branch', 'mmm' ), 'type' => 'textarea', 'rows' => 2 ),
			),
		),

		'social' => array(
			'option'     => 'mmm_social',
			'title'      => __( 'Social accounts', 'mmm' ),
			'desc'       => __( 'The footer links. The name also selects the icon.', 'mmm' ),
			'repeatable' => __( 'Account', 'mmm' ),
			'fields'     => array(
				'name' => array( 'label' => __( 'Name', 'mmm' ), 'type' => 'text', 'hint' => __( 'Facebook, Instagram, X or YouTube.', 'mmm' ), 'default' => 'Facebook' ),
				'href' => array( 'label' => __( 'Profile URL', 'mmm' ), 'type' => 'url' ),
			),
		),

		'impact_stats' => array(
			'option'     => 'mmm_impact_stats',
			'title'      => __( 'Impact statistics', 'mmm' ),
			'desc'       => __( 'The counters in the blue band on the home page.', 'mmm' ),
			'repeatable' => __( 'Statistic', 'mmm' ),
			'fields'     => array(
				'value'  => array( 'label' => __( 'Value', 'mmm' ), 'type' => 'number', 'hint' => __( 'Digits only, e.g. 1000000 — the site adds the commas.', 'mmm' ) ),
				'suffix' => array( 'label' => __( 'Suffix', 'mmm' ), 'type' => 'text', 'hint' => __( 'e.g. +  — leave empty for a plain number.', 'mmm' ) ),
				'label'  => array( 'label' => __( 'Label', 'mmm' ), 'type' => 'text', 'default' => 'Patients Served' ),
				'detail' => array( 'label' => __( 'Caption', 'mmm' ), 'type' => 'text' ),
			),
		),

		'giving_tiers' => array(
			'option'     => 'mmm_giving_tiers',
			'title'      => __( 'Giving tiers', 'mmm' ),
			'desc'       => __( 'What each donation amount buys, on the donate page.', 'mmm' ),
			'repeatable' => __( 'Tier', 'mmm' ),
			'fields'     => array(
				'amount' => array( 'label' => __( 'Amount (PKR)', 'mmm' ), 'type' => 'text', 'hint' => __( 'As displayed, e.g. 3,000.', 'mmm' ) ),
				'title'  => array( 'label' => __( 'Title', 'mmm' ), 'type' => 'text' ),
				'body'   => array( 'label' => __( 'Description', 'mmm' ), 'type' => 'textarea', 'rows' => 3 ),
				'accent' => $accent,
			),
		),

		'allocation' => array(
			'option'     => 'mmm_allocation',
			'title'      => __( 'Donation allocation', 'mmm' ),
			'desc'       => __( 'How a donation is split. These should add up to 100.', 'mmm' ),
			'repeatable' => __( 'Slice', 'mmm' ),
			'fields'     => array(
				'label' => array( 'label' => __( 'Label', 'mmm' ), 'type' => 'text' ),
				'value' => array( 'label' => __( 'Share (%)', 'mmm' ), 'type' => 'number' ),
			),
		),

		'means' => array(
			'option'     => 'mmm_means',
			'title'      => __( 'Our means and ethics', 'mmm' ),
			'desc'       => __( 'One statement per row.', 'mmm' ),
			'repeatable' => __( 'Statement', 'mmm' ),
			// The option is a flat list of strings, not a list of rows, so
			// each entry is lifted into its single field on the way in and
			// flattened again on the way out.
			'scalar'     => 'text',
			'fields'     => array(
				'text' => array( 'label' => __( 'Statement', 'mmm' ), 'type' => 'textarea', 'rows' => 3 ),
			),
		),

		'regions' => array(
			'option'     => 'mmm_regions',
			'title'      => __( 'Where we work', 'mmm' ),
			'repeatable' => __( 'Location', 'mmm' ),
			'fields'     => array(
				'name'   => array( 'label' => __( 'Name', 'mmm' ), 'type' => 'text' ),
				'detail' => array( 'label' => __( 'What happens there', 'mmm' ), 'type' => 'textarea', 'rows' => 3 ),
				'focus'  => array( 'label' => __( 'Focus', 'mmm' ), 'type' => 'text' ),
			),
		),

		'gallery_groups' => array(
			'option'     => 'mmm_gallery_groups',
			'title'      => __( 'Photo gallery filters', 'mmm' ),
			'desc'       => __( 'The tabs above the photo gallery. The key must match the group of the photographs it shows.', 'mmm' ),
			'repeatable' => __( 'Tab', 'mmm' ),
			'fields'     => array(
				'key'    => array( 'label' => __( 'Key', 'mmm' ), 'type' => 'text', 'hint' => __( 'field, disaster, medical, ration, gaza-food, gaza-water, gaza-winter', 'mmm' ) ),
				'label'  => array( 'label' => __( 'Tab label', 'mmm' ), 'type' => 'text' ),
				'region' => array( 'label' => __( 'Region', 'mmm' ), 'type' => 'text', 'hint' => __( 'Shown as the caption in the lightbox.', 'mmm' ) ),
			),
		),

		'nav' => array(
			'option'     => 'mmm_nav',
			'title'      => __( 'Main navigation', 'mmm' ),
			'desc'       => __( 'The top menu and its mega panels. Links beginning with / are relative to this site.', 'mmm' ),
			'repeatable' => __( 'Menu item', 'mmm' ),
			'fields'     => array(
				'label' => array( 'label' => __( 'Label', 'mmm' ), 'type' => 'text' ),
				'href'  => array( 'label' => __( 'Link', 'mmm' ), 'type' => 'text' ),
				'blurb' => array( 'label' => __( 'Panel intro', 'mmm' ), 'type' => 'textarea', 'rows' => 2 ),
			),
			'children'   => array(
				'columns' => array(
					'label'  => __( 'Column', 'mmm' ),
					'fields' => array(
						'heading' => array( 'label' => __( 'Heading', 'mmm' ), 'type' => 'text' ),
					),
					'children' => array(
						'links' => array(
							'label'  => __( 'Link', 'mmm' ),
							'fields' => array(
								'label' => array( 'label' => __( 'Label', 'mmm' ), 'type' => 'text' ),
								'href'  => array( 'label' => __( 'Link', 'mmm' ), 'type' => 'text' ),
								'note'  => array( 'label' => __( 'Note', 'mmm' ), 'type' => 'text' ),
							),
						),
					),
				),
				// One panel per menu item, not a list — hence `single`.
				'feature' => array(
					'single' => true,
					'label'  => __( 'Feature panel', 'mmm' ),
					'desc'   => __( 'The illustrated panel at the side of the mega menu. Leave the title empty to show none.', 'mmm' ),
					'fields' => array(
						'title' => array( 'label' => __( 'Title', 'mmm' ), 'type' => 'text' ),
						'body'  => array( 'label' => __( 'Text', 'mmm' ), 'type' => 'textarea', 'rows' => 3 ),
						'href'  => array( 'label' => __( 'Link', 'mmm' ), 'type' => 'text' ),
						'cta'   => array( 'label' => __( 'Link text', 'mmm' ), 'type' => 'text' ),
						'image' => array( 'label' => __( 'Image', 'mmm' ), 'type' => 'text', 'hint' => __( 'A /media/... path.', 'mmm' ) ),
					),
				),
			),
		),
	);
}

/**
 * Register the settings screen.
 */
function mmm_register_admin_menu() {
	add_options_page(
		__( 'MMM content', 'mmm' ),
		__( 'MMM content', 'mmm' ),
		'manage_options',
		'mmm-content',
		'mmm_render_admin_page'
	);
}
add_action( 'admin_menu', 'mmm_register_admin_menu' );

/**
 * Save handler.
 *
 * Runs before the screen renders, so a failed save still shows the form
 * with whatever is in the database.
 */
function mmm_admin_save() {
	if ( ! isset( $_POST['mmm_admin_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['mmm_admin_nonce'] ), 'mmm_save_content' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$posted = isset( $_POST['mmm'] ) && is_array( $_POST['mmm'] ) ? wp_unslash( $_POST['mmm'] ) : array();
	$schema = mmm_admin_schema();

	foreach ( $schema as $section => $spec ) {
		$option  = $spec['option'];
		$current = get_option( $option );
		$value   = isset( $posted[ $section ] )
			? mmm_sanitize_section( $posted[ $section ], $spec, $current )
			: null;

		if ( null === $value ) {
			continue;
		}

		// A repeatable list may have had rows removed; keep the array shape
		// tight and re-indexed so the front end can count on a list.
		if ( ! empty( $spec['repeatable'] ) ) {
			$value = array_values( array_filter( $value, static function ( $row ) {
				return is_array( $row ) && array_filter( $row, static fn( $v ) => '' !== $v );
			} ) );

			// A scalar list is stored flat, so unwrap it again.
			if ( ! empty( $spec['scalar'] ) ) {
				$flat  = $spec['scalar'];
				$value = array_map( static fn( $row ) => (string) ( $row[ $flat ] ?? '' ), $value );
			}
		}

		update_option( $option, $value );

		// mmm_org also carries flattened copies of the address and the bank
		// details, which is what the templates read. Keep those in step so
		// editing the address here actually changes the site.
		if ( 'address' === $section || 'bank' === $section ) {
			mmm_sync_org_flattened( 'address' === $section ? 'address' : 'bank', $value );
		}
	}

	add_settings_error(
		'mmm_content',
		'mmm_content_saved',
		__( 'Changes saved.', 'mmm' ),
		'updated'
	);
	set_transient( 'settings_errors', get_settings_errors(), 30 );
	wp_safe_redirect( add_query_arg( array( 'page' => 'mmm-content', 'updated' => 'true' ), admin_url( 'options-general.php' ) ) );
	exit;
}
add_action( 'admin_init', 'mmm_admin_save' );

/**
 * Mirror the address or bank option into its flattened copy inside mmm_org.
 *
 * The import wrote both, and the templates read mmm_org_value('address_line1')
 * / mmm_org_value('bank_iban'). Without this the settings screen would update
 * one copy and leave the site showing the other.
 *
 * @param string $which address|bank.
 * @param array  $value The nested option's value.
 */
function mmm_sync_org_flattened( $which, $value ) {
	$org = mmm_org();
	if ( ! $org || ! is_array( $value ) ) {
		return;
	}
	foreach ( $value as $key => $field ) {
		$org[ $which . '_' . $key ] = is_scalar( $field ) ? (string) $field : '';
	}
	update_option( 'mmm_org', $org );
}

/**
 * Clean one submitted section against its schema.
 *
 * Only the fields the screen actually displays are written. Anything else
 * already stored — the duplicated address and bank keys inside mmm_org, a
 * nav item's feature panel — is passed through untouched, so saving this
 * screen can never silently delete content it does not know about.
 *
 * @param array $value  Submitted section.
 * @param array $spec   Schema entry.
 * @param mixed $stored The option's current value.
 * @return array
 */
function mmm_sanitize_section( $value, $spec, $stored = null ) {
	$fields  = $spec['fields'];
	$children = $spec['children'] ?? null;

	if ( empty( $spec['repeatable'] ) ) {
		if ( ! is_array( $value ) ) {
			return is_array( $stored ) ? $stored : array();
		}
		return mmm_sanitize_row( $value, $fields, $children, is_array( $stored ) ? $stored : array() );
	}

	if ( ! is_array( $value ) ) {
		return array();
	}
	$stored = is_array( $stored ) ? array_values( $stored ) : array();
	$lift   = $spec['scalar'] ?? null;
	$rows   = array();
	foreach ( $value as $i => $row ) {
		if ( $lift && ! is_array( $row ) ) {
			$row = array( $lift => (string) $row );
		}
		if ( ! is_array( $row ) ) {
			continue;
		}
		$rows[] = mmm_sanitize_row( $row, $fields, $children, $stored[ $i ] ?? null );
	}
	return $rows;
}

/**
 * Clean one row of fields, including nested objects and repeatables.
 *
 * @param array      $row      Submitted fields.
 * @param array      $fields   Field definitions.
 * @param array|null $children Nested definitions.
 * @param mixed      $existing This row's stored value, for pass-through.
 * @return array<string,string|array>
 */
function mmm_sanitize_row( $row, $fields, $children = null, $existing = null ) {
	$clean = array();

	foreach ( $fields as $key => $field ) {
		if ( ! array_key_exists( $key, $row ) ) {
			continue;
		}
		$raw = (string) $row[ $key ];
		switch ( $field['type'] ) {
			case 'url':
				$clean[ $key ] = esc_url_raw( $raw );
				break;
			case 'textarea':
				$clean[ $key ] = sanitize_textarea_field( $raw );
				break;
			case 'int':
				$clean[ $key ] = intval( $raw );
				break;
			case 'number':
				if ( '' === trim( $raw ) ) {
					$clean[ $key ] = 0;
					break;
				}
				// Keep whole numbers as ints so a save does not rewrite every
				// value in the database from 88 to "88" for no reason.
				$number     = floatval( str_replace( ',', '', $raw ) );
				$clean[ $key ] = ( floor( $number ) === $number ) ? (int) $number : $number;
				break;
			case 'select':
				$choices     = $field['choices'] ?? array();
				$clean[ $key ] = array_key_exists( $raw, $choices ) ? $raw : ( $field['default'] ?? '' );
				break;
			default:
				$clean[ $key ] = sanitize_text_field( $raw );
		}
	}

	if ( is_array( $children ) ) {
		foreach ( $children as $key => $child ) {
			$stored_child = is_array( $existing ) && isset( $existing[ $key ] ) ? $existing[ $key ] : null;

			if ( ! empty( $child['single'] ) ) {
				// One object, e.g. a nav item's feature panel.
				if ( isset( $row[ $key ] ) && is_array( $row[ $key ] ) ) {
					$clean[ $key ] = mmm_sanitize_row(
						$row[ $key ],
						$child['fields'],
						$child['children'] ?? null,
						is_array( $stored_child ) ? $stored_child : null
					);
				} elseif ( is_array( $stored_child ) ) {
					$clean[ $key ] = $stored_child;
				}
				continue;
			}

			if ( ! isset( $row[ $key ] ) || ! is_array( $row[ $key ] ) ) {
				continue;
			}
			$stored_child = is_array( $stored_child ) ? array_values( $stored_child ) : array();
			$sub          = array();
			foreach ( $row[ $key ] as $i => $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}
				$sub[] = mmm_sanitize_row(
					$item,
				$child['fields'],
					$child['children'] ?? null,
					$stored_child[ $i ] ?? null
				);
			}
			$clean[ $key ] = array_values( array_filter( $sub, static function ( $r ) {
				return array_filter( $r, static fn( $v ) => is_array( $v ) ? $v : '' !== $v );
			} ) );
		}
	}

	// Anything stored under a key this screen does not render is left alone.
	if ( is_array( $existing ) ) {
		foreach ( $existing as $key => $value ) {
			if ( array_key_exists( $key, $fields ) ) {
				continue;
			}
			if ( is_array( $children ) && array_key_exists( $key, $children ) ) {
				continue;
			}
			$clean[ $key ] = $value;
		}
	}

	return $clean;
}

/* ------------------------------------------------------------------ *
 * Rendering
 * ------------------------------------------------------------------ */

/**
 * Build the option's current rows as an array, whatever its shape.
 *
 * @param array $spec Schema entry.
 * @return array<int,array>
 */
function mmm_admin_rows( $spec ) {
	$stored = get_option( $spec['option'], array() );

	if ( empty( $spec['repeatable'] ) ) {
		return array( is_array( $stored ) ? $stored : array() );
	}

	if ( ! is_array( $stored ) ) {
		return array();
	}
	$rows = array_values( $stored );

	// A scalar list stores plain values; the form needs a row per value.
	if ( ! empty( $spec['scalar'] ) ) {
		$lift  = $spec['scalar'];
		$rows  = array_map( static fn( $row ) => is_array( $row ) ? $row : array( $lift => (string) $row ), $rows );
	}

	return $rows;
}

/**
 * Print the screen.
 */
function mmm_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to edit this content.', 'mmm' ) );
	}
	$schema = mmm_admin_schema();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'MMM content', 'mmm' ); ?></h1>

		<p class="description" style="max-width:62ch">
			<?php esc_html_e( 'These settings control the organisation\'s own details and the repeating lists that appear across the site. The layout itself is fixed in the theme, so nothing here can change how the site looks.', 'mmm' ); ?>
		</p>

		<nav style="margin:16px 0 22px;display:flex;flex-wrap:wrap;gap:6px">
			<?php foreach ( $schema as $id => $spec ) : ?>
				<a class="button button-secondary" href="#mmm-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $spec['title'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<?php settings_errors( 'mmm_content' ); ?>

		<form method="post" id="mmm-content-form">
			<?php wp_nonce_field( 'mmm_save_content', 'mmm_admin_nonce' ); ?>

			<?php foreach ( $schema as $id => $spec ) : ?>
				<section id="mmm-<?php echo esc_attr( $id ); ?>"
					style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:18px 20px;margin:0 0 22px;max-width:900px">
					<h2 style="margin:0 0 2px;font-size:1.15em"><?php echo esc_html( $spec['title'] ); ?></h2>
					<?php if ( ! empty( $spec['desc'] ) ) : ?>
						<p class="description" style="margin:0 0 14px"><?php echo esc_html( $spec['desc'] ); ?></p>
					<?php endif; ?>

					<?php
					$rows = mmm_admin_rows( $spec );
					if ( empty( $rows ) ) {
						$rows = array( array() );
					}
					$repeatable = ! empty( $spec['repeatable'] );
					?>
					<div data-rows>
						<?php foreach ( $rows as $i => $row ) : ?>
							<fieldset data-row
								style="border:1px solid #dcdcde;border-radius:4px;padding:12px 14px;margin:0 0 12px;<?php echo $repeatable ? '' : 'border:0;padding:0;'; ?>">
								<?php if ( $repeatable ) : ?>
									<legend style="padding:0 6px;font-weight:600">
										<?php echo esc_html( $spec['repeatable'] ); ?> <?php echo (int) $i + 1; ?>
									</legend>
								<?php endif; ?>

								<?php mmm_admin_print_fields( $row, $spec['fields'], "mmm[$id][$i]", $spec['children'] ?? null ); ?>

								<?php if ( $repeatable ) : ?>
									<p style="margin:6px 0 0">
										<button type="button" class="button button-link-delete" data-remove>
											<?php esc_html_e( 'Remove', 'mmm' ); ?>
										</button>
									</p>
								<?php endif; ?>
							</fieldset>
						<?php endforeach; ?>
					</div>

					<?php if ( $repeatable ) : ?>
						<p>
							<button type="button" class="button" data-add
								data-row-label="<?php echo esc_attr( $spec['repeatable'] ); ?>">
								<?php
								/* translators: %s: singular name of the row, e.g. "Statistic". */
								printf( esc_html__( 'Add %s', 'mmm' ), esc_html( strtolower( $spec['repeatable'] ) ) );
								?>
							</button>
						</p>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>

			<?php submit_button( __( 'Save changes', 'mmm' ) ); ?>
		</form>
	</div>

	<script>
	( function () {
		var form = document.getElementById( 'mmm-content-form' );
		if ( ! form ) return;

		form.addEventListener( 'click', function ( event ) {
			var add = event.target.closest( '[data-add]' );
			if ( add ) {
				event.preventDefault();
				var section = add.closest( 'section' );
				var holder = section.querySelector( '[data-rows]' );
				var rows = holder.querySelectorAll( '[data-row]' );
				var last = rows[ rows.length - 1 ];
				if ( ! last ) return;

				var next = rows.length;
				var clone = last.cloneNode( true );

				// Renumber every field so nothing is overwritten.
				var fields = clone.querySelectorAll( 'input, select, textarea' );
				for ( var i = 0; i < fields.length; i++ ) {
					var el = fields[i];
					el.name = el.name.replace( /\[\d+\]/, '[' + next + ']' );
					if ( el.tagName === 'SELECT' ) el.selectedIndex = 0;
					else if ( el.type === 'checkbox' || el.type === 'radio' ) el.checked = false;
					else el.value = '';
				}

				var legend = clone.querySelector( 'legend' );
				if ( legend ) legend.textContent = legend.textContent.replace( /\d+$/, String( next + 1 ) );

				holder.appendChild( clone );
				var first = clone.querySelector( 'input, select, textarea' );
				if ( first ) first.focus();
				return;
			}

			var remove = event.target.closest( '[data-remove]' );
			if ( remove ) {
				event.preventDefault();
				var box = remove.closest( '[data-row]' );
				var holder2 = box && box.parentNode;
				if ( ! holder2 ) return;

				// Never leave the section empty — a blank row beats a missing key.
				if ( holder2.querySelectorAll( '[data-row]' ).length > 1 ) {
					box.remove();
				} else {
					var inputs = box.querySelectorAll( 'input, select, textarea' );
					for ( var j = 0; j < inputs.length; j++ ) {
						var f = inputs[j];
						if ( f.tagName === 'SELECT' ) f.selectedIndex = 0;
						else f.value = '';
					}
				}
			}
		} );
	}() );
	</script>
	<?php
}

/**
 * Print one row of fields.
 *
 * @param array      $row       Current values.
 * @param array      $fields    Field definitions.
 * @param string     $base      Input name prefix, e.g. mmm[org][0].
 * @param array|null $children  Nested repeatable definitions.
 */
function mmm_admin_print_fields( $row, $fields, $base, $children = null ) {
	foreach ( $fields as $key => $field ) {
		// The braces are required: "$base[$key]" would be read as an array
		// access on the $base string rather than as literal brackets.
		$value = $row[ $key ] ?? '';
		$name  = "{$base}[{$key}]";
		$id    = str_replace( array( '[', ']' ), array( '_', '' ), $name );
		?>
		<p style="margin:0 0 10px">
			<label for="<?php echo esc_attr( $id ); ?>" style="display:block;font-weight:600;margin-bottom:3px">
				<?php echo esc_html( $field['label'] ); ?>
			</label>

			<?php if ( 'textarea' === $field['type'] ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					rows="<?php echo (int) ( $field['rows'] ?? 3 ); ?>"
					style="width:100%"><?php echo esc_textarea( (string) $value ); ?></textarea>

			<?php elseif ( 'select' === $field['type'] ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
					<?php foreach ( $field['choices'] as $choice => $label ) : ?>
						<option value="<?php echo esc_attr( $choice ); ?>" <?php selected( (string) $value, (string) $choice ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>

			<?php else : ?>
				<input id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					type="<?php echo esc_attr( $field['type'] ); ?>"
					value="<?php echo esc_attr( (string) $value ); ?>"
					style="width:100%<?php echo in_array( $field['type'], array( 'number', 'int' ), true ) ? ';max-width:160px' : ''; ?>" />

			<?php endif; ?>

			<?php if ( ! empty( $field['hint'] ) ) : ?>
				<span class="description" style="display:block;margin-top:2px">
					<?php echo esc_html( $field['hint'] ); ?>
				</span>
			<?php endif; ?>
		</p>
		<?php
	}

	if ( empty( $children ) ) {
		return;
	}

	foreach ( $children as $key => $child ) {
		// A single child is one fixed object — no add or remove controls.
		if ( ! empty( $child['single'] ) ) {
			$object = isset( $row[ $key ] ) && is_array( $row[ $key ] ) ? $row[ $key ] : array();
			?>
			<fieldset style="border:1px solid #dcdcde;border-radius:4px;padding:10px 12px;margin:10px 0 0;background:#fff">
				<legend style="padding:0 6px;font-weight:600">
					<?php echo esc_html( $child['label'] ); ?>
				</legend>

				<?php if ( ! empty( $child['desc'] ) ) : ?>
					<p class="description" style="margin:0 0 8px"><?php echo esc_html( $child['desc'] ); ?></p>
				<?php endif; ?>

				<?php mmm_admin_print_fields( $object, $child['fields'], "{$base}[{$key}]", $child['children'] ?? null ); ?>
			</fieldset>
			<?php
			continue;
		}
		?>
		<div style="margin:4px 0 0;padding:10px 12px;background:#f6f7f7;border-radius:4px">
			<?php
			$columns = isset( $row[ $key ] ) && is_array( $row[ $key ] ) ? array_values( $row[ $key ] ) : array();
			foreach ( $columns as $ci => $column ) {
				?>
				<fieldset style="border:1px solid #dcdcde;border-radius:4px;padding:10px 12px;margin:0 0 10px;background:#fff">
					<legend style="padding:0 6px;font-weight:600">
						<?php echo esc_html( $child['label'] ); ?> <?php echo (int) $ci + 1; ?>
					</legend>

					<?php mmm_admin_print_fields( $column, $child['fields'], "{$base}[{$key}][{$ci}]", $child['children'] ?? null ); ?>

					<p style="margin:0">
						<button type="button" class="button button-link-delete" data-remove>
							<?php esc_html_e( 'Remove', 'mmm' ); ?>
						</button>
					</p>
				</fieldset>
				<?php
			}
			?>
			<p style="margin:0">
				<button type="button" class="button" data-add>
					<?php
					/* translators: %s: singular name of the row, e.g. "Column". */
					printf( esc_html__( 'Add %s', 'mmm' ), esc_html( strtolower( $child['label'] ) ) );
					?>
				</button>
			</p>
		</div>
		<?php
	}
}
