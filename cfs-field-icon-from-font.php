<?php

class cfs_icon_from_font_field extends cfs_field {

	private $fonts = array();

	function __construct() {
		$this->name  = 'icon_from_font_field';
		$this->label = __( 'Icon (from Font)', 'cfsifff' );

		$this->fonts = apply_filters( 'cfsifff_fonts', $this->fonts, $this );

		if ( ! empty( $this->fonts ) ) {
			foreach ( $this->fonts as $key => $font ) {
				$this->fonts[ $key ] = $this->apply_font_defaults( $font );
			}
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );

		add_action( 'admin_footer-post-new.php', array( $this, 'add_template' ) );
		add_action( 'admin_footer-post.php', array( $this, 'add_template' ) );
	}

	function apply_font_defaults( $args ) {
		$defaults = array(
		'label'     => 'My Icon Font',
		'font-face' => array(),
		'tag' => array(
			'element'   => 'i',
			'styles'    => array(
				'speak'                     => 'none',
				'font-style'                => 'normal',
				'font-weight'               => 'normal',
				'font-variant'              => 'normal',
				'text-transform'            => 'none',
				'line-height'               => '1',
				'-webkit-font-smoothing'    => 'antialiased',
				'-mos-osx-font-smoothing'   => 'grayscale',
			),
		),
	);

		return wp_parse_args( $args, $defaults );
	}

	function html( $field ) {

		$icon_info = $field->value;

		if ( ! empty( $icon_info ) ) {
			$icon_info = explode( '%|%', $icon_info );
		}

		// CSS logic for "Add" / "Remove" buttons
		$css = empty( $icon_info ) ? array( '', ' hidden' ) : array( ' hidden', '' );

		if ( empty( $this->fonts ) ) { ?>
			<p>Please use the <code>cfsifff_fonts</code> hook to register at least one icon font.</p>
			<?php
			return;
		}

		?>
			<div class="cfsifff-wrapper">
		        <div class="<?php if ( empty ( $icon_info[0] ) ) : ?>hidden <?php endif; ?>cfsifff-chooser cfsifff-preview<?php if ( ! empty ( $icon_info[0] ) ) : ?> cfsifff-chooser<?php echo esc_attr( $icon_info[0] ); ?><?php endif; ?>"><div class="cfsifff-chooser-helper"><i><?php echo ! empty ( $icon_info[1] ) ? esc_html( $icon_info[1] ) : ''; ?></i></div></div>
		        <input type="button" id="open-backbone_modal" class="cfsifff-button button add<?php echo $css[0]; ?>" value="<?php _e( apply_filters( 'cfsifff_choose_button_label', 'Choose Icon', $this ), 'cfsifff' ); ?>" />
		        <input type="button" class="cfsifff-button button remove<?php echo $css[1]; ?>" value="<?php _e( apply_filters( 'cfsifff_remove_button_label', 'Remove Icon', $this ), 'cfsifff' ); ?>" />
		        <input type="hidden" name="<?php echo $field->input_name; ?>" class="file_value" value="<?php echo $field->value; ?>" />
			</div>
	    <?php

	}

	function input_head( $field = null ) {
		?>
		<script>
		    (function($) {
		        $(function() {
                    $(document).on('click', '.cfs_input .cfsifff-button.button.remove', function() {
	                    $(this).siblings('.file_value').val('');
	                    $(this).siblings('.cfsifff-button.button.add').show();
	                    $(this).siblings('.cfsifff-preview').hide();
	                    $(this).hide();
	                });
	                $( document ).on( 'cfsifffChosen', function( event, arg1, arg2 ) {
	                    var $this = jQuery('.cfsifff-context');
                        $this.siblings('.file_value').val(arg1 + '%|%' + arg2);
                        $this.siblings('.cfsifff-preview').removeClass().addClass('cfsifff-chooser cfsifff-preview  cfsifff-chooser'+arg1).show().find('.cfsifff-chooser-helper>*:eq(0)').text(arg2);
                        $this.siblings('.cfsifff-button.button.remove').show();
                        $this.removeClass('cfsifff-context').hide();
					});
		        });
		    })(jQuery);
        </script>
		<?php
	}

	function build_character_set( $font ) {

		// config can force a character set
		if ( isset( $font['characters'] ) && ! empty( $font['characters'] ) ) {
			return $font['characters'];
		}

		// we're working from an Icomoon selection
		$chars = array();

		// make sure there is a definition set to work from
		if ( ! isset( $font['defs'] ) ) {
			return $chars;
		}

		// make sure the definition set has icons
		if ( empty( $font['defs']->icons ) ) {
			return $chars;
		}

		// convert the Icomoon definitions back into characters we can use
		foreach ( $font['defs']->icons as $icon ) {
			if ( isset( $icon->properties ) && isset ( $icon->properties->code ) && isset ( $icon->properties->name ) ) {
				$chars[ $icon->properties->name ] = chr( absint( $icon->properties->code ) );
			}
		}

		return $chars;
	}

