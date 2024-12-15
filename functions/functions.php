<?php
date_default_timezone_set('Asia/Dhaka');

// functions.php 
require_once 'vendor/autoload.php';
use Detection\MobileDetect;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require BaseDir::getFullPath('vendor/phpmailer/phpmailer/src/Exception.php');
require BaseDir::getFullPath('vendor/phpmailer/phpmailer/src/PHPMailer.php');
require BaseDir::getFullPath('vendor/phpmailer/phpmailer/src/SMTP.php');

class MailSender
{
    private $mail;
    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // SMTP settings
        $this->mail->isSMTP();
        $this->mail->Host = Data::site('smtp_host');      // Set the SMTP server to send through
        $this->mail->SMTPAuth = true;                       // Enable SMTP authentication
        $this->mail->Username = Data::site('smtp_username');    // SMTP username
        $this->mail->Password = Data::site('smtp_password');     // SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;// Enable TLS encryption
        $this->mail->Port = 465;                        // TCP port
    }

    // Set from email and name
    public function setFrom($fromEmail, $fromName = 'Your Name')
    {
        $this->mail->setFrom($fromEmail, $fromName);
    }

    // Add a recipient
    public function addRecipient($toEmail, $toName = '')
    {
        $this->mail->addAddress($toEmail, $toName);
    }

    // Add CC or BCC
    public function addCC($ccEmail)
    {
        $this->mail->addCC($ccEmail);
    }

    public function addBCC($bccEmail)
    {
        $this->mail->addBCC($bccEmail);
    }
    public function clearAddresses()
    {
        $this->mail->clearAddresses();
    }

    // Add attachment
    public function addAttachment($filePath, $fileName = '')
    {
        $this->mail->addAttachment($filePath, $fileName);
    }

    // Send an HTML email using a template file
    public function sendHTMLEmail($subject, $templatePath, $templateVariables = [])
    {
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;

        // Load HTML template
        $body = file_get_contents($templatePath);
        if ($templateVariables) {
            foreach ($templateVariables as $key => $value) {
                $body = str_replace('{{' . $key . '}}', $value, $body);
            }
        }
        $this->mail->Body = $body;
        $this->mail->AltBody = strip_tags($body);  // Fallback for plain text clients

        return $this->send();
    }

    // Send a plain text email
    public function sendTextEmail($subject, $body, $isHtml = false)
    {
        $this->mail->isHTML($isHtml);
        $this->mail->Subject = $subject;
        $this->mail->Body = $body;

        return $this->send();
    }

    // Internal send method
    private function send()
    {
        try {
            $this->mail->send();
            return 'Message has been sent successfully.';
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}
// Usage Example

// Initialize the MailSender class
// $mailSender = new MailSender();

// // Set sender information
// $mailSender->setFrom('your-email@example.com', 'Your Name');

// // Add recipient(s)
// $mailSender->addRecipient('recipient@example.com', 'Recipient Name');

// // Send a simple text email
// echo $mailSender->sendTextEmail('Test Subject', 'This is a plain text email.');

// // Send an HTML email using a template
// $templateVariables = [
//     'name' => 'John Doe',
//     'date' => date('Y-m-d')
// ];
// echo $mailSender->sendHTMLEmail('HTML Email Subject', 'path/to/template.html', $templateVariables);

class Res
{
    private static $storage = [];

    // Store data in any format (array, object, JSON, XML) using dot notation

    public static function set($key, $value)
    {
        // If the value is a string, we assume it might be JSON or XML
        if (is_string($value)) {
            if (self::isJson($value)) {
                $value = json_decode($value, true); // Convert JSON to an array
            } elseif (self::isXml($value)) {
                $value = self::xmlToArray($value); // Convert XML to an array
            }
        }

        // Handle dot notation for nested arrays
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $array = &self::$storage;

            foreach ($keys as $k) {
                if (!isset($array[$k])) {
                    $array[$k] = []; // Create the nested array if it doesn't exist
                }
                $array = &$array[$k]; // Move deeper in the array
            }

            $array = $value; // Set the final value
        } else {
            self::$storage[$key] = $value; // If it's a single key, set it normally
        }
    }

    // Retrieve data with support for dot notation or return all data
    public static function get($key = null)
    {
        // If no key is provided, return all stored data
        if ($key === null) {
            return self::$storage;
        }

        // Check if the key contains a dot for nested access
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = self::$storage;

            foreach ($keys as $k) {
                if (is_array($value) && isset($value[$k])) {
                    $value = $value[$k];
                } elseif (is_object($value) && isset($value->$k)) {
                    $value = $value->$k;
                } else {
                    return null;
                }
            }
            return $value;
        }

        // If it's a single key, return it normally
        return isset(self::$storage[$key]) ? self::$storage[$key] : null;
    }
    public static function getFloatFormatted($key)
    {
        $value = self::get($key);
        return is_numeric($value) ? number_format((float) $value, 2, '.', '') : $value;
    }
    // Check if a key exists in the storage
    public static function has($key)
    {
        return self::get($key) !== null;
    }

    // Remove a specific key from the storage
    // Remove a specific key from the storage
    public static function remove($key)
    {
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $array = &self::$storage;

            foreach ($keys as $k) {
                if (!isset($array[$k])) {
                    return; // If any key in the chain doesn't exist, return
                }
                if ($k === end($keys)) {
                    unset($array[$k]); // Unset the final key
                } else {
                    $array = &$array[$k]; // Move deeper in the array
                }
            }
        } else {
            unset(self::$storage[$key]); // Remove key if not nested
        }
    }

    // Clear all storage
    public static function clear()
    {
        self::$storage = [];
    }

    // Utility function to check if a string is valid JSON
    private static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    // Utility function to check if a string is valid XML
    private static function isXml($string)
    {
        $xml = @simplexml_load_string($string);
        return ($xml !== false);
    }

    // Utility function to convert XML to an associative array
    private static function xmlToArray($xmlString)
    {
        $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
        return json_decode(json_encode($xml), true); // Convert XML to array via JSON
    }
}
class BaseDir
{
    private static $project_dir = '/';
    private static $base_path = null;

    // Set the base path only once
    public static function init()
    {
        if (self::$base_path === null) {
            self::$base_path = $_SERVER['DOCUMENT_ROOT'] . self::$project_dir;
        }
    }

    // Get the full path for a relative path
    public static function getFullPath($relativePath)
    {
        self::init(); // Ensure the base path is set
        return self::$base_path . $relativePath;
    }

    // Get project link (URL relative to the project root)
    public static function getProjectLink($relativePath)
    {
        return self::$project_dir . $relativePath;
    }
}
class SecureToken
{


