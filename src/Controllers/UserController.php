<?php

namespace App\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Utils\JsonResponse;
use App\Models\dbHelper;

class UserController
{
    private $db;
    protected $_useremail;
    protected $_username;
    protected $_userid;

    public function __construct(dbHelper $dbHelper)
    {
        $this->_db = $dbHelper;
    }

    public function getUsers(Request $request, Response $response): Response
    {
        $users = $this->_db->get('tms_users');
        return JsonResponse::respond($response, $users, 200, 'Users retrieved successfully');
    }

    protected function getUser_Email()
    {
        $this->_db->where("vEmailAddress", $this->_useremail);
        return $results = $this->_db->getOne('tms_users');
    }

    protected function getUser_Username()
    {
        $this->_db->where("vUserName", $this->_username);
        return $results = $this->_db->getOne('tms_users');
    }

    protected function getUser_Id()
    {
        $this->_db->where("iUserId", $this->_userid);
        return $results = $this->_db->getOne('tms_users');
    }

    public function getAlluserGroup()
    {
        $this->_db->where("iFkUserTypeId", 1);
        $this->_db->where('eUserStatus', 3); //new Added after Set Inactive in users
        $data = $this->_db->get('tms_users');
        return $data;
    }

    public function getUserAllfile($field, $id)
    {
        $this->_db->where($field, $id);
        return $this->_db->getOne('tms_users');
    }
    public function getUserUsingId($id)
    {
        $this->_db->where('iUserId', $id);
        return $this->_db->getOne('tms_users');
    }

    public function getAllUserById($field, $id, $table)
    {
        $this->_db->where($field, $id);
        return $this->_db->get($table);
    }
    public function internalgetProfile()
    {
        $this->_db->where('iUserId', $user_id);
        $data = $this->_db->get('tms_users');
        if ($field_name == "") {
            return $data;
        } else {
            $field_names = explode(',', $field_name);
            $return_data = "";
            foreach ($field_names as $field) {
                $return_data .= $data[0][$field] . ' ';
            }
            return trim($return_data);
        }
    }

    public function getUserDataById(Request $request, Response $response, $args): Response
    {
        $user_id = $args['id'];
        $field_name = isset($args['field_name']) ? $args['field_name'] : "";

        $this->_db->where('iUserId', $user_id);
        $data = $this->_db->get('tms_users');
        if ($field_name == "") {
            $return_data = $data;
        } else {
            $field_names = explode(',', $field_name);
            $return_data = "";
            foreach ($field_names as $field) {
                $return_data .= $data[0][$field] . ' ';
            }
            return trim($return_data);
        }
        if ($return_data) {
            $result = [
                'data' => $data
            ];
            return JsonResponse::respond($response, $result, 200, 'Data retrieved successfully');
        } else {
            return JsonResponse::respond($response, null, 404, 'Data not found');
        }
    }

    public function getUserByField(Request $request, Response $response, $args): Response
    {
        $user_id = $args['id']; // Get the user ID from the route parameter
        $field_name = isset($args['field_name']) ? $args['field_name'] : ""; // Get the field names (comma separated)

        $this->_db->where('iUserId', $user_id);
        $data = $this->_db->get('tms_users');

        if (empty($data)) {
            // If no user data found, return 404 with a message
            return JsonResponse::respond($response, null, 404, 'User not found');
        }

        if ($field_name == "") {
            $return_data = $data;
        } else {
            $field_names = explode(',', $field_name);
            $return_data = [];

            // Loop through the specified fields and collect their values
            foreach ($field_names as $field) {
                if (isset($data[0][$field])) {
                    $return_data[$field] = $data[0][$field];
                }
            }
        }

        if (!empty($return_data)) {
            // Return a 200 response with the requested fields/data
            return JsonResponse::respond($response, $return_data, 200, 'Data retrieved successfully');
        } else {
            // If no valid fields were found, return 404 with an error message
            return JsonResponse::respond($response, null, 404, 'Fields not found');
        }
    }


    public function getUser($user_id, $field_name = "")
    {
        $this->_db->where('iUserId', $user_id);
        $data = $this->_db->getone('tms_users');
        if ($field_name == "") {
            return $data;
        } else {
            $field_names = explode(',', $field_name);
            $return_data = "";
            foreach ($field_names as $field) {
                $return_data .= $data[0][$field] . ' ';
            }
            return trim($return_data);
        }
    }

    public function userlist()
    {
        $userstatus = new userstatus();
        $this->_db->where('eUserStatus', 3);
        $data = $this->_db->get('tms_users');
        foreach ($data as $key => $value) {
            $status = $userstatus->getTypeById($value['eUserStatus']);
            $data[$key]['is_active'] = $status['status_name'];
        }

        $result['data'] = $data;
        $result['status'] = 200;
        return $result;
    }

    public function clientlistindirectGet($id)
    {
        $this->_db->where('iFkUserTypeId', $id);
        $data = $this->_db->get('tms_users');
        $result['data'] = $data;
        $result['status'] = 200;
        return $result;
    }

    public function userExternalGet($id)
    {
        $this->_db->where('iFkUserTypeId', $id);
        $this->_db->where('eUserStatus', 3); //new Added after Set Inactive in users
        $data = $this->_db->get('tms_users');
        $result['data'] = $data;
        $result['status'] = 200;
        return $result;
    }

    public function userwithType($type)
    {
        //$this->_db->where('iFkUserTypeId', $type);
        //$data = $this->_db->get(TBL_USERS);
        /*$data = $this->_db->rawQuery("SELECT tu.*,tut.vType FROM tms_users As tu INNER JOIN tms_user_type As tut on tu.vResourceType = tut.iTypeId WHERE iFkUserTypeId = $type");*/
        if ($type == 1) {
            //$data = $this->_db->rawQuery("SELECT tu.*,tut.vType,tup.position_name,tus.status_name FROM tms_users As tu INNER JOIN tms_user_type As tut on tu.vResourceType = tut.iTypeId INNER JOIN tms_user_position As tup ON tup.position_id = tu.vResourcePosition INNER JOIN tms_user_status As tus ON tus.status_id = tu.eUserStatus WHERE iFkUserTypeId = $type ");
            // Innrer Join to left join (tms_user_type)
            $data = $this->_db->rawQuery("SELECT tu.*,tut.vType,tup.position_name,tus.status_name FROM tms_users As tu LEFT JOIN tms_user_type As tut on tu.vResourceType = tut.iTypeId INNER JOIN tms_user_position As tup ON tup.position_id = tu.vResourcePosition INNER JOIN tms_user_status As tus ON tus.status_id = tu.eUserStatus WHERE iFkUserTypeId = $type ORDER BY CONCAT(tu.vFirstName, ' ', tu.vLastName) ASC ");
        } else {
            //$data = $this->_db->rawQuery("SELECT tu.*,tus.status_name FROM tms_users As tu  INNER JOIN tms_user_status As tus ON tus.status_id = tu.eUserStatus WHERE iFkUserTypeId = $type && activation_status = 1");
            $data = $this->_db->rawQuery("SELECT tu.*, concat(tu.vFirstName, ' ', tu.vLastName) as vUserFullName, tus.status_name FROM tms_users As tu  INNER JOIN tms_user_status As tus ON tus.status_id = tu.eUserStatus WHERE iFkUserTypeId = $type ORDER BY CONCAT(tu.vFirstName, ' ', tu.vLastName) ASC ");
        }

        $result['data'] = $data;
        $result['status'] = 200;
        return $result;
    }

    public function userQaSpecialist($type)
    {
        //$this->_db->where('vResourcePosition', $type);
        $this->_db->where('FIND_IN_SET(' . $type . ',vResourcePosition)');
        $this->_db->where('eUserStatus', 3); //new Added after Set Inactive in users
        $this->_db->where('activation_status', 1);
        $data = $this->_db->get('tms_users');
        //$data = $this->_db->rawQuery("SELECT * FROM tms_users WHERE activation_status=1  AND eUserStatus = 3 AND (FIND_IN_SET($type,vResourcePosition) > 0) ");

        $result['data'] = $data;
        $result['status'] = 200;
        return $result;
    }

    public function userManager($type)
    {
        $this->_db->where('FIND_IN_SET(' . $type . ',vResourcePosition)');
        $this->_db->where('eUserStatus', 3); //new Added after Set Inactive in users
        $this->_db->where('activation_status', 1);
        $data = $this->_db->get('tms_users');
        //$data = $this->_db->rawQuery("SELECT * FROM tms_users WHERE activation_status=1  AND eUserStatus=3 AND (FIND_IN_SET($type,vResourcePosition) > 0) ");

        $result['data'] = $data;
        $result['status'] = 200;
        return $result;
    }

    public function userCoordinator($type)
    {
        // $this->_db->where('vResourcePosition', $type);
        $this->_db->where('FIND_IN_SET(' . $type . ',vResourcePosition)');
        $this->_db->where('eUserStatus', 3); //new Added after Set Inactive in users
        $this->_db->where('activation_status', 1);
        $data = $this->_db->get('tms_users');
        //$data = $this->_db->rawQuery("SELECT * FROM tms_users WHERE activation_status=1  AND eUserStatus=3 AND (FIND_IN_SET($type,vResourcePosition) > 0) ");

        $result['data'] = $data;
        $result['status'] = 200;
        return $result;
    }

