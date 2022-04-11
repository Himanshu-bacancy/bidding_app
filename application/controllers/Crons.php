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
        $date = date('Y-m-d H:i:s');
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
                
                if($res_array['tracking_status']['status'] == 'TRANSIT' && $value['status'] == 'PRE_TRANSIT') {
                    $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                                ->join('bs_order', 'bs_order.items = bs_items.id')
                                ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                                ->where('bs_order.order_id', $value['order_id'])->get()->row_array();
                    
                    if(!empty($seller)) {
                        send_push( [$seller['device_token']], ["message" => "Order ship by buyer", "flag" => "order"],['order_id' => $value['order_id']] );
                    }
                    
                    $this->db->where('id', $value['id'])->update('bs_order',['return_shipment_initiate_date' => $date]);
                }
                if($res_array['tracking_status']['status'] == 'DELIVERED') {
                    $update_order['delivery_status'] = "delivered";
                    if($value['is_return']) {
                        $buyer_detail = $this->db->select('core_users.device_token')->from('bs_order')
                            ->join('core_users', 'bs_order.user_id = core_users.user_id')
                            ->where('order_id', $value['order_id'])->get()->row_array();
                        
                        if(!empty($buyer_detail)) {
                            send_push( [$buyer_detail['device_token']], ["message" => "Order received by seller", "flag" => "order"],['order_id' => $value['order_id']] );
                        }
                        $update_order['return_shipment_delivered_date'] = $date;
                        $update_order['seller_dispute_expiry_date'] = date('Y-m-d H:i:s', strtotime($date. ' + 1 days'));
                    }
                    $this->db->where('order_id', $value['order_id'])->update('bs_order',$update_order);
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
    
    public function read_xcel() {
        $file = 'D:\xampp\htdocs\short.xlsx';
        
        $this->load->library('excel');
        
        try {
            //read file from path
            $objPHPExcel = PHPExcel_IOFactory::load($file);
        } catch(Exception $e) {
            echo $e->getMessage();
        }
//        get sheet count
//        $count = $objPHPExcel->getSheetCount();
//        dd($count);
       
//        get sheet names
//        $sheets = $objPHPExcel->getSheetNames();
//        dd($sheets);
//        
//        get sheet by name
        $custom_sheet = $objPHPExcel->getSheetByName("X");
        $cell_collection = $custom_sheet->getCellCollection();
//        dd($cell_collection);
        //get only the Cell Collection
//        $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
        
        $current_date = date('Y-m-d H:i:s');
        //extract to a PHP readable array format
        foreach ($cell_collection as $cell) {
            $column = $custom_sheet->getCell($cell)->getColumn();
            $row = $custom_sheet->getCell($cell)->getRow();
            $data_value = $custom_sheet->getCell($cell)->getValue();

            //The header will/should be in row 1 only. of course, this can be modified to suit your need.
            if ($row == 1) {
                $header[$row][$column] = $data_value;
            } else {
                $arr_data[$row]['name'] = $data_value;
                $arr_data[$row]['status'] = 1;
                $arr_data[$row]['added_date'] = $current_date;
            }
        }
        
        //send the data in an array format
        $data['header'] = $header;
        $data['values'] = array_values($arr_data);
//        dd($data);
        $this->db->insert_batch('bs_brand',$data['values']);
    }
}