    // Static method to generate a token
    public static function generateToken($user_id, $role, $uid, $expiryDays = 1)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + (86400 * $expiryDays);  // Token valid for X days

        // Create the token payload
        $payload = [
            'iat' => $issuedAt,        // Issued at time
            'exp' => $expirationTime,  // Expiration time
            'user_id' => $user_id,            // User ID
            'role' => $role,           // User role (admin/user)
            'deviceuid' => $uid        // Device unique identifier
        ];

        // Encode the payload to a JSON string
        $payloadJson = json_encode($payload);

        // Base64 encode the payload
        $payloadBase64 = base64_encode($payloadJson);

        // Get the dynamic secret key from the database
        $secretKey = Data::site('secret_key');

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        // Data to encrypt


        // Encrypt the data
        $encryptedData = openssl_encrypt($payloadBase64, 'aes-256-cbc', $secretKey, 0, $iv);
        $encryptedDataWithIv = base64_encode($encryptedData . '::' . $iv); // Include IV with the ciphertext


        // Return the full token (payload + signature)
        return $encryptedDataWithIv;
    }

    // Static method to validate and extract the token
    public static function extractToken($token)
    {


        // Get the dynamic secret key from the database
        $secretKey = Data::site('secret_key');
        // Base64 decode first
        $decodedToken = base64_decode($token);

        // Check if base64_decode returned false (malformed base64 string)
        if ($decodedToken === false) {
            return false; // Token is invalid (not even base64 encoded properly)
        }

        // Explode the token into data and IV (expecting two parts)
        $parts = explode('::', $decodedToken);

        // Check if the number of parts is 2 (to prevent malformed tokens)
        if (count($parts) !== 2) {
            return false; // Token structure is invalid (doesn't contain '::')
        }

        // Separate the encrypted data and the IV
        $encryptedData = $parts[0];
        $iv = $parts[1];
        $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $secretKey, 0, $iv);
        if ($decryptedData === false) {
            return false; // Decryption failed
        } else {
            $json = json_decode(base64_decode($decryptedData));

            return $json;
        }

    }
    /**
     * Validate a user's token by comparing the cookie token with the DB token
     * 
     * @param string $cookietoken The token from the user's cookie
     * @param string $DBToken      The token from the database
     * @return boolean             True if the token is valid, false if not
     */
    public static function isUserTokenValidate($cookietoken, $DBToken)
    {

        // Check if both tokens are set
        if (empty($DBToken) || empty($cookietoken)) {

            return false;
        }

        // Extract the tokens from the cookie and the database
        $DBTokenExt = self::extractToken($DBToken);
        $cookieTokenExt = self::extractToken($cookietoken);

        // Check if the user is authenticated and session is set
        if (isset($_SESSION['authenticated'])) {

            // Check if the session is set or not
            if (!isset($_SESSION['id']) && !isset($_SESSION['role'])) {

                return false;
            }

            // Check if the user ID and role in the session match the ones in the cookie token
            if ($_SESSION['id'] !== $cookieTokenExt->user_id && $_SESSION['role'] !== $cookieTokenExt->role) {

                return false;
            }
        }

        // Check if both tokens have expired . please pass unextracted tokens
        if (self::isTokenExpired($DBToken) === true && self::isTokenExpired($cookietoken) === true) {
            return false;
        }

        // Compare the tokens
        if (Detect::compareObjects($DBTokenExt, $cookieTokenExt) == false) {
            return false;
        }

        return true;

    }

    // Static method to check if a token has expired
    public static function isTokenExpired($token)
    {
        if (empty($token)) {
            return true;
        }

        $payload = self::extractToken($token);
        // print_r(date('Y-m-d h:m:s',$payload->exp));
        if (!$payload || $payload->exp < time()) {
            return true;  // Token has expired
        }
        return false;  // Token is still valid
    }

    // Static method to check if a user is an admin based on the token
    public static function isAdmin($token)
    {
        $payload = self::extractToken($token);

        if ($payload && isset($payload['role']) && $payload['role'] === 'admin') {
            return true;
        }
        return false;
    }

    // Static method to get the user ID from the token
    public static function getUserId($token)
    {
        $payload = self::extractToken($token);

        if ($payload && isset($payload['user_id'])) {
            return $payload['user_id'];
        }
        return null;
    }

    // Static method to get the user role from the token
    public static function getUserRole($token)
    {
        $payload = self::extractToken($token);

        if ($payload && isset($payload['role'])) {
            return $payload['role'];
        }
        return null;
    }

    public static function generateDeviceUID()
    {

        $deviceInfo = [
            'deviceType' => Detect::DeviceType(),
            'screenResolution' => Detect::ScreenResolution(),
            'osType' => Detect::osType(),
            'osInfo' => Detect::OsInfo(),
            'browser' => Detect::Browser(),
            'userAgent' => Detect::userAgent(),
        ];
        $json_enc = json_encode($deviceInfo);
        $deviceUID = base64_encode($json_enc);

        return $deviceUID;

    }
    public static function extractDeviceInfo($deviceUID)
    {
        // Assume we have a method to retrieve the original Base64 encoded device info using the UID


        if ($deviceUID) {
            // Decode the Base64 string
            $jsonDeviceInfo = base64_decode($deviceUID);

            // Decode the JSON back to an associative array
            $deviceInfo = json_decode($jsonDeviceInfo, true);

            // Return the extracted device info
            return [
                'deviceType' => $deviceInfo['deviceType'],
                'screenResolution' => $deviceInfo['screenResolution'],
                'osType' => $deviceInfo['osType'],
                'osInfo' => $deviceInfo['osInfo'],
                'browser' => $deviceInfo['browser'],
                'userAgent' => $deviceInfo['userAgent'],
            ];
        }

        return ''; // Return null if device info cannot be found
    }
    public static function isValidDUID()
    {

        if (!isset($_COOKIE['DUID']) && empty($_COOKIE['DUID'])) {
            return false;
        }

        //collect duid token
        $genDUID = SecureToken::generateDeviceUID();
        $cookieDUID = $_COOKIE['DUID'];

        //extract token to object
        $cookieDUID = self::extractDeviceInfo($cookieDUID);
        $genDUID = self::extractDeviceInfo($genDUID);

        // debug 
        // print_r($cookieDUID);
        // print_r($genDUID);
        if (Detect::compareObjects($cookieDUID, $genDUID) === false) {

            return false;
        }

        return true;


    }

}
class Log
{
    public static function addCronLog($message)
    {
        $timezone = new DateTimeZone('+06:00');
        $date = new DateTime('now', $timezone);
        $time = $date->format("Y-m-d H:i:s");
        $logFile = $_SERVER['DOCUMENT_ROOT'] . "/storage/log/cron_log.txt";
        file_put_contents($logFile, "[" . $time . "] " . $message . PHP_EOL, FILE_APPEND);
    }
    public static function addMailSenderLog($message)
    {
        $timezone = new DateTimeZone('+06:00');
        $date = new DateTime('now', $timezone);
        $time = $date->format("Y-m-d H:i:s");
        $logFile = $_SERVER['DOCUMENT_ROOT'] . "/storage/log/mail_sender_log.txt";
        file_put_contents($logFile, "[" . $time . "] " . $message . PHP_EOL, FILE_APPEND);
    }
    public static function addSessionLog($type, $userDetails, $message, )
    {
        $msg = "[" . $type . "] " . $userDetails . " - " . $message;
        $timezone = new DateTimeZone('+06:00');
        $date = new DateTime('now', $timezone);
        $time = $date->format("Y-m-d H:i:s");
        $logFile = $_SERVER['DOCUMENT_ROOT'] . "/storage/log/session_log.txt";
        file_put_contents($logFile, "[" . $time . "] " . $msg . PHP_EOL, FILE_APPEND);
    }
}
class SessionManager
{