    // done
    public function checkusername(Request $request, Response $response, $args): Response
    {
        $bodyContent = $request->getBody()->getContents();
        $data = json_decode($bodyContent, true);

        $this->_username = $data;
        if ($this->getUser_Username()) {
            $return['status'] = 422;
            $return['msg'] = 'User name already exists.';
            return JsonResponse::respond($response, null, 200, 'username already exists');
        } else {
            $return['status'] = 200;
            $return['msg'] = 'success';
            return JsonResponse::respond($response, null, 200, 'success');
        }
    }

    public function checkusernameExist(Request $request, Response $response, $args): Response
    {
        $bodyContent = $request->getBody()->getContents();
        $data = json_decode($bodyContent, true);

        if ($data['id'] != 0)
            $this->_db->where("iUserId", $data['id']);
        $this->_db->where("vUserName", $data['username']);
        $results = $this->_db->getOne(TBL_USERS);

        if ($results) {
            $return['userExist'] = 1;
            $return['status'] = 200;
            $return['msg'] = 'User name already exists.';

            return JsonResponse::respond($response, null, 200, 'username already exists');
        } else {
            $return['status'] = 200;
            $return['msg'] = 'success';
            return JsonResponse::respond($response, null, 200, 'success');
        }
    }

    // done
    public function checkemailaddress(Request $request, Response $response, $args): Response
    {
        $bodyContent = $request->getBody()->getContents();
        $data = json_decode($bodyContent, true);
        $this->_useremail = $data;
        if ($this->getUser_Email()) {
            $return['status'] = 422;
            $return['msg'] = 'Email address already exists.';
            return JsonResponse::respond($response, null, 200, 'Email address already exists.');

        } else {
            $return['status'] = 200;
            $return['msg'] = 'success';
            return JsonResponse::respond($response, null, 200, 'success');
        }
    }

    public function saveuserprofile($user)
    { //For Internal Resource Profile
        $activationToken = sha1(mt_rand(10000, 99999) . time());
        $emailPassToSend = $user['vPassword'];
        $user['activation_token'] = $activationToken;

        $this->_username = $user['vUserName'];
        $this->_useremail = $user['vEmailAddress'];

        if ($this->getUser_Username()) {
            $return['status'] = 422;
            $return['msg'] = 'User name already exists.';
        } else if ($this->getUser_Email()) {
            $return['status'] = 422;
            $return['msg'] = 'Email address already exists.';
        } else {
            $user['iFkUserTypeId'] = '1';
            $user['org_pass'] = $user['vPassword'];
            $user['vPassword'] = md5($user['vPassword']);
            $user['vProfilePic'] = isset($user['image']) ? $this->uploadimage($user) : '';
            $user['vSignUpload'] = isset($user['vSignUpload']) ? $this->uploadSignimage($user) : '';
            $user['dtBirthDate'] = (isset($user['dtBirthDate']) && $user['dtBirthDate'] != 'Invalid Date' && $user['dtBirthDate'] != '') ? date('Y-m-d', strtotime($user['dtBirthDate'])) : NULL;
            $user['dtCreationDate'] = date('Y-m-d H:i:s'); /*$user['dtCreationDate']*/
            $user['dtCreatedDate'] = date('Y-m-d H:i:s');
            $user['dtUpdatedDate'] = date('Y-m-d H:i:s');
            unset($user['image']);
            $id = $this->_db->insert(TBL_USERS, $user);
            if ($id) {
                //Sending registation email to users email address
                //$userName = $user['vUserName'];
                $userName = $user['vFirstName'];
                $registrationLink = ACTIVATION_URL . '#/activation/' . $activationToken;
                $registrationLink = '<a href="' . ACTIVATION_URL . '#/activation/' . $activationToken . '"> ' . $registrationLink . ' </a>';
                $userEmail = $user['vEmailAddress'];
                $userPassword = $emailPassToSend;
                $businessManagerLink = '';

                $this->_db->where('template_id', 8);
                $emailTemplateRegistration = $this->_db->getOne('tms_email_templates');
                $search_array = array("[USERNAME]", "[REGISTRATIONLINK]", '[USEREMAIL]', '[USERPASSWORD]', '[BUSINESSMANAGERLINK]');

                $replace_array = array($userName, $registrationLink, $userEmail, $userPassword, $businessManagerLink);

                $html = str_replace($search_array, $replace_array, $emailTemplateRegistration['template_content']);

                //$jobDetail = new jobs_detail();
                //$jobDetail->sendEmail($userEmail,$emailTemplateRegistration['template_subject'],$html);
                // mailjet send mail
                $to_name = $user['vFirstName'];
                $send_fn = new functions();
                $response = $send_fn->send_email_smtp($userEmail, $to_name, $cc = '', $bcc = '', $emailTemplateRegistration['template_subject'], $html, $attachments = '');
                // End mailjet

                $return['status'] = 200;
                $return['msg'] = 'Inserted Successfully.';
                $return['iUserId'] = $id;
                $data['user_id'] = $id;
                $data['name'] = 'internal-' . $user['iResourceNumber'];
                $this->_db->insert('tms_filemanager', $data);
                $return['status'] = 200;
                $return['msg'] = 'Inserted Successfully.';
                $return['iUserId'] = $id;
                $this->_userid = $id;
                $return['userData'] = $this->getUser_Id();
            } else {
                $return['status'] = 422;
                $return['msg'] = 'Not inserted.';
            }
        }
        return $return;
    }

    public function saveuserprofileexternelS($user)
    {
        $activationToken = sha1(mt_rand(10000, 99999) . time());
        $emailPassToSend = $user['vPassword'];
        $user['activation_token'] = $activationToken;

        $this->_username = $user['vUserName'];
        $this->_useremail = $user['vEmailAddress'];
        if ($this->getUser_Username()) {
            $user['vUserName'] = $user['vUserName'] . ' ' . $user['iResourceNumber'];
        }
        if ($this->getUser_Email()) {
            $return['status'] = 422;
            $return['msg'] = 'Email address already exists.';
        } else if (isset($user['vSecondaryEmailAddress']) && ($user['vEmailAddress'] == $user['vSecondaryEmailAddress'])) {
            $return['status'] = 422;
            $return['msg'] = 'Secondary Email Address should be different.';
        } else {
            $user['iFkUserTypeId'] = '2';
            $user['vPassword'] = md5($user['vPassword']);
            $user['vProfilePic'] = isset($user['image']) ? $this->uploadimage($user) : '';
            $user['dtBirthDate'] = $user['dtBirthDate'] ? date('Y-m-d', strtotime($user['dtBirthDate'])) : '0000-00-00';
            $user['dtCreationDate'] = date('Y-m-d H:i:s')/*$user['dtCreationDate']*/ ;
            $user['dtCreatedDate'] = date('Y-m-d H:i:s');
            $user['dtUpdatedDate'] = date('Y-m-d H:i:s');
            if (isset($user['image']))
                unset($user['image']);
            $id = $this->_db->insert(TBL_USERS, $user);
            if ($id) {
                $data['created_date'] = date('Y-m-d H:i:s');
                $data['updated_date'] = date('Y-m-d H:i:s');
                $data['user_id'] = $id;
                $data['name'] = 'external-' . $user['iResourceNumber'];
                $exResourceFmanager = $this->_db->insert('tms_filemanager', $data);

                //Inserting Default Folders in External Resource File Manager
                if ($exResourceFmanager) {
                    $defaultFolderArray = array('Data', 'Projects', 'Invoice');
                    foreach ($defaultFolderArray as $key => $value) {
                        $info['name'] = $value;
                        $info['is_default_folder'] = 1;
                        if ($value == 'Projects') {
                            $info['is_ex_project_folder'] = 1;
                        } else {
                            $info['is_ex_project_folder'] = 0;
                        }
                        $info['parent_id'] = $exResourceFmanager;
                        $this->_db->insert('tms_filemanager', $info);
                    }
                }

                //Sending registation email to users email address
                //$userName = $user['vUserName'];
                $userName = $user['vFirstName'];
                $registrationLink = ACTIVATION_URL . '#/activation/' . $activationToken;
                $registrationLink = '<a href="' . ACTIVATION_URL . '#/activation/' . $activationToken . '"> ' . $registrationLink . ' </a>';

                $userEmail = $user['vEmailAddress'];
                $userPassword = $emailPassToSend;
                $businessManagerLink = '';

                $this->_db->where('template_id', 8);
                $emailTemplateRegistration = $this->_db->getOne('tms_email_templates');
                $search_array = array("[USERNAME]", "[REGISTRATIONLINK]", '[USEREMAIL]', '[USERPASSWORD]', '[BUSINESSMANAGERLINK]');

                $replace_array = array($userName, $registrationLink, $userEmail, $userPassword, $businessManagerLink);

                $html = str_replace($search_array, $replace_array, $emailTemplateRegistration['template_content']);

                $jobDetail = new jobs_detail();
                $jobDetail->sendEmail($userEmail, $emailTemplateRegistration['template_subject'], $html);

                $return['status'] = 200;
                $return['msg'] = 'Inserted Successfully.';
                $return['iUserId'] = $id;
                $this->_userid = $id;
                $return['userData'] = $this->getUser_Id();
            } else {
                $return['status'] = 422;
                $return['msg'] = 'Not inserted.';
            }
        }
        return $return;
    }