	function render_font_sets() {
		ob_start();

		if ( empty( $this->fonts ) ) { ?>
			<p>No icon fonts available</p>
			<?php
		}

		?><?php foreach ( $this->fonts as $font_family => $font ) : ?>
			<div class="cfsifff-chooser cfsifff-chooser-family cfsifff-chooser<?php echo md5( $font_family ); ?>">
				<?php $chars = $this->build_character_set( $font ); foreach ( $chars as $icon ) : ?>
					<div class="cfsifff-chooser-helper cfsifff-chooser-trigger" data-font="<?php echo esc_attr( md5( $font_family ) ); ?>" data-char="<?php echo esc_attr( $icon ); ?>">
						<<?php echo $font['tag']['element']; ?>><?php echo esc_html( $icon ); ?></<?php echo $font['tag']['element']; ?>>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach;

		$out = ob_get_clean();

		return $out;
	}

	function add_scripts() {
		$base = plugin_dir_url( __FILE__ );

		wp_enqueue_script( 'backbone_modal', $base . 'js/modal.js', array(
			'jquery',
			'backbone',
			'underscore',
			'wp-util'
		) );

		wp_localize_script( 'backbone_modal', 'cfsifff_backbone_modal_l10n',
			array(
				'icon_set_nav' => $this->render_font_nav(),
				'icon_sheets' => $this->render_font_sets(),
			) );

		wp_enqueue_style( 'backbone_modal', $base . 'css/modal.css' );

	}

	function render_font_nav() {

		ob_start();

		if ( ! empty( $this->fonts ) ) : ?>
			<?php foreach ( $this->fonts as $font_family => $font ) : ?>
				<li class="nav-item">
					<a href="#" data-cfsifff-font="<?php echo esc_attr( md5( $font_family ) ); ?>">
						<?php echo isset( $font['label'] ) ? esc_html( $font['label'] ) : esc_html( $font_family ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		<?php endif;

		$out = ob_get_clean();

		return $out;
	}

	public function add_template() {
		include_once 'template-choose-icon-from-font.php';

		$cache_buster = uniqid();

		// inject @font-face for all registered fonts
		if ( ! empty( $this->fonts ) ) : ?>
			<style type="text/css">
				<?php foreach ( $this->fonts as $font_family => $font ) : ?>
					@font-face {
						<?php if ( ! empty( $font['font-face']['family'] ) ) : ?>
							font-family: '<?php echo esc_attr( $font['font-face']['family'] ); ?>';
						<?php endif; ?>
						<?php if ( ! empty( $font['font-face']['files'] ) ) : ?>
							<?php foreach ( $font['font-face']['files'] as $format => $file ) : ?>
								src:url(<?php echo esc_url( $file . '?' . $cache_buster ); ?>);
							<?php break; endforeach; ?>
							<?php if ( 1 < $count = count( $font['font-face']['files'] ) ) : $i = 1; ?>
								src:
								<?php foreach ( $font['font-face']['files'] as $format => $file ) : ?>
									url(<?php echo esc_url( $file . '?' . $cache_buster ); ?>) format("<?php echo esc_attr( $format ); ?>")<?php if ( $i < $count ) : ?>,<?php endif; ?>
								<?php $i++; endforeach; ?>;
							<?php endif; ?>
						<?php endif; ?>
						<?php if ( ! empty( $font['font-face']['styles'] ) ) : ?>
							<?php foreach ( $font['font-face']['styles'] as $attribute => $value ) : ?>
								<?php echo esc_attr( $attribute ); ?>: <?php echo esc_attr( $value ); ?>;
							<?php endforeach; ?>
						<?php endif; ?>
					}

					.cfsifff-chooser.cfsifff-chooser<?php echo md5( $font_family ); ?> <?php echo esc_attr( $font['tag']['element'] ); ?> {
						font-family: '<?php echo esc_attr( $font['font-face']['family'] ); ?>';
						<?php if ( ! empty( $font['tag']['styles'] ) ) : ?>
							<?php foreach ( $font['tag']['styles'] as $attribute => $value ) : ?>
								<?php echo esc_attr( $attribute ); ?>: <?php echo esc_attr( $value ); ?>;
							<?php endforeach; ?>
						<?php endif; ?>
					}
				<?php endforeach; ?>
			</style>
		<?php endif;
	}

	function format_value_for_api( $value, $field = null ) {

		// stored value is an md5 hash of the array key of $this->fonts so let's extract it

		$icon_info = explode( '%|%', $value );

		if ( empty( $this->fonts ) || ! isset( $icon_info[0] ) || ! isset ( $icon_info[1] ) ) {
			return false;
		}

		foreach ( $this->fonts as $font_family => $font ) {
			if ( $icon_info[0] == md5( $font_family ) ) {
				$output = array(
					'font'  => $font_family,
					'ref'   => $font,
					'char'  => $icon_info[1]
				);
				break;
			}
		}

		return $output;

	}
}