<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsTransactionsController' ) ) :


  class OsTransactionsController extends OsController {

    function __construct(){
      parent::__construct();
      
      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'transactions/';
      $this->vars['page_header'] = __('Transactions', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Transactions', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('transactions', 'index') ) );
    }


    /*
      Index of transactions
    */

    public function index(){

      $per_page = 15;
      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;

      $transactions = new OsTransactionModel();
      $count_transactions = new OsTransactionModel();

      if($this->logged_in_agent_id){
        $transactions = $transactions->build_query_transactions_for_agent($this->logged_in_agent_id);
        $total_transactions = $count_transactions->count_transactions_for_agent($this->logged_in_agent_id);
      }else{
        $total_transactions = $count_transactions->count();
      }

      $transactions = $transactions->order_by('created_at desc')->set_limit($per_page);
      if($page_number > 1){
        $transactions = $transactions->set_offset(($page_number - 1) * $per_page);
      }

      

      
      $this->vars['transactions'] = $transactions->get_results_as_models();
      $this->vars['total_transactions'] = $total_transactions;
      $this->vars['per_page'] = $per_page;
      $this->vars['current_page_number'] = $page_number;
      
      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $total_transactions);

      $this->format_render(__FUNCTION__);
    }





  }


endif;