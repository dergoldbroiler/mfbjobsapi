<?php


function footag_func( $atts ) {
	return "foo = {$atts['foo']}";
}
add_shortcode( 'footag', 'footag_func' );