<?php
require_once(APPPATH . 'classes/ExposedModel.php');
class Gcm extends ExposedModel {
    /**
     * sends GCM message to userids mentiond in params
     * @param type $params
     */
    function sendMessageToUsers( $params ) {
        $req = ['userids'];
        $this->throwExceptionOnUnset($params, $req);
        $gcmregids_resp = 
                $this->db->where_in('id', $params['userids'])
                ->select('gcmregid')->get('userlist')->result_array();
        
        $gcmregids = [];
        foreach($gcmregids_resp as $row) {
            $gcmregids[] = $row['gcmregid'];
        }        
        $this->sendGoogleCloudMessage(['gcmregids'=>$gcmregids]);
    }
    
    function sendGoogleCloudMessage( $params )
    {
        ini_set('display_errors', 1);
        
        $ids = $params['gcmregids'];
        //------------------------------
        // Replace with real GCM API 
        // key from Google APIs Console
        // 
        // https://code.google.com/apis/console/
        //------------------------------

        $apiKey = 'AIzaSyAK4m_0pr8GeVxi71PTtn55B04ULPy61PI';

        //------------------------------
        // Define URL to GCM endpoint
        //------------------------------

        $url = 'https://android.googleapis.com/gcm/send';

        //------------------------------
        // Set GCM post variables
        // (Device IDs)
        // No payload since this is a tickle to sync
        //------------------------------

        $post = array(
                        'registration_ids'  => $ids,
                        );

        //------------------------------
        // Set CURL request headers
        // (Authentication and type)
        //------------------------------

        $headers = array( 
                            'Authorization: key=' . $apiKey,
                            'Content-Type: application/json'
                        );

        //------------------------------
        // Initialize curl handle
        //------------------------------

        $ch = curl_init();

        //------------------------------
        // Set URL to GCM endpoint
        //------------------------------

        curl_setopt( $ch, CURLOPT_URL, $url );

        //------------------------------
        // Set request method to POST
        //------------------------------

        curl_setopt( $ch, CURLOPT_POST, true );

        //------------------------------
        // Set our custom headers
        //------------------------------

        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

        //------------------------------
        // Get the response back as 
        // string instead of printing it
        //------------------------------

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        //------------------------------
        // Set post data as JSON
        //------------------------------

        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post ) );

        //------------------------------
        // Actually send the push!
        //------------------------------

        $result = curl_exec( $ch );

        //------------------------------
        // Error? Display it!
        //------------------------------

        if ( curl_errno( $ch ) )
        {
            echo 'GCM error: ' . curl_error( $ch );
        }

        //------------------------------
        // Close curl handle
        //------------------------------

        curl_close( $ch );

        //------------------------------
        // Debug GCM response
        //------------------------------

        echo $result;
    }

    public function getTable() {
        return 'userlist';
    }

}
?>