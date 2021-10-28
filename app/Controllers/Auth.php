<?php

namespace App\Controllers;

class Auth extends FrontController {

    public function __construct() {
        parent::__construct();
        $db = db_connect();
    }

    /**
     * Google login function
     * @access public
     * @description helper function
     * @author Mahek
     */
    public function google_login() {
        $googleauth = new \App\Libraries\Googleauth();
        $user = $googleauth->getUser();

        if ($user) {
            $full_name = $user['name'];
            $full_name_arr = explode(' ', $full_name);

            $userData['email'] = $user['email'];
            $userData['google_id'] = $user['id'];
            $userData['first_name'] = $full_name_arr[0];
            $userData['last_name'] = isset($full_name_arr[1]) ? $full_name_arr[1] : '';

            return $this->social_registration($userData);
        } else {
            //return redirect()->to(base_url());
            die('Loading...');
        }
    }

    /**
     * Social registration function
     * @access public
     * @description helper function
     * @author Mahek
     */
    function social_registration($userData = array()) {
        if (!empty($userData)) {
            if (isset($userData['id'])) {
                unset($userData['id']);
            }
            $condition['email'] = $userData['email'];
            $this->general->set_table('user');
            $already_registered = $this->general->get('*', $condition);

            if ($already_registered) {
                $updateCondition['user_id'] = $userId = $already_registered[0]['user_id'];
                $userData['updated_time'] = DATE_TIME;
                $this->general->update($userData, $updateCondition);
            } else {
                $userData['status'] = STATUS_ACTIVE;
                $userData['created_time'] = DATE_TIME;
                $userId = $this->general->save($userData);
            }

            $user_details_condition['user_id'] = $userId;
            $userDetails = $this->general->get('*', $user_details_condition);

            $this->session->set('user_logged_in', $userDetails[0]);
            return redirect()->to(base_url(USER_FOLDER_NAME . '/dashboard'));
        } else {
            return redirect()->to(base_url());
        }
    }

    /**
     * Logout : Clear sesssion
     * @access public
     * @return true or false (redirect to view)
     * @author by Rajnish Savaliya (TheFoxLab.com)
     */
    public function logout() {
        $user_id = session()->get('user_logged_in');
        if ($user_id != '' || $user_id != null) {
            session()->destroy();
            $this->set_msg("Successfully Logout");

            $googleauth = new \App\Libraries\Googleauth();
            $user = $googleauth->logout();            
        }
        return redirect()->to(base_url());
    }

}

?>