    // Check if the user is logged in
    public static function isAuthenticated()
    {

        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] == true;
    }
    public static function UserSessionVerify()
    {

        // error_log("Debug: UserSessionVerify at " . __FILE__ . " on line " . __LINE__);
        if (isset($_COOKIE['_token'])) {
            $token = $_COOKIE['_token'];
            // error_log("Debug: _token set at " . __FILE__ . " on line " . __LINE__);
            if (!SecureToken::isTokenExpired($token) && SessionManager::isAuthenticated() && SecureToken::isValidDUID()) {
                $user_id = $_SESSION['id'];
                $role = $_SESSION['role'];
                $email = $_SESSION['email'];
                $table = 'user';
                if ($role !== 'user') {
                    $table = 'admin_user';
                }
                $cookieTokenEXT = SecureToken::extractToken($token);
                if ($cookieTokenEXT->user_id !== $user_id && $cookieTokenEXT->role !== $role) {
                    // error_log("Debug: Cookie token user and session token user does not match at " . __FILE__ . " on line " . __LINE__);
                    self::logoutUser();
                }
                $DBToken = Data::fetchSingleFieldLastModified('login_session', '_token', ['user_id' => $user_id, 'status' => 1], [$user_id, 1]);
                if (SecureToken::isTokenExpired($DBToken) === true) {
                    // error_log("Debug: Token expired at " . __FILE__ . " on line " . __LINE__);
                    self::logoutUser();
                }
                if (SecureToken::isUserTokenValidate($token, $DBToken) === false) {
                    // error_log("Debug: Token is not valid at " . __FILE__ . " on line " . __LINE__);

                    SessionManager::logoutUser();
                }

                if (Data::countRecords($table, ['id', 'role', 'email', 'status'], [$user_id, $role, $email, 1]) === 0) {
                    // error_log("Notice: User not found with status 1 " . __FILE__ . " on line " . __LINE__);

                    SessionManager::logoutUser();
                }
                Data::updateActiveStatus(Detect::formatedCTime(), $user_id, $role, 1, Data::getCookie('DUID'));

            } elseif (!SecureToken::isTokenExpired($token) && !SessionManager::isAuthenticated() && SecureToken::isValidDUID()) {
                $user_id = SecureToken::extractToken($token)->user_id;
                $role = SecureToken::extractToken($token)->role;
                $table = 'user';
                if ($role !== 'user') {
                    $table = 'admin_user';
                }
                $DBToken = Data::fetchSingleFieldLastModified('login_session', '_token', ['user_id' => $user_id, 'status' => 1], [$user_id, '1']);

                if (SecureToken::isTokenExpired($DBToken) === true) {
                    // error_log("Debug: DBToken expired at " . __FILE__ . " on line " . __LINE__);
                    self::logoutUser();
                }
                if (SecureToken::isUserTokenValidate($token, $DBToken) === false) {
                    // error_log("Debug: Token validation failed at " . __FILE__ . " on line " . __LINE__);
                    SessionManager::logoutUser();
                }
                $userData = Data::fetchSingleRow($table, ['id' => $user_id, 'role' => $role, 'status' => 1], [$user_id, $role, 1]);
                if ($userData) {
                    // error_log("Debug: Regenerating session for user " . $user_id . " at " . __FILE__ . " on line " . __LINE__);
                    self::userSessionRegenerate($userData['email'], $userData['role'], $userData['id']);
                } else {
                    // error_log("Debug: User data not found at " . __FILE__ . " on line " . __LINE__);
                    SessionManager::logoutUser();
                }
                Data::updateActiveStatus(Detect::formatedCTime(), $user_id, $role, 1, Data::getCookie('DUID'));
            } else {
                // error_log("Debug: Logout due to failed conditions at " . __FILE__ . " on line " . __LINE__);
                SessionManager::logoutUser();
            }


        } elseif (self::isAuthenticated()) {
            SessionManager::logoutUser();
        } else {
            error_log("Debug: _token not set at " . __FILE__ . " on line " . __LINE__);

        }

    }
    public static function userSessionRegenerate($email, $role, $user_id)
    {


        $_SESSION['authenticated'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['id'] = $user_id;

    }




    // Check if the user is an admin
    public static function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    public static function isUser()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
    }

    // Redirect based on user role
    public static function redirectBasedOnRole()
    {
        if (self::isAuthenticated()) {
            if (self::isAdmin()) {

                $dashboarad_url = BaseDir::getProjectLink('admin/dashboard');
                header("Location: $dashboarad_url");
            } else {
                $userApp_url = BaseDir::getProjectLink('home');
                header("Location: $userApp_url");
            }
            exit();
        }
    }

    // Log the user in by setting cookies
    public static function loginUser($email, $role, $user_id, $expires = 7)
    {
        if (isset($_COOKIE['DUID'])) {
            $genToken = SecureToken::generateToken($user_id, $role, $_COOKIE['DUID'], $expires);
            $countDevice = Data::countRecords('login_session', ['user_id', 'role', 'DUID'], [$user_id, $role, Data::getCookie('DUID')]);
            if ($countDevice === 1) {

                if (Data::countRecords('login_session', ['user_id', 'role', 'DUID'], [$user_id, $role, Data::getCookie('DUID')]) === 1) {

                    $result = Data::updateLoginSession(Detect::ipAddress(), $genToken, $user_id, $role, 1, Data::getCookie('DUID'));
                    if ($result !== false) {


                        self::SetCookie('_token', $genToken, $expires);

                        // Correct $_SESSION array access
                        $_SESSION['authenticated'] = true;
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = $role;
                        $_SESSION['id'] = $user_id;
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

            } elseif ($countDevice === 0) {

                if (Data::insert('login_session', ['user_id', 'role', '_token', 'DUID', 'status', 'modified_at', 'created_at', 'ip_address', 'last_seen'], [$user_id, $role, $genToken, Data::getCookie('DUID'), 1, Detect::formatedCTime(), Detect::formatedCTime(), Detect::ipAddress(), Detect::formatedCTime()])) {
                    self::SetCookie('_token', $genToken);

                    // Correct $_SESSION array access
                    $_SESSION['authenticated'] = true;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role;
                    $_SESSION['id'] = $user_id;
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    public static function SetCookie($name, $value, $expires = 1)
    {
        $cookie_options = [
            'expires' => time() + (86400 * $expires), // 365 days
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        setcookie($name, $value, $cookie_options);

    }

    // Log the user out by clearing cookies
    public static function logoutUser()
    {
        $getLog = "cookie DUID: " . Data::getCookie('DUID') . " - Gen DUID: " . SecureToken::generateDeviceUID();
       
        if(isset($_SESSION['email'])){
            Log::addSessionLog('Logout', $_SESSION['email'], $getLog);
        }else {
            Log::addSessionLog('Logout', 'Unknown', $getLog);
        }
       
        // Clear the authentication status
        $_SESSION['authenticated'] = false;

        session_start();
        $_SESSION = array();

        // Destroy the session
        if (session_id()) {
            session_destroy();
        }

        // Unset the authentication-related cookies
        if (isset($_COOKIE['_token'])) {
            setcookie('_token', '', time() - 3600, '/', '', true, true); // Expire the cookie
            unset($_COOKIE['_token']);
        }


        header('Location: /signin');
        exit();
    }

    // Redirect if the user is already logged in
    public static function redirectIfLoggedIn()
    {
        if (self::isAuthenticated()) {
            self::redirectBasedOnRole();
        }
    }

    // Redirect to login if the user is not authenticated
    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            header('location: ' . BaseDir::getProjectLink('signin') . '');
            exit();
        }
    }
}

class Swap
{

    public static function status(int $status)
    {
        switch ($status) {
            case Status::Active->value:
                return "Active";
            case Status::Inactive->value:
                return "Inactive";
            case Status::Blocked->value:
                return "Blocked";
            default:
                return "Unknown Status";
        }
    }
    public static function Gender(int $gender)
    {
        switch ($gender) {
            case Gender::NotSet->value:
                return "";
            case Gender::Male->value:
                return "Male";
            case Gender::Others->value:
                return "Others";
            default:
                return "Unknown Gender";
        }
    }
    public static function CampaignStatus(int $status)
    {
        switch ($status) {
            case 0:
                return "Cancelled";
            case 1:
                return "Active";
            case 2:
                return "Completed";
            default:
                return "Unknown Status";
        }
    }

    public static function GetIntervalMuinutes($newTime, $oldTime)
    {

        if (empty($newTime) || empty($oldTime)) {
            return null;
        }
        $timezone = new DateTimeZone('+06:00');
        $updateTime = new DateTime($newTime, $timezone);
        $beforTime = new DateTime($oldTime, $timezone); // The recent active time passed as "Y-m-d H:i:s"

        $interval = $updateTime->diff($beforTime);

        // Convert to total minutes
        $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        return $totalMinutes;
    }
    public static function OnlineStatus($lastActiveTime)
    {
        $timezone = new DateTimeZone('+06:00');
        $currentTime = new DateTime('now', $timezone);
        $lastActive = new DateTime($lastActiveTime, $timezone); // The recent active time passed as "Y-m-d H:i:s"

        $interval = $currentTime->diff($lastActive);

        // Convert to total minutes
        $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        // 1. Check if the user was active in the last 5 minutes
        if ($totalMinutes <= 5) {
            return "Online";
        }

        // 2. Check if the last activity was within an hour
        if ($totalMinutes < 60) {
            return $interval->i . " minutes ago";
        }

        // 3. Check if the last activity was within 24 hours
        if ($totalMinutes < 24 * 60) {
            $hoursAgo = ($totalMinutes / 60); // Convert to hours
            return round($hoursAgo) . " hours ago";
        }

        // 4. If it was more than 24 hours ago, return the last active date
        return "Last seen on " . $lastActive->format('Y-m-d H:i:s');
    }

    // Example usage
    // $lastActive = "2024-10-22 19:09:55";
    // echo getOnlineStatus($lastActive);
    public static function getRelativeTime($lastActiveTime){
        $timezone = new DateTimeZone('+06:00');
        $currentTime = new DateTime('now', $timezone);
        $lastActive = new DateTime($lastActiveTime, $timezone); // The recent active time passed as "Y-m-d H:i:s"

        $interval = $currentTime->diff($lastActive);

        // Convert to total minutes
        $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        // 1. Check if the user was active in the last 5 minutes
        if ($totalMinutes <= 5) {
            return "Just now";
        }

        // 2. Check if the last activity was within an hour
        if ($totalMinutes < 60) {
            return $interval->i . " minutes ago";
        }

        // 3. Check if the last activity was within 24 hours
        if ($totalMinutes < 24 * 60) {
            $hoursAgo = ($totalMinutes / 60); // Convert to hours
            return round($hoursAgo) . " hours ago";
        }

        // 4. If it was more than 24 hours ago, return the last active date
        return $lastActive->format('Y-m-d H:i:s');
    }
    public static function TrxnType(string $trxnType): string
    {
        return match ($trxnType) {
            TrxnType::Deposit->value => 'Deposit',
            TrxnType::Withdrawal->value => 'Withdrawal',
            TrxnType::Adjustment->value => 'Adjustment',
            TrxnType::Profit->value => 'Profit',
            TrxnType::Refund->value => 'Refund',
            TrxnType::Chargeback->value => 'Chargeback',
            TrxnType::Bonus->value => 'Bonus',
            TrxnType::Transfer->value => 'Transfer',
            TrxnType::Fee->value => 'Fee',
            TrxnType::ReferralComm->value => 'Referral Commission',
            default => 'Unknown Transaction Type',
        };
    }

    public static function TrxnStatus(int $trxnStatus): string
    {
        return match ($trxnStatus) {

            TrxnStatus::Rejected->value => 'Rejected',
            TrxnStatus::Completed->value => 'Completed',
            TrxnStatus::Pending->value => 'Pending',
            TrxnStatus::Failed->value => 'Failed',
            TrxnStatus::ManualCheck->value => 'Manual Check',
            TrxnStatus::Reversed->value => 'Reversed',
            TrxnStatus::Refunded->value => 'Refunded',
            TrxnStatus::OnHold->value => 'On Hold',
            TrxnStatus::Processing->value => 'Processing',
            default => 'Unknown Transaction Status',
        };
    }public static function TrxnStatusColor(int $trxnStatus): string {
        error_log("TrxnStatus: " . $trxnStatus);
        return match ($trxnStatus) {
            TrxnStatus::Rejected->value => 'danger',
            TrxnStatus::Completed->value => 'success',
            TrxnStatus::Pending->value => 'warning',
            TrxnStatus::Failed->value => 'danger',
            TrxnStatus::ManualCheck->value => 'warning',
            TrxnStatus::Reversed->value => 'danger',
            TrxnStatus::Refunded->value => 'danger',
            TrxnStatus::OnHold->value => 'warning',
            TrxnStatus::Processing->value => 'warning',
            default => 'primary',
        };
    }
    
    

}

class Route
{

    private static $routes = [];

    // Register a route with a direct view (default GET)
    public static function view($uri, $viewFile)
    {
        self::$routes['GET'][$uri] = ['view' => $viewFile];
    }

    // Register a route with a controller and method
    public static function match($methods, $uri, $controller, $method)
    {
        foreach ($methods as $httpMethod) {
            self::$routes[$httpMethod][$uri] = ['controller' => $controller, 'method' => $method];
        }
    }

    // Dispatch the request to the correct controller or view
    public static function dispatch()
    {
        global $baseDir;
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // Strip query params
        // Normalize the request path
        if ($requestUri === '/') {
            // If the request path is just '/', keep it as is for the home route
            $requestUri = '/';
        } else {
            // For other routes, trim trailing slashes
            $requestUri = rtrim($requestUri, '/');
        }

        if (isset(self::$routes[$requestMethod][$requestUri])) {
            $route = self::$routes[$requestMethod][$requestUri];
            // print_r($route);
            if (isset($route['view'])) {
                // Handle view-based routes
                $viewPath = BaseDir::getFullPath($route['view']);
                if (file_exists($viewPath)) {
                    require_once $viewPath;
                } else {
                    self::handle404();
                }
            } elseif (isset($route['controller']) && isset($route['method'])) {
                // Handle controller-based routes
                $controller = new $route['controller'];
                if (method_exists($controller, $route['method'])) {
                    call_user_func([$controller, $route['method']]);
                } else {
                    self::handle404();
                }
            }
        } else {
            self::handle404();
        }

    }

    private static function handle404()
    {
        http_response_code(404);
        require_once BaseDir::getFullPath('/404.php');
    }

}

class Data
{
    private static $conn;

    // Establish DB connection (called internally)
    private static function connect()
    {
        if (!self::$conn) {
            require BaseDir::getFullPath('config/database.php');
            self::$conn = $conn; // Assume $conn is defined in the config
        }
    }
    // Select data based on tag (procedural style)
    public static function site($tag)
    {
        require BaseDir::getFullPath('config/database.php');

        // Use a prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT value FROM site_settings WHERE tag = ?");
        mysqli_stmt_bind_param($stmt, 's', $tag);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            return htmlspecialchars($data['value'], ENT_QUOTES, 'UTF-8');
        } else {
            return '';
        }
    }
    public static function getCookie($key)
    {

        if (isset($_COOKIE[$key]) && !empty($_COOKIE[$key])) {
            return $_COOKIE[$key];
        } else {
            return '';
        }

    }
    public static function getSessionValue($key)
    {

        if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return '';
        }

    }

    // Insert data into a table dynamically (procedural style)
    public static function insert($table, $columns, $values)
    {
        require BaseDir::getFullPath('config/database.php');

        // Ensure columns and values count match
        if (count($columns) !== count($values)) {
            throw new Exception('Number of columns and values do not match');
        }

        // Prepare SQL
        $colString = implode(", ", $columns);
        $placeholders = implode(", ", array_fill(0, count($values), "?"));

        // Build and prepare query
        $stmt = mysqli_prepare($conn, "INSERT INTO $table ($colString) VALUES ($placeholders)");

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare SQL query: " . mysqli_error($conn));
        }

        // Dynamically bind parameters based on the values provided
        $types = str_repeat('s', count($values)); // Assuming all inputs are strings, change as needed
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        return mysqli_stmt_execute($stmt);
    }

    // Update data in a table dynamically (procedural style)
    public static function update($table, $columns, $values, $conditionColumns, $conditionValues)
    {
        require BaseDir::getFullPath('config/database.php');

        // Build the SET part of the query
        $setString = implode(" = ?, ", $columns) . " = ?";

        // Build the WHERE part of the query
        $conditionString = implode(" = ? AND ", $conditionColumns) . " = ?";

        // Combine query
        $query = "UPDATE $table SET $setString WHERE $conditionString";

        // Debugging: print query with placeholders
        // echo "Query with placeholders: $query\n";
        // echo "Values to bind: ";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt === false) {
            die('MySQL prepare failed: ' . mysqli_error($conn));
        }

        // Merge values and conditionValues
        $allValues = array_merge($values, $conditionValues);

        // Bind parameter types, assuming all inputs are strings. Adjust types if needed
        $types = str_repeat('s', count($allValues));
        // $query1->bind_param('sss',$username, $updatecode, $confirmcode); 
        // Bind parameters
        mysqli_stmt_bind_param($stmt, $types, ...$allValues);

        // Execute the query
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            die('MySQL execute failed: ' . mysqli_stmt_error($stmt));
        }
        // print_r($allValues);
        return $result;
    }
    public static function updateLoginSession($ip, $token, $userId, $role, $status, $DUID)
    {
        require BaseDir::getFullPath('config/database.php');
        $time = Detect::formatedCTime();
        // Define the query
        $query = "UPDATE login_session SET ip_address = ?, _token = ?, status=? ,last_seen = ? WHERE user_id = ? AND role = ? AND DUID= ?   ORDER BY last_seen DESC LIMIT 1";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $query);

        // Bind the parameters directly
        mysqli_stmt_bind_param($stmt, 'ssisiss', $ip, $token, $status, $time, $userId, $role, $DUID);



        return mysqli_stmt_execute($stmt);
    }
    public static function updateActiveStatus($time, $user_id, $role, $status, $DUID)
    {
        error_log("Debug: updateActiveStatus at " . __FILE__ . " on line " . __LINE__);
        require BaseDir::getFullPath('config/database.php');

        // Define the query
        $query = "UPDATE login_session SET last_seen = ? WHERE user_id = ? AND  status = ? AND role = ? AND DUID= ?  ORDER BY last_seen DESC LIMIT 1";

        // Prepare the statement
        $stmt = mysqli_prepare($conn, $query);

        // Bind the parameters directly
        mysqli_stmt_bind_param($stmt, 'siiss', $time, $user_id, $status, $role, $DUID);



        return mysqli_stmt_execute($stmt);
    }

    // Delete data from a table based on a condition (procedural style)
    public static function delete($table, $condition, $conditionValues, $types = null)
    {
        require BaseDir::getFullPath('config/database.php');

        // Check if the connection exists
        if (!$conn) {
            throw new Exception("Database connection not found.");
        }

        // Ensure $table and $condition are sanitized or come from trusted sources
        $stmt = mysqli_prepare($conn, "DELETE FROM `$table` WHERE $condition");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
        }

        // Determine parameter types if not provided
        if ($types === null) {
            $types = str_repeat('s', count($conditionValues)); // Assuming all are strings if types are not given
        }

        // Dynamically bind parameters based on the provided types and values
        mysqli_stmt_bind_param($stmt, $types, ...$conditionValues);

        // Execute and check for success
        $executeResult = mysqli_stmt_execute($stmt);

        if (!$executeResult) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);

        return $executeResult;

        // $table = 'temporary_email_domains';
        // $condition = 'id = ?';
        // $conditionValues = [123];  // Assuming you want to delete the row with ID = 123
        // $types = 'i';  // Since `id` is an integer
    }

    // Static Fetch a single row by condition
    public static function fetchSingleRow($table, $condition, $values)
    {
        // echo "values";
        // print_r($values);

        // print_r($condition);
        self::connect();

        $conditionString = implode(" = ? AND ", array_keys($condition)) . " = ?";
        $query = "SELECT * FROM $table WHERE $conditionString LIMIT 1";
        // echo $query;
        $stmt = mysqli_prepare(self::$conn, $query);

        $types = '';
        // $types = str_repeat('s', count($values)); // Adjust types if needed
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= 'i'; // Integer type
            } elseif (is_string($value)) {
                $types .= 's'; // String type
            } else {
                $types .= 's'; // Default to string
            }
        }
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        // echo "types<br>";
        // print_r($types);
        // echo "values<br>";
        // print_r($values);

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        // print_r($result);
        return mysqli_fetch_assoc($result); // Returns an associative array
    }
    // Static Fetch a single field's value
    public static function fetchSingleField($table, $field, $condition, $values)
    {
        self::connect();

        $conditionString = implode(" = ? AND ", array_keys($condition)) . " = ?";
        $query = "SELECT $field FROM $table WHERE $conditionString ORDER BY id DESC LIMIT  1";

        $stmt = mysqli_prepare(self::$conn, $query);

        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        return $row ? $row[$field] : null; // Return the value of the field or null
    }
    public static function fetchSingleFieldLastModified($table, $field, $condition, $values)
    {
        self::connect();

        $conditionString = implode(" = ? AND ", array_keys($condition)) . " = ?";
        $query = "SELECT $field FROM $table WHERE $conditionString ORDER BY modified_at DESC LIMIT  1";

        $stmt = mysqli_prepare(self::$conn, $query);

        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        return $row ? $row[$field] : null; // Return the value of the field or null
    }
    public static function fetchSingleFieldLastCreated($table, $field, $condition)
    {
        self::connect();

        // Prepare the condition string and extract values
        $columns = array_keys($condition);
        $conditionString = implode(" = ? AND ", $columns) . " = ?";
        $query = "SELECT $field FROM $table WHERE $conditionString ORDER BY created_at DESC LIMIT 1";

        $stmt = mysqli_prepare(self::$conn, $query);

        // Extract the values from $condition
        $values = array_values($condition);
        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        return $row ? $row[$field] : null; // Return the value of the field or null


        // =====example of use =======
        // $table = 'users';
        // $field = 'email';
        // $condition = ['status' => 'active', 'role' => 'member'];

        // $email = YourClassName::fetchSingleFieldLastCreated($table, $field, $condition);
    }

    // Static Count Records
    public static function countRecords($table, $condition, $values)
    {
        self::connect();

        // Build the condition string: column names and placeholders
        $conditionParts = [];
        foreach ($condition as $col) {
            $conditionParts[] = "$col = ?";
        }
        $conditionString = implode(" AND ", $conditionParts);

        // Prepare the query
        $query = "SELECT COUNT(*) as count FROM $table WHERE $conditionString";

        // Debugging - print query and values
        // echo "SQL Query: $query\n";
        // print_r($values);

        // Prepare and execute statement
        $stmt = mysqli_prepare(self::$conn, $query);

        // Bind parameters (assuming string types for all values)
        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        // Execute and fetch result
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        // Return the count or 0 if no result
        return $row['count'] ?? 0;
    }
    public static function SiteRecords($table, $condition, $values, $needColumn = "*")
    {
        self::connect();

        // Build the condition string: column names and placeholders
        $conditionParts = [];
        foreach ($condition as $col) {
            $conditionParts[] = "$col = ?";
        }
        $conditionString = implode(" AND ", $conditionParts);

        // Prepare the query
        $query = "SELECT $needColumn FROM $table WHERE $conditionString";

        // Debugging - print query and values
        // echo "SQL Query: $query\n";
        // print_r($values);

        // Prepare and execute statement
        $stmt = mysqli_prepare(self::$conn, $query);

        // Bind parameters (assuming string types for all values)
        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        // Execute and fetch result
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $resRecords = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $resRecords[$row['tag']] = $row;
        }



        // Return the value or empty if no result
        return $resRecords ?? '';
    }


    // Generate referral ID based on the user ID
    public static function generate_referral_id($user_id)
    {
        return str_pad($user_id, 8, '0', STR_PAD_LEFT); // Example: REF000123
    }
    public static function validate_input($name, $email, $password, $cpassword)
    {
        // Validate the name (allow letters and spaces, avoid script injection)
        if (!preg_match("/^[a-zA-Z\s]*$/", $name)) {

            return "Invalid name. Only letters and spaces are allowed.";
        }

        // Validate email using PHP's filter
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        // Enforce strong password policy (min 8 characters, at least 1 number, 1 uppercase letter)
        if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
            return "Password must be at least 8 characters long, contain at least one uppercase letter and one number.";
        }
        if ($password !== $cpassword) {
            return "Password & Confirm Password didn't matched.";
        }


        return true; // Validation passed
    }


    // Check if the referred ID is valid (exists in the users table)
    public static function is_valid_referred_id($referred_id, $conn)
    {
        // Convert referral ID like 'X9000012' to the user ID number (12)
        $user_id = intval($referred_id);

        // Prepare the SQL statement to check if the user ID exists
        $query = "SELECT user_id FROM user WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        // Return true if one result is found, meaning the referrer ID is valid
        return mysqli_stmt_num_rows($stmt) === 1;
    }

    // Create a user with referral ID (optional)
    public static function create_user($name, $email, $password, $cpassword, $referrer_id = 0, $conn)
    {
        // Validate inputs
        $validation_result = self::validate_input($name, $email, $password, $cpassword);
        if ($validation_result !== true) {
            echo "validation false";
            return $validation_result; // Return validation error
        }

        // Check if referrer_id is provided and is valid
        if ($referrer_id !== null && !empty($referrer_id) && !self::is_valid_referred_id($referrer_id, $conn)) {
            echo "invalid false";
            return "Invalid referrer ID.";
        }

        // Use prepared statements to prevent SQL injection
        $query = "INSERT INTO user (user_name, email, password, referrer_id,modified_at,created_at) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        // Check if prepare was successful
        if ($stmt === false) {
            return "Database error: " . mysqli_error($conn);
        }

        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $date = Detect::formatedCTime();
        // Bind the parameters (s = string type, i = integer)
        $referrer_id_value = ($referrer_id !== null) ? (string) $referrer_id : '0';
        mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $hashed_password, $referrer_id_value, $date, $date);


        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            $id = mysqli_insert_id($conn); // Get the newly inserted user ID

            // Generate referral ID based on user ID (e.g., REF000123)
            $referral_id = self::generate_referral_id($id);

            // Update the user record with referral ID
            $update_query = "UPDATE user SET user_id = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'si', $referral_id, $id);
            mysqli_stmt_execute($update_stmt);

            return true;
        } else {
            return "Error: " . mysqli_stmt_error($stmt) . "referral:";
        }

        // Close the statement

    }
    public static function getReferralData($userId, $level)
    {
        require BaseDir::getFullPath('config/database.php');

        $query = "
            SELECT u.user_id AS main_referral_user_id, referred.user_name, ub.balance, SUM(rt.commission_amount) AS total_commission
            FROM referral_trxn rt
            JOIN user u ON u.id = rt.main_referral_user_id
            JOIN user referred ON referred.id = rt.user_id
            JOIN user_balances ub ON ub.user_id = rt.user_id
            WHERE rt.main_referral_user_id = ? AND rt.referral_level = ?
            GROUP BY rt.user_id
        ";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $level);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $referrals = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $referrals[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $referrals;
    }
    public static function sendNotification($userId = 0, $role = "", $title, $message = "")
    {
        require BaseDir::getFullPath('config/database.php');

        $time = Detect::formatedCTime();
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, role,created_at) VALUES (?, ?, ?, ?,?)");
        $stmt->bind_param('issss', $userId, $title, $message, $role, $time);
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Notification Error: " . $stmt->error);
            return false;
        }

    }
    public static function fetchAll(string $table, array $condition = [])
    {
        // Include database configuration
        require BaseDir::getFullPath('config/database.php');
    
        // Build the WHERE clause dynamically from the $condition array
        $whereClause = '';
        if (!empty($condition)) {
            // Only add WHERE clause if there are conditions
            $whereClause = 'WHERE ' . implode(' AND ', array_filter($condition)); // Filters out empty conditions
        }
    
        // Construct the SQL query
        $sql = "SELECT * FROM `$table` $whereClause";
    
        // Execute the query
        $result = mysqli_query($conn, $sql);
    
        if (!$result) {
            // Handle error and return false if the query fails
            return false;
        }
    
        // Fetch all results as an associative array
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    
        // Return the fetched data
        return $data;
    }
    
    


}
class Detect
{

