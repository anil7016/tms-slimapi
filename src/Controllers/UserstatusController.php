<?php

namespace App\Controllers;

use App\Models\dbHelper;

class UserstatusController {

    public function __construct() {
        $this->_db = dbHelper::getInstance();
    }
    // public function __construct(dbHelper $dbHelper)
    // {
    //     $this->_db = $dbHelper;
    // }

    public function save($data) {
        
        $this->_db->where('status_name',$data['status_name']);
        $match = $this->_db->getOne('tms_user_status');

        if($match){
            $return['status'] = 422;
            $return['msg'] = 'Status already exists.'; 
        }else{
            $data['created_date'] = date('Y-m-d H:i:s');
            $data['updated_date'] = date('Y-m-d H:i:s');
            $id = $this->_db->insert('tms_user_status', $data);
            if ($id) {
                $return['status'] = 200;
                $return['msg'] = 'Insert Successfully.';
            } else {
                $return['status'] = 422;
                $return['msg'] = 'Not inserted.';
            }
        }
        

        return $return;
    }

    public function update($id, $data) {
        $this->_db->where('status_name',$data['status_name']);
        $match = $this->_db->getOne('tms_user_status');

        if($match && $match['status_id'] != $id){
            $return['status'] = 422;
            $return['msg'] = 'Name of resource status already exists.'; 
        }else{
            $data['updated_date'] = date('Y-m-d H:i:s');
            $this->_db->where('status_id', $id);
            $id = $this->_db->update('tms_user_status', $data);
            if ($id) {
                $return['status'] = 200;
                $return['msg'] = 'Insert Successfully.';
            } else {
                $return['status'] = 422;
                $return['msg'] = 'Not inserted.';
            }
        }    
        return $return;
    }

    public function getAll($type) {
        $this->_db->where('status_type', $type);
        $results = $this->_db->get('tms_user_status');
        return $results;
    }

    public function delete($id) {
        $this->_db->where('eUserStatus', $id);
        $ifIdUsedInUsers = $this->_db->getOne('tms_users');
        
        $this->_db->where('vStatus', $id);
        $ifIdUsedInClient = $this->_db->getOne('tms_client');
        if($ifIdUsedInUsers || $ifIdUsedInClient){
            $return['status'] = 404;
            $return['msg'] = 'This record is already in use.';
        }else{
            $this->_db->where('status_id', $id);
            $id = $this->_db->delete('tms_user_status');
            if ($id) {
                $return['status'] = 200;
                $return['msg'] = 'Deleted Successfully.';
            } else {
                $return['status'] = 422;
                $return['msg'] = 'Not inserted.';
            }
        }
        

        return $return;
    }

    public function getTypeById($id) {
        $this->_db->where('status_id', $id);
        $result = $this->_db->getOne('tms_user_status');
        return $result;
    }
    
    public function getStatusByField($status_id, $field_name = "") {
        $this->_db->where('status_id', $status_id);
        $data = $this->_db->get('tms_user_status');
        if ($field_name == "") {
            return $data;
        } else {
            if($data) {
                $field_names = explode(',', $field_name);
                $return_data = "";
                foreach ($field_names as $field) {
                    $return_data.= $data[0][$field] . ' ';
                }
                return trim($return_data);    
            }       
        }
    }

    public function getActive() {
        $this->_db->where('is_active', 1);
        $this->_db->where('status_type', 1);
        $result = $this->_db->get('tms_user_status');
        return $result;
    }
    public function getclientActive() {
        $this->_db->where('is_active', 1);
        $this->_db->where('status_type', 2);
        $result = $this->_db->get('tms_user_status');
        return $result;
    }
}
