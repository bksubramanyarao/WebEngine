<?php
namespace WebEngine\Plugin;

class Email
{
    
    private $engine;
    
    // hooks
    private $after_mail_send = [];
    
    function __construct($engine) 
    {
        $this->_engine = $engine;
    }
    
    public function addHook($hookname, $func) 
    {
        if (isset($this->{$hookname})) {
            $this->{$hookname}[] = $func; 
        }
    }
    
    public function send($opts) 
    {
        // get html
        $html = $this->_engine->get_output_template($opts["template"], $opts["data"]);
        // send mail
        $result = mail($opts["to"], $opts["subject"], $html);
        
        // execute hooks
        $opts = [$this->_engine, date("Y-m-d H:i:s"), $opts["to"], $opts["subject"], $html, $result];
        $this->_engine->executeHook($this->after_mail_send, $opts);
    }
}