    public static function detectlib()
    {
        return new MobileDetect;
    }
    public static function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? "unkown_User_Agent";
    }

    public static function DeviceType()
    {

        if (self::detectlib()->isMobile()) {
            $deviceType = 'Mobile';
        } elseif (self::detectlib()->isTablet()) {
            $deviceType = 'Tablet';
        } else {
            $deviceType = 'Desktop';
        }

        return $deviceType;
    }
    public static function osType()
    {
        if (self::detectlib()->isiOS()) {
            $os = 'iOS';
        } elseif (self::detectlib()->isAndroidOS()) {
            $os = 'Android';
        } elseif (strpos(self::userAgent(), 'Windows NT') !== false) {
            $os = 'Windows';
        } elseif (strpos(self::userAgent(), 'Mac OS') !== false) {
            $os = 'macOS';
        } elseif (strpos(self::userAgent(), 'Linux') !== false) {
            $os = 'Linux';
        } else {
            $os = 'Unknown_OS';
        }
        return $os;
    }

    public static function Browser()
    {
        // Browser Detection
        if (preg_match('/MSIE|Trident/i', self::userAgent())) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Edge/i', self::userAgent())) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Chrome/i', self::userAgent()) && !preg_match('/OPR|Edg|CriOS|wv/i', self::userAgent())) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Safari/i', self::userAgent()) && !preg_match('/Chrome|OPR|Edg|CriOS/i', self::userAgent())) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/i', self::userAgent())) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/OPR/i', self::userAgent())) {
            $browser = 'Opera';
        } else {
            $browser = 'Unknown_Browser';
        }

        if (preg_match('/wv/i', self::userAgent())) {
            $browser .= ' (Android_WebView)';
        } elseif (preg_match('/CriOS/i', self::userAgent())) {
            $browser .= ' (iOS_Chrome_WebView)';
        } elseif (preg_match('/Safari/i', self::userAgent()) && preg_match('/GSA/i', self::userAgent())) {
            // GSA is Google Search App's WebView on iOS
            $browser .= ' (iOS_WebView)';
        }
        return $browser;
    }
    public static function OsInfo()
    {
        // Regular expression to match the contents of the first parentheses
        // Captures only the part after the first semicolon
        if (preg_match('/\((?:[^;]*; )?(.*?;[^)]+)\)/', self::userAgent(), $matches)) {
            $matches = trim($matches[1]);

            return $matches; // Return the matched group
        }

        return 'Unkown_OsInfo'; // Return null if no match is found
    }
    public static function ScreenResolution()
    {


        if (isset($_COOKIE['size'])) {
            $size = base64_decode($_COOKIE['size']);
        } else {
            $size = "Unknown_ScreenResolution";
        }
        return $size;
    }
    public static function compareObjects($obj1, $obj2)
    {
        // Convert both objects to arrays
        $arr1 = (array) $obj1;
        $arr2 = (array) $obj2;

        // Check if the number of properties are the same
        if (count($arr1) != count($arr2)) {
            return false;
        }

        // Loop through each property and compare values
        foreach ($arr1 as $key => $value) {
            // Check if the key exists in the second object and values match
            if (!isset($arr2[$key]) || $arr2[$key] !== $value) {
                return false;
            }
        }

        // If all values match, return true
        return true;
    }
    public static function formatedCTime($format = 'Y-m-d H:i:s')
    {
        $timezone = new DateTimeZone('+06:00');
        $date = new DateTime('now', $timezone);


        return $date->format($format);
    }

    public static function ipAddress()
    {
        // List of server variables to check for IP address
        $ipKeys = [
            'HTTP_CLIENT_IP',    // Client's IP from shared internet
            'HTTP_X_FORWARDED_FOR', // IP when using proxy or load balancer
            'HTTP_X_FORWARDED',  // For proxy-based routing
            'HTTP_X_CLUSTER_CLIENT_IP', // When behind certain load balancers or clusters
            'HTTP_FORWARDED_FOR', // Another forwarded IP header
            'HTTP_FORWARDED',     // Yet another forwarded header
            'REMOTE_ADDR'         // Direct IP address
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ipList = explode(',', $_SERVER[$key]);
                foreach ($ipList as $ip) {
                    $ip = trim($ip); // Trim any whitespace

                    // Validate each IP
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                        return $ip; // Return first valid IPv4
                    } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
                        return $ip; // Return first valid IPv6
                    }
                }
            }
        }
        return "Unkown_IP_Address"; // Return null if no valid IP is found
    }
    public static function isTemporaryEmail($email, $conn)
    {
        // Get the domain part of the email in lowercase
        $domain = strtolower(substr(strrchr($email, "@"), 1));

        // Prepare SQL query to check if the domain exists in the database
        $stmt = $conn->prepare("SELECT COUNT(*) FROM temporary_email_domains WHERE domain = ?");

        // Bind the domain parameter
        $stmt->bind_param("s", $domain);

        // Execute the statement
        $stmt->execute();

        // Get the result and fetch the count
        $result = $stmt->get_result();
        $row = $result->fetch_array();
        $count = $row[0];

        // Close the statement
        $stmt->close();

        // Return true if count is greater than 0, meaning the email is temporary
        return $count > 0;
    }



}

