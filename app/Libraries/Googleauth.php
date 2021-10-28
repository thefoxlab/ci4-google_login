<?php

namespace App\Libraries;

require './vendor/autoload.php';
/* use Google_Client;
  use Google\Auth\OAuth2; */

use Google\Client as Google_Client;
use Google_Service_Oauth2;

class Googleauth {

    private $client;
    private $oauth2;
    private $session;

    public function __construct() {
        $this->session = \Config\Services::session();
        
        $this->client = new Google_Client();

        $this->client->setApplicationName(GOOGLE_APP_NAME);
        $this->client->setClientId(GOOGLE_CLIENT_ID);
        $this->client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $this->client->setRedirectUri(base_url('auth/google_login'));
        $this->client->setDeveloperKey('');
        $this->client->addScope(['email','profile']);
        
        //echo "<pre>";print_r($this->client);exit;

        $this->oauth2 = new Google_Service_Oauth2($this->client);

        /* $this->oauth2 = new OAuth2();
          $this->oauth2->setApplicationName(GOOGLE_APP_NAME);
          $this->oauth2->setClientId(GOOGLE_CLIENT_ID);
          $this->oauth2->setClientSecret(GOOGLE_CLIENT_SECRET);
          $this->oauth2->setRedirectUri('auth/google_login');
          $this->oauth2->setDeveloperKey('');
          $this->oauth2 = new OAuth2($this->client);
         */


        //echo "<pre>";print_r($this->oauth2);exit;

        /* Token */
        if (isset($_GET['code'])) {
            $this->client->authenticate($_GET['code']);
            $this->session->set('token', $this->client->getAccessToken());
        }

        if ($this->session->get('token')) {
            $this->client->setAccessToken($this->session->get('token'));
        }
    }

    public function getUser() {
        if ($this->client->getAccessToken()) {
            $user = $this->oauth2->userinfo->get();
            
            return $user;
        } else {
            $authUrl = $this->client->createAuthUrl();
            //echo "<pre>";print_r($authUrl);exit;
            //redirect($authUrl);
            /*return redirect()->to($authUrl);*/
            header("Location: ".$authUrl);
        }
    }

    public function logout() {
        $this->session->get('token');
        $this->client->revokeToken();
    }
}
