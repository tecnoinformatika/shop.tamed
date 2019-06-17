<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsAuthController' ) ) :


  class OsAuthController extends OsController {

    function __construct(){
      parent::__construct();
      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'auth/';
    }

  }
endif;