class BalanceManager
{
    private static $conn;

    // Establish DB connection (called internally)


    private static function connect()
    {
        if (!self::$conn) {
            require BaseDir::getFullPath('config/database.php');
            self::$conn = $conn; // Calls the private constructor to initialize the connection
        }
        return self::$conn;
    }

    // method to get user balance by user_id
    function getUserBalance($user_id)
    {
        $conn = self::connect();
        $time = Detect::formatedCTime();
        $query = "SELECT balance FROM user_balances WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['balance'];
        } else {
            $amount = 0;
            $amount = number_format((float) $amount, 2, '.', '');
            return $amount;  // Default balance if no record found
        }
    }

    // method to create or update a user's balance
    function updateUserBalance($user_id, $amount)
    {
        if (!is_numeric($amount)) {
            return false; // Invalid input, return false or handle error
        }

        // Convert to float and ensure 2 decimal precision
        $amount = number_format((float) $amount, 2, '.', '');

        $conn = self::connect();
        $time = Detect::formatedCTime();
        // Check if user_id exists in the user_balances table
        $query = "SELECT user_id FROM user_balances WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            // User exists, update balance
            $updateQuery = "UPDATE user_balances SET balance = balance + ?, modified_at = ? WHERE user_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);

            mysqli_stmt_bind_param($updateStmt, "di", $amount, $user_id);
            return mysqli_stmt_execute($updateStmt);
        } else {
            // User does not exist, insert new row
            $insertQuery = "INSERT INTO user_balances (user_id, balance, modified_at,created_at) VALUES (?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "id", $user_id, $amount, $time, $time);
            return mysqli_stmt_execute($insertStmt);
        }
    }
    public function depositUserBalance($user_id, $amount)
    {
        $conn = self::connect();
        $time = Detect::formatedCTime();

        if (!is_numeric($amount)) {
            return false; // Invalid input, return false or handle error
        }

        // Convert to float and ensure 2 decimal precision
        $amount = number_format((float) $amount, 2, '.', '');

        // Check if user_id exists in the user_balances table
        $query = "SELECT id FROM user WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) !== 1) {
            return false;
        }
        // Check if user_id exists in the user_balances table
        $query = "SELECT user_id FROM user_balances WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            // User exists, update balance by adding the amount
            $updateQuery = "UPDATE user_balances SET balance = balance + ?, modified_at = ? WHERE user_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "dsi", $amount, $time, $user_id);

            return mysqli_stmt_execute($updateStmt);
        } else {
            // User does not exist, insert new row
            $insertQuery = "INSERT INTO user_balances (user_id, balance, modified_at, created_at) VALUES (?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "idss", $user_id, $amount, $time, $time);

            return mysqli_stmt_execute($insertStmt);
        }

    }

    // Method to withdraw funds from a user's balance
    public function withdrawUserBalance($user_id, $amount)
    {
        $conn = self::connect();
        $time = Detect::formatedCTime();
        if (!is_numeric($amount)) {
            return false; // Invalid input, return false or handle error
        }

        // Convert to float and ensure 2 decimal precision
        $amount = number_format((float) $amount, 2, '.', '');

        // Get the user's current balance
        $query = "SELECT balance FROM user_balances WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $currentBalance = $row['balance'];

            // Check if there is enough balance for withdrawal
            if ($amount > $currentBalance) {
                return false; // Insufficient balance for withdrawal
            }

            // Update the balance by subtracting the amount
            $updateQuery = "UPDATE user_balances SET balance = balance - ?, modified_at = ? WHERE user_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "dsi", $amount, $time, $user_id);

            return mysqli_stmt_execute($updateStmt);
        } else {
            // User does not exist, cannot withdraw
            return false; // No balance record, so withdrawal is not possible
        }
    }

    // method to reset user balance (optional for admin)
    function resetUserBalance($user_id)
    {
        $conn = self::connect();
        $time = Detect::formatedCTime();

        $query = "UPDATE user_balances SET balance = 0, modified_at = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $time, $user_id);
        return mysqli_stmt_execute($stmt);
    }

    // method to get all balances (for admin)
    function getAllBalances($conn)
    {
        $conn = self::connect();
        $time = Detect::formatedCTime();
        $query = "SELECT user_id, balance FROM user_balances";
        $result = mysqli_query($conn, $query);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

class Controller
{
    public static function view($path, $data = [])
    {
        // Logic to include the specified view file
        include __DIR__."../../views/{$path}.php";
    }

}
