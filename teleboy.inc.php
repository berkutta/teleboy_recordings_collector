<?php

class Teleboy {
    private $curl;

    private $config;

    private $tvapikey;
    private $tvapiuserid;
    private $tvapisessionid;

    function __construct() {
        $this->config = include("config.inc.php");
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:126.0) Gecko/20100101 Firefox/126.0');
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            "x-teleboy-device-os: linux",
            "x-teleboy-device-type: desktop",
            "x-teleboy-version: 2.0"
        ));
    }
   
    public function login() {
        $postValues = array(
            'login' => $this->config["teleboy_username"],
            'password' => $this->config["teleboy_password"],
            'keep_login' => "0"
        );

        curl_setopt($this->curl, CURLOPT_URL, "https://www.teleboy.ch/login_check");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($postValues));
        $login_output = curl_exec($this->curl);
        if(curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200) {
            echo "Error code: ".curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            throw new Exception('Login failed');
            return;
        }

        preg_match_all('/tvapiKey:                   \'(.*)\',\n/', $login_output, $tvapikey);
        preg_match_all('/\.setId\((.*)\)\n/', $login_output, $tvapiuserid);
        preg_match_all('/\.setSessionId\(\'(.*)\'\)/', $login_output, $tvapisessionid);

        $this->tvapikey = $tvapikey[1][0];
        $this->tvapiuserid = $tvapiuserid[1][0];
        $this->tvapisessionid = $tvapisessionid[1][0];

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            "x-teleboy-apikey: ".$this->tvapikey,
            "x-teleboy-session: ".$this->tvapisessionid,
            "x-teleboy-device-os: linux",
            "x-teleboy-device-type: desktop",
            "x-teleboy-version: 2.0",
          ));

        // Reset cURL POST fields
        // This also ensures to not send any Content-Length headers which might cause a 400 response
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, NULL);
        curl_setopt($this->curl, CURLOPT_POST, false);
    }

    public function recordings() {
        curl_setopt($this->curl, CURLOPT_URL, "https://web-api.teleboy.ch/users/".$this->tvapiuserid."/recordings?skip=0&type=ready&query=&genre=0&sort=date&desc=1&limit=30");
        
        $recordings_output = curl_exec($this->curl);
        if(curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200) {
            throw new Exception('Request failed');
            return;
        }
        return json_decode($recordings_output)->data->items;
    }

    public function recording_max_profile($recordingid) {
        curl_setopt($this->curl, CURLOPT_URL, "https://web-api.teleboy.ch/users/".$this->tvapiuserid."/recordings/".$recordingid."/download");
    
        $recording_output = curl_exec($this->curl);
        if(curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200) {
            throw new Exception('Request failed');
            return;
        }
    
        return end(json_decode($recording_output)->data->options)->profile;
    }

    public function recording_download_url($recordingid) {
        $profile = $this->recording_max_profile($recordingid);

        curl_setopt($this->curl, CURLOPT_URL, "https://web-api.teleboy.ch/users/".$this->tvapiuserid."/recordings/".$recordingid."/download/".$profile."?alternative=0");
    
        $download_output = curl_exec($this->curl);
        if(curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200) {
            throw new Exception('Request failed');
            return;
        }

        return json_decode($download_output)->data->url;
    }
}

?> 