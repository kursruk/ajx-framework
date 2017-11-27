<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class admin extends wPage
  {  function __construct($cfg, $path, $seg=null)
     {  $cfg->title = 'ADMIN_PANEL';
        $this->path = $path.'/index.php';
        $this->cfg = $cfg;
        $this->cfg->addJs('/js','formvalidator.js');
        $this->cfg->addJs('/js','models.js');
        $this->cfg->addJs('/html.php/pages/admin','admin.js');
        $this->seg = $seg;
     }
     
     function display()
     { if ( $this->cfg->inGroup('admin') )
       {   parent::display();
       } else $this->cfg->setError(T("AUTH_REQURED"), 1029);
     }
     
     function afterInit()
     {  $user = $this->cfg->user->user;
        if (empty($user)) header('Location: '.mkURL('/login?gotoURL=/'.implode('/',$this->seg)));        
     }
  }
?>
