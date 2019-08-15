<?php
  class confer extends wPage
  {  function confer($cfg, $path, $seg=null)
     {  $cfg->title = 'Configurator';
        $this->path = $path.'/index.php';
        $this->cfg = $cfg;
        $this->includePageLocales(__DIR__);
        $this->cfg->addJs('/bootstrap-3.3.6','bootstrap-treeview.min.js');
        $this->cfg->addJs('/js', 'formvalidator.js');
        $this->cfg->addJs('/js', 'models.js');
        $this->cfg->addJs('/js', 'jquery.sortable.min.js');
        $this->cfg->addJs('/html.php/pages/confer','confer.js');
        $this->cfg->addCSS('/bootstrap-3.3.6','bootstrap-treeview.min.css');
        $this->cfg->addCSS('/html.php/pages/confer','confer.css');
        
     }
     function display()
     { if ($this->cfg->inGroup('admin'))
       {  // echo '<h1>'.T($this->cfg->title).'</h1>';
           parent::display();
       } else  $this->cfg->setError('Войдите с правами администратора!', 404);
     }
  }
?>
