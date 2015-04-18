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
            $batch[] = ['userid'=>$params['userid'],'number'=>$number];
        }
        return $this->db->insert_batch($this->getTable(),$batch);
    }
}

?>  