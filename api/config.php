<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once __DIR__ . '/constant.php';
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL & ~E_NOTICE);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@ini_set("display_startup_errors", "1");
@ini_set('display_errors', 'On');
@ini_set('error_reporting', 1);
@ini_set('error_reporting', E_ALL);

class Connection
{
    private $db;
    public function __construct()
    {
        global $dbHost, $dbName, $dbUsername, $dbPassword;
        $this->db = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUsername, $dbPassword);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->db->exec("SET NAMES utf8mb4");
    }
    public function __destruct()
    {
        $this->db = null;
    }
    public function getConnection()
    {
        return $this->db;
    }
    public function getAll($table)
    {
        $sql = "SELECT * FROM $table";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getAllFromWhere($table, $column, $value, $order = null)
    {
        $sql = "SELECT * FROM $table WHERE $column = :value";
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getAllFromWhere2($table, $column, $value, $column2, $value2, $order = null)
    {
        $sql = "SELECT * FROM $table WHERE $column = :value AND $column2 = :value2";
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':value2', $value2);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}


function getSessionToken($db, String $username, int $id)
{
    $created_at = date('Y-m-d H:i:s');
    $token = md5($username . $id . $created_at);
    // more than 2 days old
    $sql = "DELETE FROM `sessions` WHERE AuthId = :id AND created_at < DATE_SUB(NOW(), INTERVAL 2 DAY)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $ip = getClientIP();
    $sql = "INSERT INTO `sessions` (`AuthId`, `AuthUsername`, `AuthKey`, `created_at`, `ip_addr`) VALUES (:id, :username, :token, :created_at, :ip)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':created_at', $created_at);
    $stmt->bindParam(':ip', $ip);
    $stmt->execute();
    return $token;
}

function validateSessionToken($db, String $token, $username = null)
{
    $sql = "SELECT * FROM `sessions` WHERE `AuthKey` = :token LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($stmt->rowCount() > 0) {
        if ($result['AuthUsername'] == $username) {
            return $result;
        }
    }
    return false;
}



class BrowserDetection
{

    private $_user_agent;
    private $_name;
    private $_version;
    private $_platform;

    private $_basic_browser = array(
        'Trident\/7.0' => 'Internet Explorer 11',
        'Beamrise' => 'Beamrise',
        'Opera' => 'Opera',
        'OPR' => 'Opera',
        'Shiira' => 'Shiira',
        'Chimera' => 'Chimera',
        'Phoenix' => 'Phoenix',
        'Firebird' => 'Firebird',
        'Camino' => 'Camino',
        'Netscape' => 'Netscape',
        'OmniWeb' => 'OmniWeb',
        'Konqueror' => 'Konqueror',
        'icab' => 'iCab',
        'Lynx' => 'Lynx',
        'Links' => 'Links',
        'hotjava' => 'HotJava',
        'amaya' => 'Amaya',
        'IBrowse' => 'IBrowse',
        'iTunes' => 'iTunes',
        'Silk' => 'Silk',
        'Dillo' => 'Dillo',
        'Maxthon' => 'Maxthon',
        'Arora' => 'Arora',
        'Galeon' => 'Galeon',
        'Iceape' => 'Iceape',
        'Iceweasel' => 'Iceweasel',
        'Midori' => 'Midori',
        'QupZilla' => 'QupZilla',
        'Namoroka' => 'Namoroka',
        'NetSurf' => 'NetSurf',
        'BOLT' => 'BOLT',
        'EudoraWeb' => 'EudoraWeb',
        'shadowfox' => 'ShadowFox',
        'Swiftfox' => 'Swiftfox',
        'Uzbl' => 'Uzbl',
        'UCBrowser' => 'UCBrowser',
        'Kindle' => 'Kindle',
        'wOSBrowser' => 'wOSBrowser',
        'Epiphany' => 'Epiphany',
        'SeaMonkey' => 'SeaMonkey',
        'Avant Browser' => 'Avant Browser',
        'Firefox' => 'Firefox',
        'Chrome' => 'Google Chrome',
        'MSIE' => 'Internet Explorer',
        'Internet Explorer' => 'Internet Explorer',
        'Safari' => 'Safari',
        'Mozilla' => 'Mozilla'
    );

    private $_basic_platform = array(
        'windows' => 'Windows',
        'iPad' => 'iPad',
        'iPod' => 'iPod',
        'iPhone' => 'iPhone',
        'mac' => 'Apple',
        'android' => 'Android',
        'linux' => 'Linux',
        'Nokia' => 'Nokia',
        'BlackBerry' => 'BlackBerry',
        'FreeBSD' => 'FreeBSD',
        'OpenBSD' => 'OpenBSD',
        'NetBSD' => 'NetBSD',
        'UNIX' => 'UNIX',
        'DragonFly' => 'DragonFlyBSD',
        'OpenSolaris' => 'OpenSolaris',
        'SunOS' => 'SunOS',
        'OS\/2' => 'OS/2',
        'BeOS' => 'BeOS',
        'win' => 'Windows',
        'Dillo' => 'Linux',
        'PalmOS' => 'PalmOS',
        'RebelMouse' => 'RebelMouse'
    );

