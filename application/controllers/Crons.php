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
        $this->db->insert('bs_cron_log',['cron_name' => 'remove-item-from-cart', 'created_at' => date('Y-m-d H:i:s')]);
        echo 'cron run successfully';
    }
    
    public function update_order_status() {
        $track_order = $this->db->from('bs_track_order')->where('status','!=', 'DELIVERED')->get()->result_array();
        if(count($track_order)) {
            foreach ($track_order as $key => $value) {
                $headers = array(
                "Content-Type: application/json",
                "Authorization: ShippoToken ".SHIPPO_AUTH_TOKEN  // place your shippo private token here
                                  );

                $url = 'https://api.goshippo.com/tracks/shippo/'.$value['tracking_number'];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                $response = curl_exec($ch); 
                
                $res_array = json_decode($response,true);
                        
                $this->db->where('id', $value['id'])->update('bs_track_order',['status' => $res_array['tracking_status']['status']]);
                
                if($res_array['tracking_status']['status'] == 'DELIVERED') {
                    $this->db->where('order_id', $value['order_id'])->update('bs_order',['delivery_status' => "delivered", 'completed_date' => date('Y-m-d H:i:s')]);
                }
            }
        }
        $this->db->insert('bs_cron_log',['cron_name' => 'track-order', 'created_at' => date('Y-m-d H:i:s')]);
        echo 'cron run successfully';
    }
    
    public function expire_offer() {
        $past_record = $this->db->select('id, added_date, updated_date, timezone')->from('bs_chat_history')
                ->where('is_cancel', 0)
                ->where('is_offer_complete', 0)
                ->order_by('added_date', 'desc')->get()->result_array();
//        dd($past_record);
        foreach ($past_record as $key => $value) {
            if(empty($value['updated_date'])) {
                $date1 = date_create($value['added_date']);
            } else {
                $date1 = date_create($value['updated_date']);
            }
            $date = new DateTime("now", new DateTimeZone($value['timezone']) );
            $date2 = date_create($date->format('Y-m-d H:i:s'));
            
            $diff = date_diff($date1, $date2)->format("%a||%h||%i"); 
            $split_diff = explode('||',$diff);
            $days = $split_diff[0];
//            $hours = $split_diff[1];
//            $mins = $split_diff[2];
//            dd($diff);
            if($days > 0) {
//                if($hours == 0) {
//                    if($mins > 5 && $mins < 10) {
//                        echo $value['id'].'<br>';
//                    }
//                }
//                echo 'expired offer :- '.$value['id']. '<br>';
                $this->db->where('id', $value['id'])->update('bs_chat_history',['is_expired' => 1, 'updated_date' => $date->format('Y-m-d H:i:s')]);
            }
        }
        $this->db->insert('bs_cron_log',['cron_name' => 'expire-offer', 'created_at' => date('Y-m-d H:i:s')]);
        echo 'cron run successfully';
    }
}