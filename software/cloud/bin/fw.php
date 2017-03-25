<?php
//date_default_timezone_set('UTC');
date_default_timezone_set('Africa/Algiers');
setlocale(LC_MONETARY, 'dz_DZA');
session_start();

require_once('fw_set.php');

if (isset($_SESSION['user']))
{
    $id = $_SESSION['user']->id;
    $fw->fetchAll("UPDATE user SET user.date_login=current_timestamp WHERE id=$id");
}

// ---------------------------------------

class FireWorks{

    private static $databases;
    private $connection;
    public $tb_user = "user";
    public $tb_log  = "log";
    public $telegram_api;
    public $telegram_id;


    public function __construct($connDetails){
        if(!is_object(self::$databases[$connDetails])){
            list($host, $user, $pass, $dbname) = explode('|', $connDetails);
            $dsn = "mysql:host=$host;dbname=$dbname";
            self::$databases[$connDetails] = new PDO($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        }
        $this->connection = self::$databases[$connDetails];
    }

    // RUN SQL =========================================================
    public function fetchAll($sql){
        $args = func_get_args();
        array_shift($args);
        $statement = $this->connection->prepare($sql);
        $statement->execute($args);
        return $statement->fetchAll(PDO::FETCH_OBJ);
    }

    function inj_sql($value)
    {
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
        return str_replace($search, $replace, $value);
    }

    // LOG =============================================================
    public function log($msg, $type = null)
    {
        //$sql = "INSERT INTO $this->tb_log (ip,username,type,msg) VALUES ( '$_SERVER[REMOTE_ADDR]','".(isset($_SESSION['user']) ? $_SESSION['user']->email : "guest")."','$type','".htmlentities($msg)."')";
        //$this->fetchAll($sql);

        // add to message to the end of log.csv 
        // + date; ip; type; msg;
    }

    // TELEGRAM ========================================================
    public function telegram($message)
    {
        $message = htmlentities($message);
        $result = file_get_contents("https://api.telegram.org/bot$telegram_api/sendMessage?chat_id=$telegram_id&text=$message");
        //$result = json_decode($result, true);
    }

    // AVATAR ==========================================================
    public function gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&d=$d&r=$r";
        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }

    // LOGIN ==========================================================
    public function login( $username=null, $password=null ) {
        $ret = null;
        if (!$username && !$password)
        {
            if (isset($_SESSION['user']) && $_SESSION['user'] != false )
                $ret = $_SESSION['user'];
        }
        else
        {
            $username = $this->inj_sql($username);
            $password = $this->inj_sql($password);

            $sql = "SELECT * FROM $this->tb_user WHERE ( username='$username' OR email='$username' ) AND password='".sha1($password)."'";

            $result =  $this->fetchAll($sql);
            if (isset($result[0]) ){
                $ret = $result[0];
                unset($ret->password);
                $ret->gravatar = $this->gravatar($ret->email);
                session_cache_limiter('private');
                session_cache_expire(60);                   // set the cache expire to 5 minutes
                $this->log( $username,"SIGNIN" );
            }else{
                session_destroy();                          // destroy last session
                $this->log( "$username : $password","DENIED" ) ;
            }
        }

        return $ret;
    }

    // PROFILE ========================================================
    public function profile( $id ) {
        
        $ret =  $this->fetchAll("SELECT * FROM user3 WHERE id=$id");
        if (isset($ret[0]) ){
            $ret = $ret[0];
            unset($ret->password);
            $ret->gravatar = $this->gravatar($ret->email);
        }else{
            $ret = false;
        }

        return $ret;
    }

    // LOGOUT =========================================================
    public function logout( ) {
        global $_SESSION;
        $_SESSION['user'] = null;
        unset($_SESSION['user']);
        session_destroy();                          // destroy last session
    }

    // PERMISSION =========================================================
    public function permission($sub_role) {
        global $_SESSION;
        $ret_access = false;
        if (strpos($_SESSION['user']->role, $sub_role) !== false)
            $ret_access = true;
        return $ret_access;
    }
}