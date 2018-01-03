<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2017 | kursruk@yandex.ru */
  class photos extends wPage
  {  function __construct($cfg, $path, $seg=null)
     {  $this->cfg = $cfg;
        $this->includePageLocales(__DIR__);
        $cfg->title = 'Photos';
        $this->cpage = '';
        $this->seg = $seg;
        $this->nav ='';
        if (isset($seg[1])) 
        { $cpage = __DIR__."/{$seg[1]}.php";
          if (!file_exists($cpage)) header("HTTP/1.0 404 Not Found");
          else 
          { $this->cpage = $cpage;
            $this->nav = $seg[1];
            $cfg->title = T($this->nav);
          }
        } else $this->cpage=__DIR__."/index.php";
       $this->cfg->addJs('/js', 'formvalidator.js');
       $this->cfg->addJs('/js', 'models.js');
       
       switch ($this->nav)
       {
           case 'upload':
                $this->cfg->addJs('/html.php/pages/sales','previewCSV.js');
                $this->cfg->addJs('/html.php/pages/sales','upload.js');    
           break;           
           case 'sic':
                $this->cfg->addJs('/html.php/pages/sales','download.js');
                $this->cfg->addJs('/html.php/pages/sales','sic.js');
           break;           
           case 'subsector':
                $this->cfg->addJs('/html.php/pages/sales','download.js');
                $this->cfg->addJs('/html.php/pages/sales','subsector.js');
           break; 
           case 'companies':
               $this->cfg->addJs('/html.php/pages/sales','lookup.js');
               $this->cfg->addJs('/html.php/pages/sales','companies.js');
               $this->cfg->addJs('/bootstrap-3.3.6','bootstrap3-typeahead.min.js');
           break;
           case 'market-ranking':
               $this->cfg->addJs('/html.php/pages/sales','market-ranking.js'); 
           break;
           case 'market-summary':
               $this->cfg->addJs('https://code.highcharts.com','highcharts.js');
               $this->cfg->addJs('https://code.highcharts.com','highcharts-more.js');
               $this->cfg->addJs('https://code.highcharts.com/modules','exporting.js');
               $this->cfg->addJs('/html.php/pages/sales','lookup.js');
               $this->cfg->addJs('/html.php/pages/sales','market-summary.js'); 
           break;                      
           default:            
              $this->cfg->addJs('/html.php/pages/photos','photos.js');           
       }

     }
     
     function afterInit()
     {  $user = $this->cfg->user->user;
        $page = '';
        if (isset($this->seg[1])) $page = $this->seg[1];   
        
        $nogroup = !($this->cfg->inGroup('admin') 
         ||  $this->cfg->inGroup('user')
         ||  $this->cfg->inGroup('editor') );
        
        $public_page = ($page=='deny'|| $page=='themeA'|| $page=='themeB' 
            || $page=='themeC'|| $page=='themeD');
            
        if (empty($user) && !$public_page) header('Location: '.mkURL('/login'));
        else if ($nogroup && !$public_page) header('Location: '.mkURL('/photos/deny'));
        
        // check permissions
        $this->allow_edit =  ($this->cfg->inGroup('admin') || $this->cfg->inGroup('editor')  );
     }
     
     function display()
     {  $user = $this->cfg->user->user;
        if ($this->cpage!='') include($this->cpage);        
     }
  }
?>
