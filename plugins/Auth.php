<?php
namespace Plugin;
class Auth
{
    
    private $machine;
    private $data_callback;
    
    public $logged_user_id;
    public $data;
    
    const AUTH_COOKIE_NAME = "KfjqrRAVhuJlzvX5ANWz";
    
    function __construct($machine) 
    {
        $this->machine = $machine;
    }
    
    public function setDataCallback($func) 
    {
        $this->data_callback = $func; 
    }
    
    public function generateAuthCookies($user_id) 
    {
        $state = $this->machine->getState();
        
        // generate a unique session code.
        $sessioncode = md5($this->machine->uuid());
        
        // set the auth cookie with the session code.
        setcookie(self::AUTH_COOKIE_NAME, $sessioncode, 0, "/");
        
        // save session in db.
        $this->machine->plugin("Database")->addItem(
            "loginsession", [
            "user_id" => $user_id,
            "sessioncode" => $sessioncode,
            "ip" => $state["SERVER"]["REMOTE_ADDR"],
            "created" => date("Y-m-d H:i:s")
            ]
        );
    }
    
    public function checkLogin() 
    {
        $this->logged_user_id = 0;
        
        // retrieve cookie value
        $state = $this->machine->getState();
        if (isset($state["COOKIE"][self::AUTH_COOKIE_NAME])) {
            $sessioncode = $state["COOKIE"][self::AUTH_COOKIE_NAME];
            // get the session in db
            $session = $this->machine->plugin("Database")->getItemByField("loginsession", "sessioncode", $sessioncode);
            if ($session) {
                // additional check based on ip
                if ($session->ip == $state["SERVER"]["REMOTE_ADDR"]) {
                    // return the user id
                    $this->logged_user_id = $session->user_id;
                    // execute data_callback
                    if ($this->data_callback) {
                        $this->data = call_user_func_array($this->data_callback, [$this->machine, $this->logged_user_id]);
                    }
                }
            }
        }
    }
}
