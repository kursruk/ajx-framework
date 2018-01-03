<?php
 /* Fedotov Vitaliy (c) Ulan-Ude 2017 | kursruk@yandex.ru */
 // include(SYS_PATH.'lib/params.php');
 // include(SYS_PATH.'lib/phpmailer.php');
 include(SYS_PATH.'lib/ajaxmodel.php');
 
 class ajxphotos extends wAjaxModel
 {  
    function ajxModel()
    {   $this->includePageLocales(__DIR__);
        if (!isset($this->cfg->user) || !isset($this->cfg->user->user->id))
        return $this->error(T("ERR_NOT_AUTHORIZED"), true);
        $this->processModel(__DIR__);
    }
   
    
    function loadSettings($key)
    {  $db = $this->cfg->db;
       $qr = $db->query("select json from settings where name=:name",array('name'=>$key));
       $s = $db->fetchSingleValue($qr);
       if ($s!=null) return json_decode($s);
       return null;
    }
    
    function saveSettings($key)
    {  $db = $this->cfg->db;
       $params = (object)$_POST;
       if (isset($params->data))
       try
       { $qr = $db->query("insert into settings (name,json) values (:name,:json)",
           array('name'=>$key,'json'=>json_encode($params->data)));
       } catch(Exception $e)
       {  $qr = $db->query("update settings set json=:json where name=:name",
           array('name'=>$key,'json'=>json_encode($params->data)));
       }  
       $this->res->info = T('SAVED');
    }

 }

?>
