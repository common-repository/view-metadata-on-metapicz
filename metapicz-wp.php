<?php
/*
Plugin Name: Metapicz
Plugin URI: http://metapicz.com
Description: Allow your viewers to see the metadata (exif, xml, gps, copyright etc.) of the pictures in your posts.
Author: Marco Rucci
Version: 0.1
Author URI: http://securo.it
*/
/** This plugin is heavily based on the cool "display exif" plugin by V.J.Catkick */

$global_exif_datas;

function metapicz_js( $arg ) {
	$js_source =  '
	<!-- JavaScript for Display Exif -->
	<script type="text/javascript">
//<![CDATA[
	function _ie8_anti_NaN( number_to_check ) {
		var r = 0;
		if( isFinite( number_to_check ) ) r = number_to_check;
		return( r );
	} /* _ie8_anti_NaN */

	jQuery( function(){

		var $each_imgs = jQuery( \'img\' );

		$each_imgs.bind( \'mouseenter\', function() {
			$this_img = jQuery( this );
			//console.log([\'Mouse enter: \', $this_img]);
			if( $this_img.attr( \'metapicz\' ) && $this_img.attr( \'metapicz\' ).length > 0 ) {
				var $metapicz_id = $this_img.attr( \'metapicz\' );
				$this_metapicz = jQuery( $metapicz_id );
				//console.log($this_metapicz);

				var $img_pos = $this_img.position();
				var $oft_w = ( _ie8_anti_NaN( parseInt( $this_img.css( "marginRight" ) ) ) - _ie8_anti_NaN( parseInt( $this_img.css( "marginLeft" ) ) ) ) / 2;
				var $oft_h = ( _ie8_anti_NaN( parseInt( $this_img.css( "marginBottom" ) ) ) - _ie8_anti_NaN( parseInt( $this_img.css( "marginTop" ) ) ) ) / 2;

				$t_pos = $img_pos.top + ( $this_img.outerHeight( true ) - $this_img.height() ) / 2 - $oft_h;
				$l_pos = $img_pos.left + ( $this_img.outerWidth( true ) - $this_img.width() ) / 2 - $oft_w;
				$e_width = $this_img.width();

				$t_pos -= 52;

				$this_metapicz.css({
					position: "absolute",
					top: $t_pos,
					left: $l_pos,
					//width: $e_width - 10
				});

				$most_top = $this_img.parent();
				if( !$most_top.css( "position" ) ) { $most_top.css( { position: "relative" } ); }

				$this_metapicz.show();

				/* suppress flicker when mouse on the box */
				$this_metapicz.bind( \'mouseover\', function() {
					$this_metapicz.show();
				}).bind( \'mouseleave\', function() {
					$this_metapicz.hide();
				});
				//console.log(\'done\');
			}
		}).bind( \'mouseleave\', function() {
				$this_metapicz.hide();
				jQuery( \'.metapicz_box\' ).remove();
		});

	});
//]]>
	</script><!-- end Javascript for Display Exif -->
	<style type="text/css" >
.metapicz_hidden {
	background-color: black;
	/*
	filter: alpha(opacity=60);
	-moz-opacity: .60;
	opacity: .60;
	*/
	padding: 10px;
	border-top-right-radius: 10px;
	border-top-left-radius: 10px;
}
.metapicz_raw {
	margin: 0px;
	padding: 0px;
	position: relative;
	font-family: sans;
	font-size: 14px;
	line-height: 1.2em;
	clear: both;
	text-align: right;
}
.metapicz_raw a {
	color: white;
	text-decoration: none;
}
.metapicz_title {
	float: left;
	width: 100px;
}
.metapicz_desc {
}
	</style>
	';
	echo $js_source;
} /* metapicz_js() */

function metapicz_replace_cb( $matches ) {
	global $global_exif_datas;
	$output = $matches[0];
	$attrs_org = $matches[ 1 ];
	$dp_str = 'METAPICZ_' . rand( 10000, 99999 );

	$filename = '';
	if( preg_match( '/src="(.+?)"/s', $attrs_org, $f ) && !preg_match( '/^"/s', $f[ 1 ] ) ) {
		$filename = $f[ 1 ];
		$global_exif_datas[ $dp_str ] = $filename;
		$output = '<img ' . $attrs_org . ' metapicz=".' . $dp_str . '" />';
	}

	return( $output );
} /* metapicz_replace_cb() */

function metapicz_filter( $arg ) {
	$output = $arg;

	global $global_exif_datas;
	if( !empty( $global_exif_datas ) ) $global_exif_datas = '';	// init

	$output = preg_replace_callback( '/<img(.+?)\/>/s', 'metapicz_replace_cb', $output );
	if( empty( $global_exif_datas ) )
		return( $output );

	foreach( $global_exif_datas as $ged_key => $filename ) {
		$metapicz_url = 'http://metapicz.com/#landing?imgsrc=' . urlencode($filename);
		$output .= '<div class="metapicz_hidden ' . $ged_key . '" style="display: none;" >'
		. '<div class="metapicz_raw">'
		. '<a href="' . $metapicz_url . '" target="_new">View Metadata<br>on <b>Metapicz</b></a>'
		. '</div>'
		. '</div>';
	}
	return( $output );
} /* metapicz_filter() */

function metapicz_init_jquery() {
	if( !is_admin() ) {
		wp_enqueue_script( 'jquery' );
	}
} /* metapicz_init_jquery() */
 
add_action( 'init', 'metapicz_init_jquery' );
add_action( 'wp_head', 'metapicz_js' );
add_filter( 'the_content', 'metapicz_filter', 50 );

?>
