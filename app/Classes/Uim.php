<?php
    namespace Modules\Authentication\Classes;

    use Exception;

    class Uim
    {
        private string $serviceHost;
        private int    $servicePort;
        private string $appId;
        private string $serverAddress;

        /**
         * @param string $serviceHost   The host address of the user verification service
         * @param int    $servicePort   The port of the user verification service
         * @param string $appId         The application ID
         * @param string $serverAddress The server address
         */
        public function __construct(string $serviceHost, int $servicePort, string $appId, string $serverAddress)
        {
            $this->serviceHost   = $serviceHost;
            $this->servicePort   = $servicePort;
            $this->appId         = $appId;
            $this->serverAddress = $serverAddress;
        }

        /**
         * Verify a user's credentials
         *
         * @param string $userId   The user ID
         * @param string $password The user password
         *
         * @return string The verification response
         * @throws Exception If connection fails or other errors occur
         */
        public function verify(string $userId, string $password)
        : string
        {
            $socket = $this->openSecureConnection();

            $postData = $this->buildPostData($userId, $password);
            $this->sendHttpRequest($socket, $postData);

            $response = $this->readResponse($socket);
            fclose($socket);

            return $this->decodeHexResponse($response);
        }

        /**
         * Open a secure connection to the verification service
         *
         * @return resource The socket connection
         * @throws Exception If connection fails
         */
        private function openSecureConnection()
        {
            $errno  = 0;
            $errstr = '';
            $socket = @fsockopen("tcp://" . $this->serviceHost, $this->servicePort, $errno, $errstr, 30);

            if (!$socket) {
                throw new Exception("Connection failed: $errstr ($errno)");
            }

            return $socket;
        }

        /**
         * Build the form data for the verification request
         *
         * @param string $userId   The user ID
         * @param string $password The user password
         *
         * @return string The URL-encoded form data
         */
        private function buildPostData(string $userId, string $password)
        : string
        {
            return http_build_query([
                'appsid'  => $this->appId,
                'loginid' => $userId,
                'passwd'  => $password,
                'addr'    => $this->serverAddress,
                'version' => 2
            ]);
        }

        /**
         * Send the HTTP request to the verification service
         *
         * @param resource $socket   The socket connection
         * @param string   $postData The data to send
         *
         * @return void
         */
        private function sendHttpRequest($socket, string $postData)
        : void
        {
            fwrite($socket, "POST /user_verification_dev.php HTTP/1.0\r\n");
            fwrite($socket, "Host: {$this->serviceHost}\r\n");
            fwrite($socket, "Content-type: application/x-www-form-urlencoded\r\n");
            fwrite($socket, "Content-length: " . strlen($postData) . "\r\n");
            fwrite($socket, "Accept: */*\r\n");
            fwrite($socket, "\r\n");
            fwrite($socket, "$postData\r\n");
            fwrite($socket, "\r\n");
        }

        /**
         * Read the response from the verification service
         *
         * @param resource $socket The socket connection
         *
         * @return string The response body
         */
        private function readResponse($socket)
        : string
        {
            // Skip headers
            $headers = "";
            while ($str = trim(fgets($socket, 4096))) {
                $headers .= "$str\n";
            }

            // Read body
            $body = "";
            while (!feof($socket)) {
                $body .= fgets($socket, 4096);
            }

            return $body;
        }

        /**
         * Decode a hex-encoded response
         *
         * @param string $data The hex-encoded data
         *
         * @return string The decoded string
         */
        private function decodeHexResponse(string $data)
        : string
        {
            $text  = '';
            $total = strlen($data);

            for ($j = 0; $j < $total; $j += 2) {
                $text .= chr(hexdec(substr($data, $j, 2)));
            }

            return $text;
        }
    }

