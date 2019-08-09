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
   {   $id = null;
       if (isset($_SESSION['conf_id'])) $id = $_SESSION['conf_id']; 
       return $id;
   }
   
    function ajxGetConfigId() 
    {  $this->res->id = $this->getConfId();
       echo json_encode($this->res);
    }
    
    function ajxSetConfigId() 
    {  $id = post('id', null);
       if ($id>0) $_SESSION['conf_id'] = $id;
       $this->res->id = $id;
       echo json_encode($this->res);
    }
    
    function ajxCreateView()
    {  $table = post('table', null);
       $db = $this->cfg->db;
       $name = $table;
       $i = 0;
       // Поиск свободного имени 
       do 
       {  $qr = $db->query('select id from md_views where name=:name', ['name'=>$name] );
          $r =  $db->fetchSingle($qr);
          if (empty($r)) break;
          $i++;
          $name = "$name$i";         
       } while (!empty($r));
       
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
" where TABLE_SCHEMA=:database_name and IS_NULLABLE='NO' ".
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
         $n->nodes[] = $tn;
         $tn->id = $t[0];
       }
       
       $tree[] = $n;

       $this->res->tree = $tree;
       
       echo json_encode($this->res);
    }
    
    function ajxLoadView()
    {  $id = post('id', null);
       if ($id!=null)
       { $db = $this->cfg->db;
         $qr = $db->query('select * from md_views where  id=:id', array('id'=>$id) );
         $this->res->view =  $db->fetchSingle($qr);
         
         $qr = $db->query('select * from md_fields where view_id=:id order by id', array('id'=>$id) );
         $this->res->fields = $qr->fetchAll(PDO::FETCH_OBJ);

         echo json_encode($this->res);
       } else $this->error('Отстутствует обязательный параметр id',5001);
    }
    
    function ajxSaveFields()
    {  $this->res->type = 'fld';
       $db = $this->cfg->db;
       foreach ($_POST as $id=>$obj)
       {  $db->updateObject('md_fields',$obj, array('id'=>$id) );  // Обновим данные
       }
       echo json_encode($this->res);
    }

    function ajxSaveView()
    {  $this->res->type = 'view';
       $id = $this->seg[3];
       $db = $this->cfg->db;
       $this->res->id = $id;
       $this->res->dara = $_POST;
       $db->updateObject('md_views',$_POST, array('id'=>$id) );  // Обновим данные
       echo json_encode($this->res);
    }

 }

?>