    public function saveuserprofileinternalupdate($id, $user)
    {
        $email = $user['vEmailAddress'];
        $vUserName = $user['vUserName'];
        $uId = $user['iUserId'];
        $emailExsists = $this->_db->rawQuery("SELECT * FROM tms_users WHERE vEmailAddress = '$email'  AND iUserId != $uId");
        $vUserName = $this->_db->rawQuery("SELECT * FROM tms_users WHERE vUserName = '$vUserName' AND iUserId != $uId");
        if ($emailExsists) {
            $return['status'] = 422;
            $return['msg'] = 'Email address already exists.';
        } else if ($vUserName) {
            $return['status'] = 422;
            $return['msg'] = 'UserName already exists.';
        } else {

            $this->_db->where('iUserId', $id);
            $userData = $this->_db->getOne('tms_users');

            if (isset($user['image'])) {
                if (isset($id)) {
                    // $this->_db->where('iUserId',$id);
                    // $data = $this->_db->getOne('tms_users');
                    $image = $userData['vProfilePic'];
                    if (isset($userData['vProfilePic']) && $userData['vProfilePic']) {
                        $path = "../../uploads/profilePic/";
                        $images = glob($path . $image);
                        if (file_exists($path . $image)) {
                            unlink($path . $image);
                        } else {
                            $image = $userData['vProfilePic'];
                        }
                    }
                }
                $user['vProfilePic'] = $this->uploadimage($user);
            }

            // for sign upload
            if (isset($user['vSignUpload']) && !empty($user['vSignUpload'])) {
                if (isset($id)) {
                    $path = "../../uploads/signImages/";
                    // $this->_db->where('iUserId',$id);
                    // $data = $this->_db->getOne('tms_users');
                    $image = $userData['vSignUpload'];
                    if (isset($userData['vSignUpload']) && $userData['vSignUpload']) {
                        if (file_exists($path . $image)) {
                            unlink($path . $image);
                        }
                    }
                    $image = $user['vSignUpload'];
                    $images = @glob($path . $image);
                }
                $user['vSignUpload'] = $this->uploadSignimage($user);
            }

            //$user['dtBirthDate'] = date('Y-m-d', strtotime($user['dtBirthDate']));
            $user['dtBirthDate'] = ($user['dtBirthDate'] == 'Invalid date' || $user['dtBirthDate'] == '') ? NULL : date('Y-m-d', strtotime($user['dtBirthDate']));
            //$user['dtBirthDate'] = date('Y-m-d', strtotime($user['dtBirthDate']));

            $user['dtUpdatedDate'] = date('Y-m-d H:i:s');
            unset($user['image']);

            //print_r($user);
            if (isset($user['vPassword']))
                unset($user['vPassword']);

            $this->_db->where('iUserId', $id);
            //echo $this->_db->getLastQuery();
            if ($this->_db->update(TBL_USERS, $user)) {
                $return['status'] = 200;
                $return['msg'] = 'Updated Successfully.';
                $this->_userid = $id;
                $return['userData'] = $this->getUser_Id();
                $profilePicPath = 'uploads/profilePic/' . $return['userData']['vProfilePic'];
                $this->_db->where('user_id', $id);
                $this->_db->update('tms_discussion', array('profile_picture_url' => $profilePicPath));
            } else {
                $return['status'] = 422;
                $return['msg'] = 'Not Updated.';
            }
        }
        return $return;
    }

    public function saveuserprofileexternelupdate($id, $user)
    {
        $email = self::getUserAllfile('vEmailAddress', $user['vEmailAddress']);
        $uId = $user['iUserId'];
        $emailid = $user['vEmailAddress'];
        $emailExsists = $this->_db->rawQuery("SELECT * FROM tms_users WHERE vEmailAddress = '$emailid' AND iUserId != $uId");
        if ($this->getUser_Username()) {
            $return['status'] = 422;
            $return['msg'] = 'User name already exists.';
            //} else if($email['iUserId'] != $id && $email['vEmailAddress'] ==  $user['vEmailAddress']) {
        } else if ($emailExsists) {
            $return['status'] = 422;
            $return['msg'] = 'Email address already exists.';
        } else if ($user['vEmailAddress'] == $user['vSecondaryEmailAddress']) {
            $return['status'] = 422;
            $return['msg'] = 'Secondary Email Address should be different.';
        } else {
            if (isset($user['image'])) {
                if (isset($id)) {
                    $this->_db->where('iUserId', $id);
                    $data = $this->_db->getOne('tms_users');
                    $image = $data['vProfilePic'];
                    if (isset($image) && $image != '') {
                        $path = "../../uploads/profilePic/";
                        $images = glob($path . $image);
                        if ($images) {
                            if (file_exists($path . $image)) {
                                unlink($path . $image);
                            }
                        } else {
                            $image = $data['vProfilePic'];
                        }
                    }
                }
                $user['vProfilePic'] = $this->uploadimage($user);
            }
            if (($user['dtBirthDate'] != 'Invalid date'))
                $user['dtBirthDate'] = date('Y-m-d', strtotime($user['dtBirthDate']));
            $user['dtUpdatedDate'] = date('Y-m-d H:i:s');
            unset($user['image']);
            $this->_db->where('iUserId', $id);
            if ($this->_db->update(TBL_USERS, $user)) {
                $return['status'] = 200;
                $return['msg'] = 'Updated Successfully.';
                $this->_userid = $id;
                $return['userData'] = $this->getUser_Id();
            } else {
                $return['status'] = 422;
                $return['msg'] = 'Not Updated.';
            }
        }
        return $return;
    }

    public function uploadimage($data)
    {
        $result = explode(',', $data['image']);

        $getFileType = explode(';', explode(':', $result[0])[1]);

        $finalstring = base64_decode($result[1]);

        $mimetype = self::getImageMimeType($finalstring, $getFileType[0]);
        if ($mimetype == 'jpeg') {
            $mimetype = 'jpg';
        }
        $filename = time() . "." . $mimetype;
        $output_file = UPLOADS_ROOT . "profilePic/" . $filename;
        $ifp = fopen($output_file, "wb");
        fwrite($ifp, $finalstring);
        fclose($ifp);
        return $filename;
    }

    // save another images from the client profile.
    public function uploadSignimage($data)
    {
        $result = explode(',', $data['vSignUpload']);

        $getFileType = explode(';', explode(':', $result[0])[1]);

        $finalstring = base64_decode($result[1]);

        $mimetype = self::getImageMimeType($finalstring, $getFileType[0]);
        if ($mimetype == 'jpeg') {
            $mimetype = 'jpg';
        }
        $filename = time() . "." . $mimetype;
        $output_file = UPLOADS_ROOT . "signImages/" . $filename;
        $ifp = fopen($output_file, "wb");
        fwrite($ifp, $finalstring);
        fclose($ifp);
        return $filename;
    }

    public static function getBytesFromHexString($hexdata)
    {
        for ($count = 0; $count < strlen($hexdata); $count += 2)
            $bytes[] = chr(hexdec(substr($hexdata, $count, 2)));
        return implode($bytes);
    }

    public static function changeDateFormate($data)
    {
        $d = explode(" ", $data);
        $date = explode("/", $d[0]);

        if (isset($d[1]))
            return $final_date = $date[2] . "-" . $date[0] . "-" . $date[1] . " " . $d[1];
        else
            return $final_date = $date[2] . "-" . $date[0] . "-" . $date[1];
    }

