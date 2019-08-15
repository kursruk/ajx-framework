<?php
 include(SYS_PATH.'lib/ajaxmodel.php');
   
 class ajxconfer extends wAjaxModel
 {      
    function ajxModel()
    {   $this->includePageLocales(__DIR__);
        if (!isset($this->cfg->user) || !isset($this->cfg->user->user->id))
        return $this->error(T("ERR_NOT_AUTHORIZED"), true);
        $this->processModel(__DIR__);
    }
    
    function getConfId()
    {  $cf = $this->cfg->getUserConfig('confer');
       if ($cf!==null && isset($cf->conf_id)) return $cf->conf_id;       
       return null;
    }
   
    function ajxSetConfigId() 
    {  $cf = $this->cfg->getUserConfig('confer');
       if ($cf==null) $cf = new stdClass();
       $cf->conf_id = post('id', null);
       $this->cfg->setUserConfig('confer', $cf);
       $this->res->id = $cf->conf_id;
       echo json_encode($this->res);
    }
    
    function ajxGetConfigId() 
    {  $this->res->id = $this->getConfId();
       echo json_encode($this->res);
    }
    
    function findFreeName($table, $nameColumn, $nameStart)
    {  $db = $this->cfg->db;
       $i = 1;
       $name = $nameStart;
       // Поиск свободного имени 
       do 
       {  $qr = $db->query("select id from $table where $nameColumn=:name", ['name'=>$name] );
          $r =  $db->fetchSingle($qr);
          if (empty($r)) break;
          $i++;
          $name = "$nameStart$i";         
       } while (!empty($r));
       return $name;       
    }
    
    function ajxCreateView()
    {  $table = post('table', null);
       $db = $this->cfg->db;
       $name=$this->findFreeName('md_views', 'name', $table);
       $conf_id = $this->getConfId();       
       $dbname = $this->cfg->dbname;
       
       $qr = $db->query('insert into md_views (name,vtitle,tname,conf_id) values '.
    '(:name,:vtitle,:tname,:conf_id)',  ['name'=>$name, 'vtitle'=>$name, 'tname'=>$table, 'conf_id'=>$conf_id]);
       $v_id = $db->db->lastInsertId();
       
       // Add columns of the table
       $db->query('insert into md_fields (view_id, fname, ftitle)'.
' select :insid as view_id,   COLUMN_NAME as fname, COLUMN_NAME as ftitle'.
' from information_schema.columns'.
' where TABLE_SCHEMA=:database_name and TABLE_NAME=:tname',
      ['tname'=>$table, 'insid'=>$v_id, 'database_name'=>$dbname] );
       
       // Non required fields
       $db->query('update md_fields set required=0 '.
' where view_id=:view_id and fname in '.
' (select COLUMN_NAME from information_schema.columns '.
" where TABLE_SCHEMA=:database_name and IS_NULLABLE='YES' ".
' and TABLE_NAME=:tname);',['tname'=>$table, 'view_id'=>$v_id, 'database_name'=>$dbname]);

       // Set primary keys
       $db->query('update md_fields set pkey=1, visable=0 '.
' where view_id=:view_id and fname in '.
' (select COLUMN_NAME from information_schema.KEY_COLUMN_USAGE '.
" where TABLE_SCHEMA=:database_name and CONSTRAINT_NAME='PRIMARY' ".
' and TABLE_NAME=:tname);',['tname'=>$table, 'view_id'=>$v_id, 'database_name'=>$dbname]);

       // Hide foreign keys
       $db->query('update md_fields set visable=0, ingrid=0 '.
' where view_id=:view_id and fname in '.
' (select COLUMN_NAME from information_schema.KEY_COLUMN_USAGE '.
' where TABLE_SCHEMA=:database_name and REFERENCED_TABLE_NAME is not null '.
' and TABLE_NAME=:tname);',['tname'=>$table, 'view_id'=>$v_id, 'database_name'=>$dbname]);


       $this->res->name = $name;
       $this->res->id = $v_id;
       
       
       echo json_encode($this->res);
    }


    
    function ajxLoad()
    {  $conf = post('conf', $this->cfg->md_conf);
       $db = $this->cfg->db;
       $qr = $db->query('show tables');
       $tables = $qr->fetchAll(PDO::FETCH_NUM);
       
       $state = new stdClass();
       $state->expanded = false;
       
       $tree = array();
       $n = new stdClass();
       $n->text = T('Tables');
       $n->state = $state;
       $n->nodes = array();
       foreach($tables as $t)
       { $tab = $t[0];
         if (substr($tab,0,3)!='md_')
         { $tn = new stdClass();
           $tn->text = $tab;
           $n->nodes[] = $tn;
         }
       }
       $tree[] = $n;

       $n = new stdClass();
       $n->text = T('Forms');
       $qr = $db->query('select id,vtitle from md_views where conf_id=:id order by 2', array('id'=>$conf) );
       $tables = $qr->fetchAll(PDO::FETCH_NUM);
       $n->nodes = array();
       foreach($tables as $t)
       { $tn = new stdClass();
         $tn->text = $t[1]; //.' ('.$t[0].')';
         $tn->icon = 'glyphicon glyphicon-trash';
         $n->nodes[] = $tn;
         $tn->id = $t[0];
       }
       
       $tree[] = $n;

       $this->res->tree = $tree;
       
       echo json_encode($this->res);
    }
    
    function appendViewTranslation($view_id)
    {  global $_TRANSLATIONS;
       $db = $this->cfg->db;         
       // Append text to translation
       $qr = $db->query('select json from md_view_translations '
       .' where view_id=:view_id and lang=:lang', 
       ['view_id'=>$view_id,'lang'=>$this->cfg->lang] ); 
       $tr = json_decode( $db->fetchSingleValue($qr) );
       if (!empty($tr)) $_TRANSLATIONS = array_merge($_TRANSLATIONS, (array)$tr);
    }
    
    function ajxGetFieldsByRef()
    {  $id = post('id', null);
       if ($id!=null)
       { $db = $this->cfg->db;
         $qr = $db->query('select id, fname, view_id from md_fields where view_id=:id and ingrid=1 and visable=1 order by id', ['id'=>$id] );
         $this->res->fields = $qr->fetchAll(PDO::FETCH_OBJ);
         if (isset($this->res->fields[0]))
         { $view_id = $this->res->fields[0]->view_id;
           $this->appendViewTranslation($view_id);
           $flds = $this->res->fields;
           foreach($flds as $k=>$v)
           {  $fname = $flds[$k]->fname;
              $flds[$k]->title = T($fname);
           }
         }
       } 
       echo json_encode($this->res);
    }

    function getView($id)
    {  $db = $this->cfg->db;
        $qr = $db->query('select * from md_views where id=:id',
        ['id'=>$id]);
        return $db->fetchSingle($qr);
    }
    
    function createAndGetReference()
    {  $master_view_id = post('master_view_id', null);
       $view_id = post('view_id', null);
       $conf_id = $this->getConfId();
       
       $db = $this->cfg->db;
       $qr = $db->query('select * from md_refs  '
       .' where conf_id=:conf_id and master_view_id=:master_view_id '
       .' and view_id=:view_id', ['master_view_id'=>$master_view_id,
       'conf_id'=>$conf_id, 'view_id'=>$view_id ] );
       
       $r = $db->fetchSingle($qr); 
       $ref_id = 0;
       // If reference is not exists then create it
       if (empty($r))       
       {  $w = $this->getView($view_id);
          $mw = $this->getView($master_view_id);
          
          if (empty($w)) throw new Exception( T("VIEW_NOT_FOUND") );
          if (empty($mw)) throw new Exception( T("MASTER_VIEW_NOT_FOUND") );
          $ref_name =  $this->findFreeName('md_refs', 'refname', $w->name.'->'.$mw->name );          
          $this->res->ref_name = $ref_name;
          $nr = new stdClass();
          $nr->conf_id = $conf_id;
          $nr->refname = $ref_name;
          $nr->rtitle = $ref_name;
          $nr->view_id = $view_id;
          $nr->master_view_id = $master_view_id;
          $db->insertObject('md_refs', $nr);
          $ref_id = $db->db->lastInsertId();
          
          // Add fields of the reference
          $db->query('insert into md_refs_fields (fk_field, pk_field, ref_id)
select cu.COLUMN_NAME as fk_field, cu.REFERENCED_COLUMN_NAME as pk_field, :ref_id as ref_id
from information_schema.REFERENTIAL_CONSTRAINTS as c
join  information_schema.KEY_COLUMN_USAGE as cu on c.CONSTRAINT_NAME=cu.CONSTRAINT_NAME
where c.CONSTRAINT_SCHEMA=:dbname and c.TABLE_NAME=:table AND c.REFERENCED_TABLE_NAME=:master_table',
        ['ref_id'=>$ref_id, 'dbname'=>$this->cfg->dbname, 
         'table'=>$w->tname,'master_table'=>$mw->tname]);
         
                  
                    
       } else $ref_id = $r->id;
       return $ref_id;
    }
    
    function ajxSaveFieldsOrder()
    {  $order = post('order', null);
       $db = $this->cfg->db;
       foreach($order as $n=>$id)
       {  $db->query('update md_fields set ordr=:n where id=:id',
               ['id'=>$id, 'n'=>($n+1)]);
       }
       echo json_encode($this->res);
    }

    function ajxDeleteField()
    {  $id = post('id', null);
       $db = $this->cfg->db;
       $db->query('delete from md_fields where id=:id',['id'=>$id]);
       $this->res->info = T('Deleted');
       echo json_encode($this->res);
    }

    function ajxAddFieldsByRef()
    {  $f_id = post('f_id', null);
       $view_id = 
       
       $db = $this->cfg->db;
       $ref_id = $this->createAndGetReference();
       // get field name 
       $qr = $db->query('select * from md_fields where id=:id',['id'=>$f_id]);        
       $f =  $db->fetchSingle($qr);
       if (empty($f)) throw new Exception(T('DISPLAY_FIELD_NOT_FOUND'));
              
       $nr = new stdClass();
       $nr->fname = 
            $this->findFreeName('md_fields', 'fname', 'lk_'.$f->fname);
       $nr->view_id =  post('view_id', null);
       $nr->ref_id = $ref_id;
       $nr->widget_id = 1; 
       $db->insertObject('md_fields', $nr);
       $field_id = $db->db->lastInsertId();
       
       $nl = new stdClass();
       $nl->display_field_id = $f_id;
       $nl->field_id = $field_id;
       $db->insertObject('md_lookups', $nl);
       $this->res->info = T('LOOKUP_FIELD_CREATED');
       echo json_encode($this->res);
    }
    
    function ajxLoadView()
    {  $id = post('id', null);
       if ($id!=null)
       { $db = $this->cfg->db;
         $qr = $db->query('select * from md_views where  id=:id', ['id'=>$id] );
         $this->res->view =  $db->fetchSingle($qr);
         
         $qr = $db->query('select * from md_fields where view_id=:id order by ordr, id', ['id'=>$id] );
         $this->res->fields = $qr->fetchAll(PDO::FETCH_OBJ);
            
         $dbname = $this->cfg->dbname;
         $sql  = 'select v.id, v.name, UPDATE_RULE as on_update,DELETE_RULE as on_delete'
                 .' from information_schema.REFERENTIAL_CONSTRAINTS r'
                 .' join md_views v on v.conf_id=:conf_id and r.REFERENCED_TABLE_NAME=v.tname'
                 .' where CONSTRAINT_SCHEMA=:dbname and TABLE_NAME=:tname';
         
         $qr = $db->query($sql, ['tname'=>$this->res->view->tname,
          'dbname'=>$dbname, 'conf_id'=>$this->res->view->conf_id] );
         $this->res->refs = $qr->fetchAll(PDO::FETCH_OBJ);
    

         echo json_encode($this->res);
       } else $this->error('Отстутствует обязательный параметр id',5001);
    }
    
    function ajxLoadViewTranslation()
    {  $id = post('view', null);
       $lang = $this->cfg->lang;
       $db = $this->cfg->db;       
       $qr = $db->query("select json from md_view_translations where view_id=:view_id and lang=:lang",
         ['view_id'=>$id, 'lang'=>$lang]
       );
       $j = $db->fetchSingleValue($qr);
       if (!empty($j))
       {  $this->res->data = json_decode($j);          
       } else $this->res->data = new stdClass();
       echo json_encode($this->res);
    }
     
    function ajxSaveViewTranslation()
    {  $id = post('view', null);
       $data = post('data', []);
       $lang = $this->cfg->lang;
            
       $db = $this->cfg->db;       
       $qr = $db->query("select json from md_view_translations where view_id=:view_id and lang=:lang",
         ['view_id'=>$id, 'lang'=>$lang]
       );
       $j = $db->fetchSingleValue($qr);
       $json = json_encode($data, JSON_UNESCAPED_UNICODE);
       if (empty($j)) 
       {  $db->query("insert into md_view_translations (view_id, lang, json) values 
          ( :view_id, :lang, :json)",
            ['view_id'=>$id, 'lang'=>$lang, 'json'=>$json]
          );
       } else
       {  $db->query("update md_view_translations set json=:json 
          where view_id=:view_id and lang=:lang;",
            ['view_id'=>$id, 'lang'=>$lang, 'json'=>$json]
          );
       }
       $this->res->lang = $lang;
       $this->res->info=T('Saved');
       echo json_encode($this->res);
    }
    
    function ajxSaveFields()
    {  $this->res->type = 'fld';
       $db = $this->cfg->db;
       foreach ($_POST as $id=>$obj)
       {  foreach($obj as $k=>$v)
          { if ($v=='') $obj[$k] = null;
          }
          $db->updateObject('md_fields',$obj, array('id'=>$id) );  // Обновим данные
       }
       $this->res->info=T('Saved');
       echo json_encode($this->res);
    }

    function ajxSaveView()
    {  $this->res->type = 'view';
       $id = $this->seg[3];
       $db = $this->cfg->db;
       $this->res->id = $id;
       $this->res->dara = $_POST;
       $db->updateObject('md_views',$_POST, array('id'=>$id) );  // Обновим данные
       $this->res->info=T('Saved');
       echo json_encode($this->res);
    }

 }

?>
