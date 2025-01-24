<?php

include 'sendmail/class.phpmailer.php';
include 'attach_mailer/attach_mailer_class.php';

require 'mailjet/vendor/autoload.php';
use \Mailjet\Resources;

class customhelper {

    protected $_db;
    protected $_mailer;
    protected $_attach_mailer;
    protected $_mailjet;

    public function __construct() {
        // $this->_db = db::getInstance();
        $this->_db = new db(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        $this->_mailer = new PHPMailer();

        $this->_mailjet = new \Mailjet\Client(EMAIL_API_KEY, EMAIL_SECRETE_KEY,true,['version' => 'v3.1']);
    }

    public function isLogged() {
        if ($_SESSION['is_login']) { # When logged in this variable is set to TRUE
            return TRUE;
        } else {
            return FALSE;
        }
    }

    // remove random number from files
    public function get_filename($file) {
        $arr = explode('-', $file);
        for ($a = 1; $a < sizeof($arr); $a++) {
            $data = $arr[$a];
        }
        return $data;
    }

    //# Log a user Out
    public function sessionX($logout_time) {
        $logLength = $logout_time; # time in seconds :: 1800 = 30 minutes
        $ctime = strtotime("now"); # Create a time from a string
        # If no session time is created, create one
        if (!isset($_SESSION['sessionX'])) {
            # create session time
            $_SESSION['sessionX'] = $ctime;
        } else {
            # Check if they have exceded the time limit of inactivity
            if (((strtotime("now") - $_SESSION['sessionX']) > $logLength) && $this->isLogged()) {
                # If exceded the time, log the user out
                $_SESSION['is_lock'] = 'yes';
                echo "<script>window.location='lock'</script>";

                # Redirect to login page to log back in
                //header("Location: login.php");
                exit;
            } else {
                # If they have not exceded the time limit of inactivity, keep them logged in
                $_SESSION['sessionX'] = $ctime;
            }
        }
    }
    
    //get userdata
    public function get_user($user_id, $field_name = '') {
        $this->_db->where('user_id', $user_id);
        $data = $this->_db->get(TBL_USERS);
        
        if ($field_name == "") {
            return $data[0];
        } else {
            $field_names = explode(',', $field_name);
            $return_data = "";
            foreach ($field_names as $field) {
                $return_data.= $data[0][$field] . ' ';
            }
            return trim($return_data);
        }
    }
    
    //get customerdata
    public function get_customer($customer_id, $field_name = '') {
        $this->_db->where('customer_id', $customer_id);
        $data = $this->_db->get(TBL_CUSTOMERS);
        
        if ($field_name == "") {
            return $data[0];
        } else {
            $field_names = explode(',', $field_name);
            $return_data = "";
            foreach ($field_names as $field) {
                $return_data.= $data[0][$field] . ' ';
            }
            return trim($return_data);
        }
    }
    
    public function avatar($user_id) {
        $avatar = $this->get_user($user_id, 'profile_pic');
        if ($avatar == '') {
            return 'assets/img/avatar.jpg';
        } else {
            return 'uploads/avatar/' . $avatar;
        }
    }

    //get userrole/s
    public function get_user_role($user_id) {
//        $this->_db->where('user_id', $user_id);
//        $results = $this->_db->get(TBL_USERS, null, array('role_id'));
//
//        $user_role_query = $this->_db->selectdata(TBL_ROLES, "role_id IN ($user_role_id)");
//        $user_roles = mysql_fetch_object($user_role_query);
//        return $user_roles->name;
                
        $ids = $this->_db->subQuery();
        $ids->where("user_id", $user_id, "=");
        $ids->get(TBL_USERS, null, "role_id");

        $this->_db->where("role_id", $ids, 'in');
        $res = $this->_db->get(TBL_ROLES);
        // print_r($res);
        return $res[0]['name'];
    }

    public function dropdown($db_array, $select_array, $option_array) {
        
//        mysql_set_charset('utf8');
        $query = "SELECT * FROM " . $db_array['tbl_name'];
        
        if (array_key_exists('innerjoin', $db_array)) {
            $query .= " " . $db_array['innerjoin'];
        }
        
        if (array_key_exists('condition', $db_array)) {
            $query .= " WHERE " . $db_array['condition'];
        }
        
        if (array_key_exists('order_by', $db_array)) {
            $query .= " ORDER BY " . $db_array['order_by'];
        }
        
//      mysql_set_charset('utf8');
        //echo $query;
        $dropdown = $this->_db->rawQuery($query);
        $count = $this->_db->count;
//      print_r($result);
        //$result = mysql_query($query) or die('error');
        $return = '<select ';
        foreach ($select_array as $select_key => $select_value) {
            if ($select_key == 'multiple') {
                if ($select_value == '1') {
                    $return.='multiple';
                } else {
                    $return.= '';
                }
            } else if ($select_key == 'disabled') {
                if ($select_value == 'disabled') {
                    $return.='disabled';
                } else {
                    $return.= '';
                }
            } else {
                $return .=$select_key . "='" . $select_value . "' ";
            }
        }
        
        $return .='>';
        if (array_key_exists('placeholder', $option_array)) {
            $return .='<option value="">' . $option_array['placeholder'] . '</option>';
        }
        
        for ($d = 0; $d < $count; $d++) {
            $opt = explode(',', $option_array['label']);
            $return .='<option value="' . $dropdown[$d][$option_array['value']] . '"';
            $selected_vals = explode(',', @$option_array['selected']);
            for ($s = 0; $s < sizeof($selected_vals); $s++) {
                if ($dropdown[$d][$option_array['value']] == $selected_vals[$s]) {
                    $return .='selected="selected"';
                }
            }
            $return .='>';
            for ($i = 0; $i < sizeof($opt); $i++) {
                $return .=$dropdown[$d][$opt[$i]] . "&nbsp;";
            }
            $return .='</option>';
        }
        $return .='</select>';
        return $return;
    }   

//get attachment
//    public function get_attachment($attchment_id) {
//        $attachment_query = $this->_db->selectdata("tbl_attachment", "attach_id='" . $attchment_id . "'");
//        $attachment = mysql_fetch_object($attachment_query);
//        return $attachment;
//    }
//styles
    public function styles($style_value) {
        $style_array = explode(',', $style_value);
        $css = '';

        foreach ($style_array as $style) {
            $css .= '<link rel="stylesheet" type="text/css" href="' . $style . '" media="all" />' . "\n\r";
        }
        return $css;
    }

//styles
    public function js($js_value) {
        $js_array = explode(',', $js_value);
        $javascripts = '';
        foreach ($js_array as $js) {
            $javascripts .= '<script type="text/javascript" src="' . $js . '"></script>' . "\n\r";
        }
        return $javascripts;
    }

    //send mail
    public function send_email($to, $cc, $bcc, $subject, $message, $alert_msg, $attachments) {
        //$this->_PHPMailer = new PHPMailer();
        $this->_mailer = 'ISO-8859-1';
        $this->_mailer->IsSMTP();
        $this->_mailer->Host = "ssl://smtp.gmail.com";
        $this->_mailer->From = "krunal48@gmail.com";
        $this->_mailer->FromName = "Admin";

        $this->_mailer->AddAddress($to);

        if ($cc != "") {
            $this->_mailer->AddCC($cc);
        }

        if ($bcc != "") {
            $this->_mailer->AddBCC($cc);
        }

        $this->_mailer->SMTPAuth = "true";
        $this->_mailer->Username = "krunal48@gmail.com";
        $this->_mailer->Password = "";
        $this->_mailer->Port = "465";

        $this->_mailer->Subject = $subject;
        $this->_mailer->IsHTML(true);
        $this->_mailer->Body = $message;
        $this->_mailer->WordWrap = 50;


        if ($attachments != '') {
            foreach ($attachments as $key => $val) {
                $this->_mailer->AddAttachment($val);
            }
        }
        if (!$this->_mailer->Send()) {
            echo 'Message was not sent.';
            echo 'Mailer error: ' . $this->_mailer->ErrorInfo;
        } else {
            if ($alert_msg != "") {
                echo $alert_msg;
            }
        }
    }

    public function send_mail($to_mail, $cc_mail, $bcc_mail, $subject_mail, $message, $alert_msg, $attachments) {
        $this->_attach_mailer = new attach_mailer($name = "Admin", $from = "php@kanhasoft.com", $to_mail, $cc_mail, $bcc_mail, $subject_mail);
        if ($attachments != '') {
            foreach ($attachments as $key => $val) {
                $this->_attach_mailer->add_attach_file($val);
            }
        }
        $this->_attach_mailer->html_body = $message;
        $this->_attach_mailer->text_body = strip_tags($this->_attach_mailer->html_body, "<a>");
        $this->_attach_mailer->process_mail();
        return $this->_attach_mailer->get_msg_str();
    }

    public function generateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function char_limit($length, $limit) {
        $string = strip_tags($length);

        if (strlen($string) > $limit) {

// truncate string
            $stringCut = substr($string, 0, $limit);

// make sure it ends in a word so assassinate doesn't become ass...
            $string = substr($stringCut, 0, strrpos($stringCut, ' '));

            $string = $string . "...";
        }
        echo $string;
    }

    public function moneyFormatIndia($num1) {
        $num_array = explode('.', $num1);
        $num = $num_array[0];

        $explrestunits = "";
        if (strlen($num) > 3) {
            $lastthree = substr($num, strlen($num) - 3, strlen($num));
            $restunits = substr($num, 0, strlen($num) - 3); // extracts the last three digits
            $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
            $expunit = str_split($restunits, 2);
            for ($i = 0; $i < sizeof($expunit); $i++) {
                // creates each of the 2's group and adds a comma to the end
                if ($i == 0) {
                    $explrestunits .= (int) $expunit[$i] . ","; // if is first value , convert into integer
                } else {
                    $explrestunits .= $expunit[$i] . ",";
                }
            }
            $thecash = $explrestunits . $lastthree;
        } else {
            $thecash = $num;
        }
        return $thecash . "." . $num_array[1]; // writes the final format where $currency is the currency symbol.
    }

    public function check_isset($value, $other) {
        $string = isset($value) ? $value : $other;
        return $string;
    }

    // SMTP send mail (Using mailjet library)
    public function send_email_smtp($to, $to_name = '', $cc, $bcc, $subject, $content, $attachments = '', $inlinedAttachments ='', $sendFromInvoiceEmail = 0 ) {
        $smtpFromEmail = $sendFromInvoiceEmail == 1 ? SMTP_FROM_EMAIL_INVOICE : SMTP_FROM_EMAIL;         
        $mailParams = [
            'Messages' => [
                [
                    'From' => [
                            'Email' => $smtpFromEmail,
                            'Name' => SMTP_FROM_NAME
                        ],
                    'To' => [[
                                'Email' => $to,
                                'Name' => $to_name
                            ]],
                    'Subject' => $subject,
                    //'HTMLPart' => $htmlString,
                    'HTMLPart' => $content,
                ]    
            ]
        ];
        //$mailParams['Messages'][0]['InlinedAttachments'] =  $inlinedAttachments;
        // parameter array of array
        if ($cc != '') {
            $arrCC = [];
            $var = explode(',',$cc);
            foreach ($var as $ccAddress) {
                $arrE = ['Email' => $ccAddress,];
                    array_push($arrCC, $arrE);
                }
            $mailParams['Messages'][0]['Cc'] = $arrCC;
        }
        if ($bcc) {
            $arrBcc = [];
            $varA = explode(',',$bcc);
            foreach ($varA as $bccAddress) {
                $arrE2 = ['Email' => $bccAddress,];
                    array_push($arrBcc, $arrE2);
                }
            $mailParams['Messages'][0]['Bcc'] = $arrBcc;
        }
        if (is_array($attachments)) {
            // Attachment parameter
            $mailParams['Messages'][0]['Attachments'] =  $attachments;
        }
        if (is_array($inlinedAttachments)) {
            $mailParams['Messages'][0]['InlinedAttachments'] =  $inlinedAttachments;
        }
        $response = $this->_mailjet->post(Resources::$Email, ['body' => $mailParams]);
        if ($response->success()) {            
            $result['status'] = 200;
            $result['msg'] = 'Succes!';
            return $result;            
        } else {
            $result['status'] = 422;
            $result['msg'] = 'Could not send mail!';
            return $result;
        }
    }

    public function mailjet_get_all_email(){
        //$this->_mailjet = new \Mailjet\Client(EMAIL_API_KEY, EMAIL_SECRETE_KEY,true,['version' => 'v3.1']);
        // version v3
        $mj = new \Mailjet\Client( EMAIL_API_KEY, EMAIL_SECRETE_KEY ,true,['version' => 'v3']);

        $params = [
            'Limit' => 1000, 
            'ShowSubject' => true,
            'Sort' => 'ArrivedAt DESC',
            'ShowContactAlt' => true
            //'ShowSubject' => true,
            
        ];
        // Fetch sent emails with query parameters
        $response = $mj->get(Resources::$Message, ['filters' => $params]);
        if ( $response->success() ) {            
            $result = $response->getData();
            $result['status'] = 200;
            $result['msg'] = 'Succes!';
        } else {
            $result['status'] = 422;
            $result['msg'] = 'Could not send mail!';
        }
        return $result;            
        
    }

    public function send_email_smtp__imgg($to, $to_name = '', $cc, $bcc, $subject, $content, $attachments = '') {
        $b64image = base64_encode(file_get_contents('http://tms.kanhasoftdev.com/assets/img/logo.png'));

        $dom = new DOMDocument;
        @$dom->loadHTML($content);
        $imgs = $dom->getElementsByTagName('img');
        // Store the list of image urls in an array - this will come in handy later
        $imgURLs = [];
        foreach($imgs as $img) {
            if (!$img->hasAttribute('src')) {
                continue;
            }
            $imgURLs[] = $img->getAttribute('src');
            //$img->setAttribute('src', $var);
        }
        //$htmlString = $dom->saveHTML();
        $i=0;
        $urlsrc = $urlsrc =  [];
        $inlinedAttachments=[]; 
        foreach($imgURLs as $imgURL) {
            //$cid[] = addslashes('src="cid:id'.$i.'"');
            $cid[] = 'src="cid:id'.$i.'"';
            $urlsrc[] = 'src="'. $imgURL . '"' ;
            //if (str_contains($imgURL1, 'base64,')) {
            if(strpos($imgURL, 'base64,') !== false){     
                $img = explode( 'base64,', $imgURL );
                $imgURL = $img[1];
            }else{
                $imgURL = base64_encode(file_get_contents('http://tms.kanhasoftdev.com/'.$imgURL));
            }
            $inlinedAttachments[] = array(
                'ContentType' => "image/gif",
                'Filename' => "e59d1954aef9b3ed1a5e3c4c8e43a7f081cfa9e9.gif",
                'ContentID' => "id".$i,
                'Base64Content' => $imgURL
            );

            $i++;
        }
        $htmlString = str_replace($urlsrc, $cid , $content);
        
    }

}

?>