    public static function getImageMimeType($imagedata, $FileType)
    {
        // $imagemimetypes = array(
        //     "jpeg" => "FFD8",
        //     "png" => "89504E470D0A1A0A",
        //     "gif" => "474946",
        //     "bmp" => "424D",
        //     "tiff" => "4949",
        //     "tiff" => "4D4D",
        //     "zip" => "630000000000",
        //     "doc" => "CFAD12FE",
        //     "docx" => "504B030414000600",
        //     "doc" => "D0CF11E0A1B11AE100",
        //     "pdf" => "255044462D312E",
        //     "text" => "EFBBBF",
        //     "xlsx" => "504B030414000600",
        //     "rar" => "526172211A0700"
        // );
        $imagemimetypes =
            array(
                array('jpeg', 'FFD8', 'image/jpeg', ),
                array('png', '89504E470D0A1A0A', 'image/png', ),
                array('gif', '474946', 'image/gif', ),
                array('bmp', '424D', 'image/bmp', ),
                array('tiff', '4949', 'image/tiff', ),
                array('zip', '504B0304', 'application/zip', ),
                array('pdf', '255044462D312E', 'application/pdf', ),
                array('text', 'EFBBBF', 'text/plain', ),
                array('rar', '526172211A0700', '', ),
                array('xlsx', '504B030414000600', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', ),
                array('docx', '504B0506', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', ),
                array('pptx', '504B030414000600', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', ),
            );
        foreach ($imagemimetypes as $row) {
            $mime = $row[0];
            $hexbytes = $row[1];
            $bytes = self::getBytesFromHexString($hexbytes);
            if (substr($imagedata, 0, strlen($bytes)) == $bytes) {
                if (empty($FileType)) {
                    return $mime;
                } elseif ($FileType == $row[2]) {
                    return $mime;
                }
            }
        }

        return NULL;
    }

    public function authenticate(Request $request, Response $response, $args): Response
    {
        $bodyContent = $request->getBody()->getContents();
        $data = json_decode($bodyContent, true);

        $username = $data["email"];
        $password = md5($data["password"]);

        $this->_db->where("vEmailAddress", $username);
        $this->_db->where("vPassword", $password);
        $user = $this->_db->getOne('tms_users');

        if ($user && count($user) > 0) {
            if ($user['eUserStatus'] == 4) {
                $response->getBody()->write(json_encode([
                    'status' => 401,
                    'message' => 'Inactive account. Please contact the administrator.'
                ]));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            if ($user['activation_status'] == 0) {
                $response->getBody()->write(json_encode([
                    'status' => 401,
                    'message' => 'Your account is not activated. Please activate your account.'
                ]));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Generate JWT Token
            $secretKey = 'TMS'; // Replace with a secure key
            $issuedAt = time();
            $expirationTime = $issuedAt + (3600*4); // Token valid for 1 hour
            $payload = [
                'iss' => "http://localhost:8080", // Issuer
                'iat' => $issuedAt,        // Issued at
                'exp' => $expirationTime,  // Expiration time
                'sub' => $user['iUserId'], // Subject (user ID)
                'data' => [
                    'id' => $user['iUserId'],
                    'email' => $user['vEmailAddress'],
                    'name' => $user['vUserName']
                ]
            ];

            $jwt = JWT::encode($payload, $secretKey, 'HS256');

            $response->getBody()->write(json_encode([
                'status' => 200,
                'message' => 'Successfully logged in.',
                'token' => $jwt,
                'user_data' => [
                    'id' => $user['iUserId'],
                    'email' => $user['vEmailAddress'],
                    'name' => $user['vUserName']
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode([
                'status' => 422,
                'message' => 'Invalid Username or Password.'
            ]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }
    }

    public function authenticate__(Request $request, Response $response, $args): Response
    {
        $bodyContent = $request->getBody()->getContents();
        //$data = json_decode($request->getBody()->getContents(), true);
        $data = json_decode($bodyContent, true);
        //print_r($data);
        $username = $data["email"];
        $password = md5($data["password"]);

        $this->_db->where("vEmailAddress", $username);
        $this->_db->where("vPassword", $password);
        $user = $this->_db->getOne('tms_users');

        if ($user && count($user) > 0) {
            if ($user['eUserStatus'] == 4) {
                $response->getBody()->write(json_encode([
                    'status' => 401,
                    'message' => 'Inactive account. Please contact the administrator.'
                ]));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            if ($user['activation_status'] == 0) {
                $response->getBody()->write(json_encode([
                    'status' => 401,
                    'message' => 'Your account is not activated. Please activate your account.'
                ]));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Generate JWT Token
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600; // Token valid for 1 hour
            $payload = [
                'iss' => "yourdomain.com", // Issuer
                'iat' => $issuedAt,        // Issued at
                'exp' => $expirationTime,  // Expiration time
                'sub' => $user['iUserId'], // Subject (user ID)
                'data' => [
                    'id' => $user['iUserId'],
                    'email' => $user['vEmailAddress'],
                    'name' => $user['vUserName']
                ]
            ];

            $response->getBody()->write(json_encode([
                'status' => 200,
                'message' => 'Successfully logged in.',
                'Authorization' => $user['vPassword'],
                'user_data' => $user
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode([
                'status' => 422,
                'message' => 'Invalid Username or Password.'
            ]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }
    }
    public function authenticate___($user)
    {
        $username = $user["email"];
        $password = md5($user["password"]);
        $this->_db->where("vEmailAddress", $username);
        $this->_db->where('vPassword', $password);
        $results = $this->_db->getOne('tms_users');

        // checking whether any match found or not
        if ($this->_db->count > 0) {
            if ($results['eUserStatus'] == 4) {
                $return['status'] = 401;
                $return['msg'] = 'Inactivate account please contact administrator.';
            } else if ($results['activation_status'] == 0) {
                $return['status'] = 401;
                $return['msg'] = 'Your account is not activated.Please activate your account.';
            } else {
                $_SESSION['is_login'] = 'yes';
                $_SESSION['user_data'] = $results;
                unset($_SESSION['sessionX']);
                unset($results['org_pass']);
                $return['status'] = 200;
                $return['msg'] = 'Successfully Login.';
                $return['session_data'] = $results;
            }
        } else {
            $return['status'] = 422;
            $return['msg'] = 'Invalid User Name OR Password.';
        }
        return $return;
    }

    public function resetPassword($user)
    {
        $fn = new functions();
        $mail_format = new mail_format();

        $username = $user["email"];
        $this->_db->where("vEmailAddress", $username);
        $results = $this->_db->getOne(TBL_USERS);

        if ($this->_db->count > 0) {
            $str = $fn->generateRandomString(5);
            $newPass = md5($str);

            $this->_db->where('template_category', 3);
            $emailTemplate = $this->_db->getOne('tms_email_templates');
            if ($emailTemplate) {
                $resetPasswordToken = sha1(mt_rand(10000, 99999) . time());
                $resetPasswordLink = ACTIVATION_URL . '#/resetpassword/' . $resetPasswordToken;
                //$search_array = array("[USERNAME]","[USEREMAIL]","[USERPASSWORD]");
                $search_array = array("[USERNAME]", "[RESETPASSWORDLINK]");
                $userNm = $results['vFirstName'] ? $results['vFirstName'] : $results['vUserName'];
                //$userNm = $results['vUserName'];
                $replace_array = array($userNm, $resetPasswordLink);

                $message = str_replace($search_array, $replace_array, $emailTemplate['template_content']);


                // $this->_mailer = new PHPMailer();
                // $this->_mailer->IsSMTP();
                // $this->_mailer->Host = "ssl://smtp.gmail.com";
                // $this->_mailer->SMTPAuth = "true";
                // $this->_mailer->Port = "465";
                // $this->_mailer->Username = SMTP_EMAIL_USER;
                // $this->_mailer->Password = SMTP_EMAIL_PASSWORD;
                // $this->_mailer->From = "Kanhasoft.com";
                // $this->_mailer->FromName = "TMS Admin";
                // $this->_mailer->Subject = $emailTemplate['template_subject'];
                // $this->_mailer->Body = $message;
                // $this->_mailer->WordWrap = 50;
                // $this->_mailer->AddAddress($results['vEmailAddress']);
                // $this->_mailer->IsHTML(true);

                $to = $results['vEmailAddress'];
                $body = $message;
                $subject = $emailTemplate['template_subject'];
                $attachments = '';
                $to_name = ' ';

                // mailjet function  
                $send_fn = new functions();
                $mailResponse = $send_fn->send_email_smtp($to, $to_name, $cc = '', $bcc = '', $subject, $body, $attachments);

                if ($mailResponse['status'] == 200) {
                    $this->_db->where("iUserId", $results['iUserId']);
                    $found = $this->_db->getOne('tms_passwordreset_tbl');

                    if ($found) {
                        $this->_db->where('iUserId', $results['iUserId']);
                        $this->_db->update('tms_passwordreset_tbl', array("reset_password_token" => $resetPasswordToken, "updated_date" => date('Y-m-d H:i:s')));
                    } else {
                        $this->_db->insert('tms_passwordreset_tbl', array("iUserId" => $results['iUserId'], "reset_password_token" => $resetPasswordToken, "created_date" => date('Y-m-d H:i:s'), "updated_date" => date('Y-m-d H:i:s')));
                    }

                    $return['status'] = 200;
                    $return['msg'] = 'Thank you for your email';
                } else {
                    $return['status'] = 400;
                    $return['msg'] = 'Could not send mail!';
                }
            } else {
                $return['status'] = 422;
                $return['msg'] = 'No email template available please contact administrator.';
            }
        } else {
            $return['status'] = 422;
            $return['msg'] = 'You are not Registered in System';
        }
        return $return;
    }

    public function resetPassword1($data)
    {
        $vPassword = md5($data['newPassword']);
        $org_pass = $data['newPassword'];

        $this->_db->where("reset_password_token", $data['resetToken']);
        $found = $this->_db->getOne('tms_passwordreset_tbl');

        if ($found) {
            $this->_db->where('iUserId', $found['iUserId']);
            $id = $this->_db->update('tms_users', array("vPassword" => $vPassword, "org_pass" => $org_pass));
            if ($id) {
                //set blank in token
                $this->_db->where('iUserId', $found['iUserId']);
                $this->_db->update('tms_passwordreset_tbl', array("reset_password_token" => ''));

                $return['status'] = 200;
                $return['msg'] = 'password reseted successfully.';
            } else {
                $return['status'] = 401;
                $return['msg'] = 'unable to reset password please try later.';
            }
        } else {
            $return['status'] = 404;
            $return['msg'] = 'resetpassword token not available.please enter you email.';
        }
        return $return;
    }

    public function updateprofile($id, $user)
    {
        if (isset($user['image'])) {
            $user['vProfilePic'] = $this->uploadimage($user);
        }

        if (isset($user['dtBirthDate'])) {
            $user['dtBirthDate'] = date('Y-m-d', strtotime($user['dtBirthDate']));
            $user['dtUpdatedDate'] = date('Y-m-d H:i:s');
            unset($user['image']);
        }

        $this->_db->where('iUserId', $id);
        if ($this->_db->update(TBL_USERS, $user)) {
            $return['status'] = 200;
            $return['msg'] = 'Updated Successfully.';
            $this->_userid = $id;
            $return['userData'] = $this->getUser_Id();
        } else {
            $return['status'] = 422;
            $return['msg'] = 'Not Updated.';
        }

        return $return;
    }


    public function deleteUser($id, $image)
    {
        $path = "../../uploads/profilePic/";
        if ($image != '' || $image == 'blank.png') {
            $images = glob($path . $image);
            // if(file_exists($path.$image)){
            //     unlink($path.$image);
            // }
            if ($images) {
                @unlink($path . $image);
            }
        }
        $this->_db->where('iUserId', $id);
        $this->_db->delete('tms_user_contact');

        $this->_db->where('iUserId', $id);
        $this->_db->delete('tms_payment');

        $this->_db->where('user_id', $id);
        $this->_db->delete('tms_filemanager');

        $this->_db->where('iUserId', $id);
        $id = $this->_db->delete(TBL_USERS);

        if ($id) {
            $return['status'] = 200;
            $return['msg'] = 'Delete Successfully.';
        } else {
            $return['status'] = 422;
            $return['msg'] = 'Not Deleted.';
        }
        return $return;
    }

    public function internalResourceCheck($info)
    {
        $ret = array();
        $updatedBy_id = $info['updatedBy_id'];
        $updated_id = $info['updated_id'];
        $this->_db->where('iUserId', $updated_id);
        $data = $this->_db->getone('tms_users');
        $edited_Id = $data['iEditedBy'];
        if ($edited_Id == 0) {
            $this->_db->where('iUserId', $updated_id);
            $this->_db->update('tms_users', array('iEditedBy' => $updatedBy_id));
            //echo $this->_db->getLastQuery();exit;

            $this->_db->where('iUserId', $updatedBy_id);
            $data = $this->_db->getone('tms_users');
            $ret['UserName'] = "you";
            $ret['UserId'] = $updatedBy_id;
        } else if ($edited_Id != 0 && $updatedBy_id == $edited_Id) {
            $ret['UserName'] = "you";
            $ret['UserId'] = $updatedBy_id;
        } else {
            $this->_db->where('iUserId', $edited_Id);
            $data = $this->_db->getone('tms_users');
            $ret['UserName'] = $data['vUserName'];
            $ret['UserId'] = $data['iUserId'];
        }
        return $ret;
    }

    public function userUpdate_Byid($id)
    {
        $this->_db->where('iEditedBy', $id);
        $this->_db->update('tms_users', array('iEditedBy' => '0'));
        $result['status'] = 200;
        $msg = 'Ok';
        return $result;
    }

    public function messageUserOneget($id)
    {
        $this->_db->where('iUserId', $id);
        $data = $this->_db->getone('tms_users');
        if ($data) {
            $this->_db->where('is_active', 1);
            $info = $this->_db->getone('tms_email_sign');
        }
        $result['data'] = $data;
        $result['info'] = $info;
        $result['status'] = 200;
        return $result;
    }

    public function AddressTypeget()
    {
        $data = array('1' => 'Home', '2' => 'Office');
        return $data;
    }

    public function viewExternalget($id)
    {

        $data = self::getUserAllfile('iUserId', $id);
        if (isset($data['vResourceType'])) {
            $this->_db->where('iTypeId', $data['vResourceType']);
            $id = $this->_db->getone('tms_user_type');
            $data['vResourceType'] = isset($id['vType']) ? $id['vType'] : '';
        }
        if (isset($data['vResourcePosition'])) {
            $this->_db->where('position_id', $data['vResourcePosition']);
            $id = $this->_db->getone('tms_user_position');
            $data['vResourcePositionName'] = isset($id['position_name']) ? $id['position_name'] : '';

            //$this->_db->where("position_id", explode(',',$data['vResourcePosition']), 'IN');
            //$res = $this->_db->get('tms_user_position');

        }
        if (isset($data['eUserStatus'])) {
            $this->_db->where('status_id', $data['eUserStatus']);
            $id = $this->_db->getone('tms_user_status');
            $data['eUserStatusName'] = isset($id['status_name']) ? $id['status_name'] : '';
        }
        if (isset($data['created_by'])) {
            $created = self::getUserAllfile('iUserId', $data['created_by']);
            $data['createdBy'] = isset($created['vUserName']) ? $created['vUserName'] : '';
        }
        if (isset($data['modified_by'])) {
            $modified = self::getUserAllfile('iUserId', $data['modified_by']);
            $data['modifiedBy'] = isset($modified['vUserName']) ? $modified['vUserName'] : '';
        }
        return $data;
    }

    public function currencyConverts($data)
    {
        if ($data['from'] != $data['to']) {
            $from_Currency = $data['from'];
            $to_Currency = $data['to'];
            $amount = $data['amount'];
            $amount = urlencode($amount);
            $from_Currency = urlencode($from_Currency);
            $to_Currency = urlencode($to_Currency);
            $get = file_get_contents("https://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency");
            $get = explode("<span class=bld>", $get);
            $get = explode("</span>", $get[1]);
            $converted_amount = preg_replace("/[^0-9.]/", null, $get[0]);
            $data = round($converted_amount, 2);
            return $data;
        }
    }

    //city wise time zone get
    public function cityTimeZoneget($id)
    {
        //$id = 'China';
        $location = urlencode($id);
        $geoapiKey = '34b84344790146fe81584b20f1376807';
        //$url = "http://maps.googleapis.com/maps/api/geocode/json?address={$location}&sensor=false";
        $url = "https://api.ipgeolocation.io/timezone?apiKey=" . $geoapiKey . "&location=" . $location;
        $data = file_get_contents($url);

        // Get the lat/lng out of the data
        $data = json_decode($data);

        if (!$data)
            return false;
        if (!is_object($data->geo))
            return false;
        if (!is_numeric($data->geo->latitude))
            return false;
        if (!is_numeric($data->geo->longitude))
            return false;
        $lat = $data->geo->latitude;
        $lng = $data->geo->longitude;

        $dt = new DateTimeZone($data->timezone);
        $time = explode(":", $data->time_24);

        $hours = $time[0];
        $minutes = $time[1];
        date_default_timezone_set($data->timezone);
        $info['timeZone'] = "(GMT " . $hours . '.' . $minutes . ') ' . $data->timezone;
        $info['timeZoneCity'] = $id;
        return $info;
    }
    // city vise info old api
    public function cityTimeZoneget_oldAPI($id)
    {
        //$id = 'China';
        $location = urlencode($id);
        $url = "http://maps.googleapis.com/maps/api/geocode/json?address={$location}&sensor=false";
        $data = file_get_contents($url);

        // Get the lat/lng out of the data
        $data = json_decode($data);
        //print_r($data);exit;
        if (!$data)
            return false;
        if (!is_array($data->results))
            return false;
        if (!isset($data->results[0]))
            return false;
        if (!is_object($data->results[0]))
            return false;
        if (!is_object($data->results[0]->geometry))
            return false;
        if (!is_object($data->results[0]->geometry->location))
            return false;
        if (!is_numeric($data->results[0]->geometry->location->lat))
            return false;
        if (!is_numeric($data->results[0]->geometry->location->lng))
            return false;
        $lat = $data->results[0]->geometry->location->lat;
        $lng = $data->results[0]->geometry->location->lng;
        // get the API response for the timezone
        $timestamp = time();
        $timezoneAPI = "https://maps.googleapis.com/maps/api/timezone/json?location={$lat},{$lng}&sensor=false&timestamp={$timestamp}";
        $response = file_get_contents($timezoneAPI);
        if (!$response)
            return false;
        $response = json_decode($response);
        if (!$response)
            return false;
        if (!is_object($response))
            return false;
        if (!is_string($response->timeZoneId))
            return false;

        $dt = new DateTime(null, new DateTimeZone($response->timeZoneId));
        $offset = $dt->getOffset() / 60 / 60; // 11

        $hours = intval($offset);
        $minutes = str_pad((string) ($offset - $hours) * 60, 2, '0', STR_PAD_RIGHT);

        date_default_timezone_set($response->timeZoneId);
        $info['timeZone'] = "(GMT " . $hours . '.' . $minutes . ') ' . $response->timeZoneName;
        $info['timeZoneCity'] = $id;
        return $info;
    }
    public function getTimeZoneByLatLong($data)
    {
        $ApiKeys = [
            'AIzaSyDEnCDLGH8q4uDmLSROcNcqKQNYQKfwLYM',
            'AIzaSyDB7UY5kOPPgonOM4TAw46fdxFKcze6a6g',
            'AIzaSyATslWNHs_MGeUkpE78skLsItN8olAOC5c',
            'AIzaSyATMPdj9crO_Oy1A501KfEv7XLWDGo7U5k',
            'AIzaSyDc4_2QvdgrCLP9D7pNilEl28JA92X4kpQ'
        ];

        $pick_random_key = array_rand($ApiKeys, 1);

        $getKey = $ApiKeys[$pick_random_key];


        $lat = $data['lat'];
        $lng = $data['long'];
        $timestamp = time();

        $timezoneAPI = "https://maps.googleapis.com/maps/api/timezone/json?location={$lat},{$lng}&timestamp={$timestamp}&key=AIzaSyB_Fcb_a2y6MfVYDMGjFXoFbN9WEttDdl8";

        $response = file_get_contents($timezoneAPI);

        if (!$response)
            return false;
        $response = json_decode($response);
        if (!$response)
            return false;
        if (!is_object($response))
            return false;
        if (!is_string($response->timeZoneId))
            return false;

        $dt = new DateTime(null, new DateTimeZone($response->timeZoneId));
        $offset = $dt->getOffset() / 60 / 60; // 11

        $hours = intval($offset);
        $minutes = str_pad((string) ($offset - $hours) * 60, 2, '0', STR_PAD_RIGHT);

        date_default_timezone_set($response->timeZoneId);
        $info['timeZone'] = "(GMT " . $hours . '.' . $minutes . ') ' . $response->timeZoneName;
        //$info['timeZoneCity'] = $id;
        return $info;
    }

    public function userProfileNumberGet($id)
    {
        $this->_db->where('iFkUserTypeId', $id);
        $this->_db->where('iResourceNumber', '', '!=');
        $this->_db->orderBy("iUserId", "Desc");
        $data = $this->_db->get('tms_users', 1);
        if ($data) {
            $return = $data[0]['iResourceNumber'] + 1;
        } else {
            $return = 1;
        }
        return $return;
    }

    public function getUserById($id)
    {
        $this->_db->where('iUserId', $id);
        $result = $this->_db->getOne('tms_users');

        $this->_db->where('iTypeId', $result['vResourceType']);
        $result1 = $this->_db->getOne('tms_user_type');
        return $result1;
    }

    public function updateUserTabsortorder__(Request $request, Response $response, $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();

        $updated = $this->db->update('tms_users', ['tab_sortedorder' => $data['tab_sortedorder']], ['iUserId' => $id]);

        if ($updated) {
            return JsonResponse::respond($response, null, 200, 'Tab sort order updated successfully');
        }

        return JsonResponse::respond($response, null, 400, 'Failed to update tab sort order');
    }

    //public function getUserDataById($id)
    public function getUserDataById__(Request $request, Response $response, $args): Response
    {
        $id = $args['id'];

        $this->_db->where('iUserId', $id);
        $this->_db->join('tms_tax tx', 'tx.tax_id = tp.tax_rate', 'LEFT');
        $userPaymentData = $this->_db->getOne('tms_payment tp', 'tp.*, tx.tax_percentage');

        if ($userPaymentData) {
            $result = [
                'userPaymentData' => $userPaymentData
            ];
            return JsonResponse::respond($response, $result, 200, 'User payment data retrieved successfully');
        } else {
            return JsonResponse::respond($response, null, 404, 'User payment data not found');
        }
    }

    public function changeUserStatus($id, $data)
    {
        if ($data['eUserStatus'] == 4) {
            $this->_db->where('project_coordinator', $id);
            $this->_db->orWhere('project_manager', $id);
            $this->_db->orWhere('QA_specialist', $id);
            $getAdssigned = $this->_db->getOne('tms_customer'); //for internal user

            $this->_db->where('resource', $id);
            $getAdssigned1 = $this->_db->getOne('tms_summmery_view'); //for external user
            if ($getAdssigned || $getAdssigned1) {
                $return['status'] = 422;
                $return['msg'] = "You can't set Inactive for assigned user.";
            } else {
                $this->_db->where('iUserId', $id);
                $updateStatus = $this->_db->update('tms_users', $data);

                if ($updateStatus) {
                    if ($data['eUserStatus'] == 3) {

                        $msg = 'User activated Successfully.';
                    } else if ($data['eUserStatus'] == 4) {

                        $msg = 'User Inactivated Successfully.';
                    }

                    $return['status'] = 200;
                    $return['msg'] = $msg;
                } else {
                    $return['status'] = 422;
                    $return['msg'] = 'Error please try later.';
                }
            }
        } else {
            $this->_db->where('iUserId', $id);
            $updateStatus = $this->_db->update('tms_users', $data);

            if ($updateStatus) {
                if ($data['eUserStatus'] == 3) {
                    $msg = 'User activated Successfully.';
                } else if ($data['eUserStatus'] == 4) {
                    $msg = 'User Inactivated Successfully.';
                }

                $return['status'] = 200;
                $return['msg'] = $msg;
            } else {
                $return['status'] = 422;
                $return['msg'] = 'Error please try later.';
            }
        }
        return $return;
    }

    public function saveFvMenu($data)
    {
        $this->_db->where('iUserId', $data['iUserId']);
        $getMemuByuser = $this->_db->getOne('tms_favourite_menu');
        if (!$getMemuByuser) {
            $data['created_date'] = date('Y-m-d H:i:s');
            $data['updated_date'] = date('Y-m-d H:i:s');

            $insertMenu = $this->_db->insert('tms_favourite_menu', $data);
            if ($insertMenu) {
                $return['status'] = 200;
                $return['msg'] = 'Menu successfully added to favourite.';

                $this->_db->where('iUserId', $data['iUserId']);
                $getMemuByuser = $this->_db->getOne('tms_favourite_menu');

                $return['data'] = $getMemuByuser;
            } else {
                $return['status'] = 422;
                $return['msg'] = 'error please try later.';
            }
        } else {
            $data['updated_date'] = date('Y-m-d H:i:s');

            $this->_db->where('iUserId', $data['iUserId']);
            $insertMenu = $this->_db->update('tms_favourite_menu', $data);

            if ($insertMenu) {
                $return['status'] = 200;
                $return['msg'] = 'Menu successfully added to favourite.';

                $this->_db->where('iUserId', $data['iUserId']);
                $getMemuByuser = $this->_db->getOne('tms_favourite_menu');

                $return['data'] = $getMemuByuser;
            } else {
                $return['status'] = 422;
                $return['msg'] = 'error please try later.';
            }
        }

        return $return;
    }
    public function getFvMenu($id)
    {
        $this->_db->where('iUserId', $id);
        $getMemuByuser = $this->_db->getOne('tms_favourite_menu');
        if ($getMemuByuser) {
            $return['status'] = 200;
            $return['msg'] = 'success';
            $return['data'] = $getMemuByuser;
        } else {
            $return['status'] = 422;
            $return['msg'] = 'error';
            $return['data'] = '';
        }

        return $return;
    }
    public function mailTest($data)
    {
        $this->_db->where('template_id', 8);
        $emailTemplate = $this->_db->getOne('tms_email_templates');
        //echo '<pre>'; print_r($emailTemplate); echo '</pre>';exit;

        $search_array = array("[PROJECTNUMBER]", "[CLIENTNAME]", '[ORDERDATE]');
        $replace_array = array('KNH170001', 'Vijay gohil', '2018-09-07');

        $html = str_replace($search_array, $replace_array, $emailTemplate['template_content']);

        $this->_mailer = new PHPMailer();
        //$this->_mailer = 'ISO-8859-1';
        $this->_mailer->IsSMTP();

        $this->_mailer->SMTPAuth = "true";
        //$this->_mailer->SMTPSecure = 'tls';
        $this->_mailer->Host = "ssl://smtp.gmail.com";
        $this->_mailer->Port = "465";

        // $this->_mailer->Username = "info@worldee.co.uk";
        // $this->_mailer->Password = "1nfoW0rld33P@$$w0rd";
        $this->_mailer->Username = SMTP_EMAIL_USER;
        $this->_mailer->Password = SMTP_EMAIL_PASSWORD;

        $this->_mailer->From = "Kanhasoft.com";
        $this->_mailer->FromName = "TMS Admin";
        //$this->_mailer->Subject = $emailTemplate['template_subject'];
        $this->_mailer->Subject = 'Tesing Email SMTP';
        $this->_mailer->Body = 'Just testing Email';
        $this->_mailer->WordWrap = 50;
        $this->_mailer->AddAddress('anil.kanhasoft@gmail.com');
        $this->_mailer->IsHTML(true);
        if ($this->_mailer->Send()) { //output success or failure messages
            $return['status'] = 200;
            $return['msg'] = 'Thank you for your email';
        } else {
            $return['status'] = 400;
            $return['msg'] = 'Could not send mail!';
        }

        return $return;
    }
    public function activateAccount($data)
    {
        if ($data && isset($data['userId'])) {
            $this->_db->where('iUserId', $data['userId']);
            $data = $this->_db->getone('tms_users');
        } else {
            $this->_db->where('activation_token', $data['activationToken']);
            $data = $this->_db->getone('tms_users');
        }
        if ($data) {
            if ($data['activation_status'] == 1) {
                $return['status'] = 401;
                $return['msg'] = 'Your account already activated.';
            } else {
                $updateData['activation_status'] = 1;
                $this->_db->where('iUserId', $data['iUserId']);
                $id = $this->_db->update('tms_users', $updateData);
                if ($id) {
                    $return['status'] = 200;
                    $return['msg'] = 'You account activated successfully.';
                }
            }
        } else {
            $return['status'] = 401;
            $return['msg'] = 'Your activation token not available,please contact administrator.';
        }
        return $return;
    }

    // Specialization Data
    public function getAllSpecialization()
    {
        $this->_db->where("is_active", 1);
        return $results = $this->_db->get('tms_specialization');
    }

    public function sendAcountActivationlink($user)
    {
        //Sending registation email to users email address
        //$userName = $user['vUserName'];
        $userName = isset($user['vFirstName']) ? $user['vFirstName'] : '';

        $registrationLink = ACTIVATION_URL . '#/activation/' . $user['activation_token'];
        $registrationLink = '<a href="' . ACTIVATION_URL . '#/activation/' . $user['activation_token'] . '"> ' . $registrationLink . ' </a>';

        $userEmail = $user['vEmailAddress'];
        $userPassword = $user['org_pass'];
        $businessManagerLink = '';

        $this->_db->where('template_id', 8);
        $emailTemplateRegistration = $this->_db->getOne('tms_email_templates');
        $search_array = array("[USERNAME]", "[REGISTRATIONLINK]", '[USEREMAIL]', '[USERPASSWORD]', '[BUSINESSMANAGERLINK]');

        $replace_array = array($userName, $registrationLink, $userEmail, $userPassword, $businessManagerLink);

        $html = str_replace($search_array, $replace_array, $emailTemplateRegistration['template_content']);

        $to_name = $user['vFirstName'];
        $send_fn = new functions();
        $response = $send_fn->send_email_smtp($userEmail, $to_name, $cc = '', $bcc = '', $emailTemplateRegistration['template_subject'], $html, $attachments = '');
        // End mailjet
        if ($response && $response['status'] == 200) {
            $return['status'] = 200;
            $return['msg'] = 'Email has been sent successfully!';
        } else {
            $return['status'] = 401;
            $return['msg'] = 'Could not send mail!';
        }
        return $return;
    }

    // Linguist Profile Import csv
    public function savelinguistCsvProfile($userData)
    {

        foreach ($userData as $user) {
            $resourceNumber = self::userProfileNumberGet(2);
            $user['iResourceNumber'] = str_pad($resourceNumber, 4, '0', STR_PAD_LEFT);
            $propSoftware = $user['propSoftware'];
            $propHardware = $user['propHardware'];
            $propCatTools = $user['propCatTools'];
            $vPaymentInfo = $user['vpaymentInfo'];
            unset($user['propSoftware']);
            unset($user['propHardware']);
            unset($user['propCatTools']);
            unset($user['vpaymentInfo']);
            $activationToken = sha1(mt_rand(10000, 99999) . time());
            //$emailPassToSend =  $user['vPassword'];
            $user['dtUpdatedDate'] = date('Y-m-d H:i:s');
            $proptDataIns['type'] = 1;
            $proptDataIns['created_date'] = date('Y-m-d H:i:s');
            $proptDataIns['updated_date'] = date('Y-m-d H:i:s');

            $this->_username = $user['vUserName'];
            $this->_useremail = $user['vEmailAddress'];

            if ($this->getUser_Username() && $this->getUser_Email()) {
                $ChaeckUserId = $this->getUser_Username();
                $updatedID = $ChaeckUserId['iUserId'];
                if ($updatedID) {
                    $this->_db->where('iUserId', $updatedID);
                    $this->_db->update(TBL_USERS, $user);

                    $proptData['updated_date'] = date('Y-m-d H:i:s');
                    if ($propSoftware != '') {
                        $proptData['value_id'] = $propSoftware;
                        $this->_db->where('user_id', $updatedID);
                        $this->_db->where('property_id', 1);
                        $this->_db->update('tms_user_property', $proptData);
                    }
                    if ($propHardware != '') {
                        $proptData['value_id'] = $propHardware;
                        $this->_db->where('user_id', $updatedID);
                        $this->_db->where('property_id', 10);
                        $this->_db->update('tms_user_property', $proptData);

                        $this->_db->where('user_id', $updatedID);
                        $this->_db->where('property_id', 10);
                        $propRec10 = $this->_db->get('tms_user_property');
                    }
                    if ($propCatTools != '') {
                        $proptData['value_id'] = $propCatTools;
                        $this->_db->where('user_id', $updatedID);
                        $this->_db->where('property_id', 11);
                        $this->_db->update('tms_user_property', $proptData);

                        $this->_db->where('user_id', $updatedID);
                        $this->_db->where('property_id', 11);
                        $isExistProp = $this->_db->get('tms_user_property');
                        if (!$isExistProp) {
                            $proptDataIns['property_id'] = 11; // 'Cat Tools' property id from tms_property tabel
                            $proptDataIns['value_id'] = $propCatTools;
                            $proptDataIns['user_id'] = $updatedID;
                            $hDataIns = $this->_db->insert('tms_user_property', $proptDataIns);
                        }
                    }

                    if ($vPaymentInfo != '') {
                        $payData['vPaymentInfo'] = $vPaymentInfo;
                        $payData['dtUpdatedDate'] = date('Y-m-d H:i:s');
                        $this->_db->where('iUserId', $updatedID);
                        $this->_db->where('iType', 1);
                        $this->_db->where('iClientId', 0);
                        $this->_db->update('tms_payment', $payData);
                    }
                }
                $return['status'] = 200;
                $return['msg'] = 'User record updated.';
                //$return['msg'] = 'User name OR email already exists.';
            } else {
                $user['activation_token'] = $activationToken;
                // User Data
                $user['iFkUserTypeId'] = 2;
                $user['csv_import'] = 1;
                $user['eUserStatus'] = 3;
                $user['vResourceType'] = 4;
                $user['vPassword'] = 123; // static for import csv data
                $user['org_pass'] = $user['vPassword'];
                $user['vPassword'] = md5($user['vPassword']);
                $user['vProfilePic'] = 'user-icon.png';
                //$user['dtBirthDate'] = date('Y-m-d', strtotime($user['dtBirthDate']));
                //$user['dtCreationDate'] = date('Y-m-d H:i:s')/*$user['dtCreationDate']*/;
                $user['dtCreatedDate'] = date('Y-m-d H:i:s');

                $id = $this->_db->insert(TBL_USERS, $user);
                // echo $this->_db->getLastQuery();

                if ($id) {
                    $data['created_date'] = date('Y-m-d H:i:s');
                    $data['updated_date'] = date('Y-m-d H:i:s');
                    $data['user_id'] = $id;
                    $data['name'] = 'external-' . $user['iResourceNumber'];
                    $exResourceFmanager = $this->_db->insert('tms_filemanager', $data);
                    //Inserting Default Folders in External Resource File Manager
                    if ($exResourceFmanager) {
                        $defaultFolderArray = array('Data', 'Projects', 'Invoice');
                        foreach ($defaultFolderArray as $key => $value) {
                            $info['name'] = $value;
                            $info['is_default_folder'] = 1;
                            if ($value == 'Projects') {
                                $info['is_ex_project_folder'] = 1;
                            } else {
                                $info['is_ex_project_folder'] = 0;
                            }
                            $info['parent_id'] = $exResourceFmanager;
                            $this->_db->insert('tms_filemanager', $info);
                        }
                    }

                    $proptDataIns['user_id'] = $id;
                    if ($propSoftware != '') {
                        $proptDataIns['property_id'] = 1; // 'Software' property id from tms_property tabel
                        $proptDataIns['value_id'] = $propSoftware;
                        $sDataIns = $this->_db->insert('tms_user_property', $proptDataIns);
                    }
                    if ($propHardware != '') {
                        $proptDataIns['property_id'] = 10; // 'Hardware' property id from tms_property tabel
                        $proptDataIns['value_id'] = $propHardware;
                        $hDataIns = $this->_db->insert('tms_user_property', $proptDataIns);
                    }

                    if ($propCatTools != '') {
                        $proptDataIns['property_id'] = 11; // 'Cat Tools' property id from tms_property tabel
                        $proptDataIns['value_id'] = $propCatTools;
                        $hDataIns = $this->_db->insert('tms_user_property', $proptDataIns);
                    }

                    if ($vPaymentInfo != '') {
                        $payData['iUserId'] = $id;
                        $payData['iType'] = 1;
                        $payData['iClientId'] = 0;
                        $payData['vPaymentInfo'] = $vPaymentInfo;
                        $payData['dtCreatedDate'] = date('Y-m-d H:i:s');
                        $payData['dtUpdatedDate'] = date('Y-m-d H:i:s');
                        $hDataIns = $this->_db->insert('tms_payment', $payData);
                    }

                    $return['status'] = 200;
                    $return['msg'] = 'Inserted Successfully.';
                    $return['iUserId'] = $id;
                    $this->_userid = $id;
                    $return['userData'] = $this->getUser_Id();
                } else {
                    $return['status'] = 422;
                    $return['msg'] = 'Not inserted.';
                }
            }
        }
        return $return;
    }

    public function saveuserProfileSignUp($user)
    {
        $activationToken = sha1(mt_rand(10000, 99999) . time());
        $emailPassToSend = $user['vPassword'];
        $user['activation_token'] = $activationToken;
        $this->_username = $user['vUserName'];
        $this->_useremail = $user['vEmailAddress'];
        if ($this->getUser_Username()) {
            $return['status'] = 422;
            $return['msg'] = 'User name already exists.';
        } else if ($this->getUser_Email()) {
            $return['status'] = 422;
            $return['msg'] = 'Email address already exists.';
        } else if (isset($user['vSecondaryEmailAddress']) && ($user['vEmailAddress'] == $user['vSecondaryEmailAddress'])) {
            $return['status'] = 422;
            $return['msg'] = 'Secondary Email Address should be different.';
        } else {
            $resourceNumber = self::userProfileNumberGet(2);
            $user['iResourceNumber'] = str_pad($resourceNumber, 4, '0', STR_PAD_LEFT);

            $user['iFkUserTypeId'] = '2';
            $user['vPassword'] = md5($user['vPassword']);
            $user['vProfilePic'] = isset($user['image']) ? $this->uploadimage($user) : 'user-icon.png';
            $user['dtBirthDate'] = ($user['dtBirthDate'] == 'Invalid date' || $user['dtBirthDate'] == '') ? '0000-00-00' : $user['dtBirthDate'];
            $user['dtBirthDate'] = date('Y-m-d', strtotime($user['dtBirthDate']));
            $user['dtCreationDate'] = date('Y-m-d H:i:s')/*$user['dtCreationDate']*/ ;
            $user['dtCreatedDate'] = date('Y-m-d H:i:s');
            $user['dtUpdatedDate'] = date('Y-m-d H:i:s');
            if (isset($user['image']))
                unset($user['image']);
            $id = $this->_db->insert(TBL_USERS, $user);
            if ($id) {
                $data['created_date'] = date('Y-m-d H:i:s');
                $data['updated_date'] = date('Y-m-d H:i:s');
                $data['user_id'] = $id;
                $data['name'] = 'external-' . $user['iResourceNumber'];
                $exResourceFmanager = $this->_db->insert('tms_filemanager', $data);

                //Inserting Default Folders in External Resource File Manager
                if ($exResourceFmanager) {
                    $defaultFolderArray = array('Data', 'Projects', 'Invoice');
                    foreach ($defaultFolderArray as $key => $value) {
                        $info['name'] = $value;
                        $info['is_default_folder'] = 1;
                        if ($value == 'Projects') {
                            $info['is_ex_project_folder'] = 1;
                        } else {
                            $info['is_ex_project_folder'] = 0;
                        }
                        $info['parent_id'] = $exResourceFmanager;
                        $this->_db->insert('tms_filemanager', $info);
                    }
                }

                //Sending registation email to users email address
                $userName = $user['vFirstName'];
                $registrationLink = ACTIVATION_URL . '#/activation/' . $activationToken;
                $registrationLink = '<a href="' . ACTIVATION_URL . '#/activation/' . $activationToken . '"> ' . $registrationLink . ' </a>';

                $userEmail = $user['vEmailAddress'];
                $userPassword = $emailPassToSend;
                $businessManagerLink = '';

                $this->_db->where('template_id', 8);
                $emailTemplateRegistration = $this->_db->getOne('tms_email_templates');
                $search_array = array("[USERNAME]", "[REGISTRATIONLINK]", '[USEREMAIL]', '[USERPASSWORD]', '[BUSINESSMANAGERLINK]');

                $replace_array = array($userName, $registrationLink, $userEmail, $userPassword, $businessManagerLink);
                $html = str_replace($search_array, $replace_array, $emailTemplateRegistration['template_content']);

                $jobDetail = new jobs_detail();
                $jobDetail->sendEmail($userEmail, $emailTemplateRegistration['template_subject'], $html);

                $return['status'] = 200;
                $return['msg'] = 'Inserted Successfully.';
                $return['iUserId'] = $id;
                $this->_userid = $id;
                $return['userData'] = $this->getUser_Id();
            } else {
                $return['status'] = 422;
                $return['msg'] = 'Not inserted.';
            }
        }
        return $return;
    }

    // Sidebar menu structure tree view
    public function buildTree(array $elements, $parentId = 0)
    {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::buildTree($elements, $element['id']);

                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public function getTreeMenu()
    {
        $dataArr = $this->_db->get('tms_tree_menu');
        $data = $this->_db->get('tms_tree_menu');
        $i = 0;
        foreach ($data as $data1) {
            //$data[$i]['children'] = [];
            //if($data1['parent_id'] == 0){
            $data[$i]['children'] = self::buildTree($dataArr, $data[$i]['id']);
            //}
            if ($data1['parent_id'] != 0) {
                unset($data[$i]);
            }
            $i++;
        }
        //print_r($data);
        //exit;
        //return $data;
        $payload = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function updateAbscentDate($id, $user)
    {
        $user['dtUpdatedDate'] = date('Y-m-d H:i:s');
        $this->_db->where('iUserId', $id);
        if ($this->_db->update('tms_users', $user)) {
            $return['status'] = 200;
            $return['msg'] = 'Updated Successfully.';
        } else {
            $return['status'] = 422;
            $return['msg'] = 'Not Updated.';
        }

        return $return;
    }

    public function changepassword($user)
    {
        $userId = $user["userId"];
        $this->_db->where("iUserId", $userId);
        $results = $this->_db->getOne(TBL_USERS);

        if ($this->_db->count > 0) {
            $oldPassword = $user["vOldPassword"];
            $vNewPassword = $user["vNewPassword"];
            if ($results && !empty($results)) {
                $this->_db->where("org_pass", $oldPassword)->where("iUserId", $userId);
                $passwordresults = $this->_db->getOne(TBL_USERS);
                if ($passwordresults && !empty($passwordresults)) {
                    $this->_db->where("iUserId", $userId);
                    $this->_db->update('tms_users', array("vPassword" => md5($vNewPassword), "org_pass" => $vNewPassword, "dtUpdatedDate" => date('Y-m-d H:i:s')));
                    $return['status'] = 200;
                    $return['match'] = true;
                    $return['msg'] = 'Password has been updated';
                } else {
                    $return['status'] = 200;
                    $return['match'] = false;
                    $return['msg'] = 'Old password is wrong';
                }
            } else {
                $return['status'] = 422;
                $return['match'] = false;
                $return['msg'] = 'User not found';
            }
        } else {
            $return['status'] = 422;
            $return['match'] = false;
            $return['msg'] = 'You are not Registered in System';
        }
        return $return;
    }

    public function getAllsentEmail()
    {

        $send_fn = new functions();
        $response = $send_fn->mailjet_get_all_email();
        if ($response) {
            $return = $response;
            $return['status'] = 200;
            $return['msg'] = 'Updated Successfully.';
        } else {
            $return['status'] = 422;
            $return['msg'] = 'Not Updated.';
        }

        return $return;
    }

    public function getMultipleReourse($data)
    {
        $array = $data['resourceIds'];
        //$resIds = implode(", ", $data['resourceIds']);
        // $this->_db->where('eUserStatus',3);
        $this->_db->where('iUserId', $array, 'IN');
        $this->_db->where('activation_status', 1);
        $data = $this->_db->get('tms_users');
        //$data = $this->_db->rawQuery("SELECT * FROM tms_users WHERE activation_status=1  AND eUserStatus = 3 AND (FIND_IN_SET($type,vResourcePosition) > 0) ");
        //echo $this->_db->getLastQuery();
        $result['data'] = $data;
        $result['status'] = 200;
        return $result;
    }

    public function updateUserTabsortorder($id, $data)
    {
        $postData = [];
        $postData['tab_sortedorder'] = $data->tab_sortedorder;
        $this->_db->where('iUserId ', $id);
        $this->_db->update('tms_users', $postData);
        $result['status'] = 200;
        return $result;
    }

    // user dashboard tabs permission
    public function getTabsPermision($id, $postData)
    {
        $this->_db->where('is_active', 1);
        $tabList = $this->_db->get('tms_dashboard_tabs');
        $this->_db->where('iUserId ', $id);
        $userData = $this->_db->get('tms_users');

        // Assuming $user['tabPermission'] is the updated permission data from the post
        if (isset($user['tabPermission'])) {
            // Decode the existing tab_sortedorder and the new tabPermission
            $tabSortedorder = json_decode($userData['tab_sortedorder'], true);
            $tabPermission = json_decode($postData['tabPermission'], true);

            // Get the active tab permissions (tabs the user is allowed to see)
            $activeTabs = array_keys(array_filter($tabPermission));

            // Step 1: Remove tabs from tab_sortedorder that are no longer in tabPermission
            $tabSortedorder = array_filter($tabSortedorder, function ($tab) use ($activeTabs) {
                // Keep only those tabs where the tabPermissionValue is in the activeTabs list
                return in_array($tab['tabPermissionValue'], $activeTabs);
            });

            // Step 2: Add any new tabs that are in tabPermission but not in tab_sortedorder
            foreach ($activeTabs as $permissionValue) {
                // Check if the tab exists in the current tab_sortedorder
                $tabExists = false;
                foreach ($tabSortedorder as $tab) {
                    if ($tab['tabPermissionValue'] == $permissionValue) {
                        $tabExists = true;
                        break;
                    }
                }

                if (!$tabExists) {
                    // Find the tab info based on the permissionValue
                    $tabInfo = null;
                    foreach ($tabList as $tab) {
                        if ($tab['tabPermissionValue'] == $permissionValue) {
                            // Remove unwanted fields like is_active and created_date
                            $tabInfo = array(
                                'tabName' => $tab['tabName'],
                                'tabClassName' => $tab['tabClassName'],
                                'tabPermissionValue' => $tab['tabPermissionValue'],
                                'projectScoopCount' => $tab['projectScoopCount'],
                                'totalItems' => $tab['totalItems'],
                                'totalPages' => $tab['totalPages'],
                                'pageShowRec' => $tab['pageShowRec'],
                                'tabIndexId' => $tab['tabIndexId']
                            );
                            break;
                        }
                    }
                    // If tabInfo is found, add it to the sorted order
                    if ($tabInfo) {
                        $tabSortedorder[] = $tabInfo;
                    }
                }
            }
            $updatedTabSortedorder = json_encode($tabSortedorder);
            $userData['tab_sortedorder'] = $updatedTabSortedorder;
        }
    }
}