    function __construct($ua = '')
    {
        if (empty($ua)) {
            $this->_user_agent = (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT'));
        } else {
            $this->_user_agent = $ua;
        }
    }

    function detect()
    {
        $this->detectBrowser();
        $this->detectPlatform();
        return $this;
    }

    function detectBrowser()
    {
        foreach ($this->_basic_browser as $pattern => $name) {
            if (preg_match("/" . $pattern . "/i", $this->_user_agent, $match)) {
                $this->_name = $name;
                // finally get the correct version number
                $known = array('Version', $pattern, 'other');
                $pattern_version = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
                if (!preg_match_all($pattern_version, $this->_user_agent, $matches)) {
                    // we have no matching number just continue
                }
                // see how many we have
                $i = count($matches['browser']);
                if ($i != 1) {
                    //we will have two since we are not using 'other' argument yet
                    //see if version is before or after the name
                    if (strripos($this->_user_agent, "Version") < strripos($this->_user_agent, $pattern)) {
                        @$this->_version = $matches['version'][0];
                    } else {
                        @$this->_version = $matches['version'][1];
                    }
                } else {
                    $this->_version = $matches['version'][0];
                }
                break;
            }
        }
    }

    function detectPlatform()
    {
        foreach ($this->_basic_platform as $key => $platform) {
            if (stripos($this->_user_agent, $key) !== false) {
                $this->_platform = $platform;
                break;
            }
        }
    }

    function getBrowser()
    {
        if (!empty($this->_name)) {
            return $this->_name;
        } else {
            return 'Unknown';
        }
    }

    function getVersion()
    {
        return $this->_version;
    }

    function getPlatform()
    {
        if (!empty($this->_platform)) {
            return $this->_platform;
        } else {
            return 'Unknown';
        }
    }

    function getUserAgent()
    {
        return $this->_user_agent;
    }

    function getInfo()
    {
        return "<strong>Browser Name:</strong> {$this->getBrowser()}<br/>\n" .
            "<strong>Browser Version:</strong> {$this->getVersion()}<br/>\n" .
            "<strong>Browser User Agent String:</strong> {$this->getUserAgent()}<br/>\n" .
            "<strong>Platform:</strong> {$this->getPlatform()}<br/>";
    }
}

function getIpAddress()
{
    //whether ip is from the share internet
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    //whether ip is from the proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    //whether ip is from the remote address
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function random_num($length)
{
    $text = "";
    if ($length < 5) {
        $length = 5;
    }
    $len = rand(4, $length);
    for ($i = 0; $i < $len; $i++) {
        $text .= rand(0, 9);
    }
    return $text;
}

function getClientIP()
{
    $ipAddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipAddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipAddress = 'UNKNOWN';
    return $ipAddress;
}


function createSlug($string)
{
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9_\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', ' ', $string);
    $string = preg_replace('/[\s_]/', '-', $string);
    return $string;
}

function getIdFromYoutubeUrl($url)
{
    $regex = '#^(?:https?://)?' . // http(s)://
        '(?:www\.)?' . // www.
        '(?:m\.)?' . // m.
        '(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))' . // youtu.be/
        '([\w-]{11})' . // youtube id .e.g. 8U4Yce6_xjY
        '(?:.+)?$#x';
    $result = preg_match($regex, $url, $matches);
    if ($result) {
        return $matches[1];
    } else {
        return false;
    }
}
function validateSession($db)
{
    if (!isset($_SESSION)) {
        session_start();
    }
    if (isset($_SESSION['token'])) {
        return true;
    } else if (isset($_COOKIE['token']) && isset($_COOKIE['email'])) {
        $token = $_COOKIE['token'];
        $email = $_COOKIE['email'];
        $userAuth = validateSessionToken($db, $token, $email);
        if ($userAuth) {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['id'] = $result["id"];
            $_SESSION['email'] = $result["email"];
            $_SESSION['fullname'] = $result["fullname"];
            $_SESSION['username'] = $result["username"];
            $_SESSION['email'] = $result["email"];
            $_SESSION['role'] = $result["role"];
            $_SESSION['token'] = $token;
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}



function track($db)
{

    $activeUrl = "";
    // get the current page url
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $activeUrl = $_SERVER['SCRIPT_NAME'];
    } else if (isset($_SERVER['PHP_SELF'])) {
        $activeUrl = $_SERVER['PHP_SELF'];
    } else if (isset($_SERVER['ORIG_SCRIPT_NAME'])) {
        $activeUrl = $_SERVER['ORIG_SCRIPT_NAME'];
    } else if (isset($_SERVER['ORIG_PHP_SELF'])) {
        $activeUrl = $_SERVER['ORIG_PHP_SELF'];
    } else {
        $activeUrl = "index.php";
    }
    // split the url into an array
    $activeUrl = explode('/', $activeUrl);
    // get the last element of the array
    $activeUrl = $activeUrl[count($activeUrl) - 1];
    $detection = new BrowserDetection();
    $sql = "INSERT INTO `page_views` (`page`, `ip`, `date`, `time`,`referer`, `session_id`,`device`,`browser`,`agent`) VALUES (:page, :ip, :date, :time, :referer, :session_id, :device, :browser, :agent)";
    $params = array(
        ':page' => $activeUrl,
        ':ip' => getIpAddress(),
        ':date' => date('Y-m-d'),
        ':time' => date('H:i:s'),
        ':referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
        ':session_id' => session_id(),
        ':device' => $detection->detect()->getPlatform(),
        ':browser' => $detection->detect()->getBrowser(),
        ':agent' => $detection->detect()->getUserAgent(),
    );
    $db->prepare($sql)->execute($params);
}
function getKeyValue($key, $settings, $isImage = false)
{
    global $webAddress;
    for ($i = 0; $i < count($settings); $i++) {
        if ($settings[$i]['name'] == $key) {
            if ($isImage) {
                return $webAddress . 'uploads/images/' . $settings[$i]['value'];
            }
            return $settings[$i]['value'];
        }
    }
    return '';
}
