<?php
require_once(APPPATH . 'classes/ExposedModel.php');
class Contact extends ExposedModel {
    public function getTable() {
        return 'contact';
    }
    public function uploadContacts($params) {
        $req = ['userid','contacts'];
        $this->throwExceptionOnUnset($params, $req);
        $numbers = explode(':', $params['contacts']);
        $batch = [];
        foreach ($numbers as $value) {
            $number = substr($value, -10);
            $exist_contact = $this->getEntries(['userid'=>1,'number'=>$number], ['userid','number']);
            if(count($exist_contact)==0) {
                $batch[] = ['userid'=>$params['userid'],'number'=>$number];
            }
        }
        if(count($batch)!=0) {
            return $this->db->insert_batch($this->getTable(),$batch);
        }
        else {
            throw new Exception("No contacts to update");
        }
    }
}

?>  