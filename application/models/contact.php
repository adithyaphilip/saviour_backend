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
        $lreq = ['userid','number'];
        foreach ($numbers as $value) {
            $params['number'] = substr($value, -10);
            $exist_contact = $this->getEntries($params, $lreq);
            if(count($exist_contact)==0) {                
                $this->newEntry($params,$lreq);
            }
        }
        return true;
    }
}

?>  