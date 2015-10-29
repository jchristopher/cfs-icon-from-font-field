# CFS Icon From Font Field

Add-on field for Custom Field Suite that allows you to implement a trigger for a modal that facilitates choosing an icon from any registered icon font.

### Registering icon fonts

CFSIFFF requires that you 'register' your icon fonts with the field in order to work. You can do so like so:

```php
function my_cfsifff_fonts( $fonts, $field ) {

	// prep the filesystem
	global $wp_filesystem;
	include_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();

	$base   = str_replace( site_url(), '', get_stylesheet_directory_uri() );
	$dir    = '/assets/fonts/myfont/';

    $fonts['myiconfont'] = array(

        // you can use your IcoMoon export directly to automatically provide the character set
        'defs'      => json_decode( $wp_filesystem->get_contents( get_stylesheet_directory() . $dir . 'selection.json' ) ),

        // the label of this font (will show up on the left of the modal)
        'label'     => 'My Icon Font',

        // you need to declare the font family and the location of the font files
        'font-face' => array(
            'family'                        => 'myiconfont',
            'files' => array(
                'embedded-opentype'         => $base . $dir . 'fonts/myiconfont.eot',
                'truetype'                  => $base . $dir . 'fonts/myiconfont.ttf',
                'woff'                      => $base . $dir . 'fonts/myiconfont.woff',
                'svg'                       => $base . $dir . 'fonts/myiconfont.svg',
            ),
            'styles' => array(
                'font-weight'               => 'normal',
                'font-style'                => 'normal',
            ),
        ),

    );

	return $fonts;
}

add_filter( 'cfsifff_fonts', 'my_cfsifff_fonts', 10, 2 );
```

#### All options

```php
function my_cfsifff_fonts_full_example( $fonts, $field ) {

	$base   = str_replace( site_url(), '', get_stylesheet_directory_uri() );
	$dir    = '/assets/fonts/myfont/';

	$fonts['myfont'] = array(

		// the label of this font (will show up on the left of the modal)
		'label'     => 'My Icon Font',

		// you need to declare the font family and the location of the font files
		'font-face' => array(
			'family'                        => 'myfont',
			'files' => array(
				'embedded-opentype'         => $base . $dir . 'fonts/myfont.eot',
				'truetype'                  => $base . $dir . 'fonts/myfont.ttf',
				'woff'                      => $base . $dir . 'fonts/myfont.woff',
				'svg'                       => $base . $dir . 'fonts/myfont.svg',
			),
			'styles' => array(
				'font-weight'               => 'normal',
				'font-style'                => 'normal',
			),
		),

		// you need to define which HTML element to use for your font, along with styles
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

		// if you did not provide an IcoMoon export file to use, or you want to
		// limit the available icons to display in the modal, define the chars here
		'characters' => array(
			'search'                        => 's',
			'facebook'                      => 'f',
			'googleplus'                    => '+',
			'linkedin'                      => 'i',
			'twitter'                       => 't',
			'youtube'                       => 'y',
			'arrow-right'                   => 'a',
			'caret'                         => 'c',
			'grid'                          => 'g',
		),
	);

	return $fonts;
}

add_filter( 'cfsifff_fonts', 'my_cfsifff_fonts_full_example', 10, 2 );
```