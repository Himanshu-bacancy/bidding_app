<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * blogs Controller
 */
class Crons extends CI_Controller {

    public function remove_item_from_cart() {
        $past_record = $this->db->select('id')->from('bs_cart')->where('DATE(created_date) < DATE(now())')->get()->result_array();
        $past_record_ids = array_column($past_record, 'id');
        $this->db->where_in('id', $past_record_ids)->delete('bs_cart');
        echo 'cron run successfully';
    }
}