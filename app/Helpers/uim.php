<?php
    use Modules\Authentication\Classes\Uim;

    if (!function_exists('verify_user')) {
        function verify_user($id, $passwd, $SERVER_ADDR, $IPUserManager, $portUserManager, $appId)
        {
            try {
                $verifier = new Uim($IPUserManager, $portUserManager, $appId, $SERVER_ADDR);
                return $verifier->verify($id, $passwd);
            } catch (Exception $e) {
                // Handle error more gracefully than the original die()
                error_log("User verification error: " . $e->getMessage());
                return false;
            }
        }
    }

