<?php

function autonettv_relay_api_footer( $footer ) {

  $new_footer = str_replace( '.</span>', __(' and <a href="https://autonettv.com">AutoNetTV Media, Inc.</a></span>', 'autonettv-relay-api' ), $footer);
  return $new_footer;

}
add_filter( 'admin_footer_text', 'autonettv_relay_api_footer', 10, 1 );
