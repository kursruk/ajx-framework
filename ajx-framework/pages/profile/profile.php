<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class profile extends wPage
  {  function profile($cfg, $path, $seg=null)
     {  $cfg->title = 'Profile';
        $this->cfg = $cfg;
        $this->cfg->addJs('/js','formvalidator.js');
        $this->cfg->addJs('/js', 'profile.js');
     }
     
     function display()
     { //  echo '<center><h1>'.T($this->cfg->title).'</h1></center>';
        echo '<div id="profile"></div>';
     }
  }
?>
