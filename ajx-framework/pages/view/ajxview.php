<?php
  class ajxview extends wAjax
  {
     
     function getConfId()
     {  $cf = $this->cfg->getUserConfig('confer');
        if ($cf!==null && isset($cf->conf_id)) return $cf->conf_id;       
        return null;
     }
   
      function calcTotal($tname, $wh='', $wk=array() )
      {  $db = $this->cfg->db;
         $sql = 'select count(*) from '.$tname.' r '.$this->joins.' '.$wh;
         if ($this->total_joins) $qr = $db->query($sql, $wk); else
         $qr = $db->query('select count(*) from '.$tname.' r '.$wh, $wk);
         $a = $qr->fetchAll(PDO::FETCH_NUM);
         return 1*$a[0][0];
      }
      
      function loadViewTranslation($view_id)
      {  $id = $view_id;
         $lang = $this->cfg->lang;
         $db = $this->cfg->db;       
         $qr = $db->query("select json from md_view_translations where view_id=:view_id and lang=:lang",
           ['view_id'=>$id, 'lang'=>$lang]
         );
         $j = $db->fetchSingleValue($qr);
         if (!empty($j))
         {  return json_decode($j);
         } 
         return new stdClass();         
      }
      
      function ajxload()
      {  if (!isset($this->seg[3])) return $this->error('Не передано название представления!',4001);
         $pg_rows = 7; // 1*post('pg_rows', $this->cfg->pg_rows);
         $v = $this->seg[3];
         $db = $this->cfg->db;
         $sql = $this->mkSQL($v, true).' limit '.$pg_rows;
         // $this->res->sql = $sql;
        
         $qr = $db->query($sql, $this->wk);
         $this->res->rows = $qr->fetchAll(PDO::FETCH_NUM);
         if (!isset($this->res->total)) $this->res->total= $this->calcTotal($this->tname);
         $this->res->pg_rows = $pg_rows;
         $this->res->locale = $this->loadViewTranslation( $this->res->id );
         echo json_encode($this->res);
      }

      // Загрузим подчинённое представление по коду ссылки
      function ajxloadChild()
      {  $ref = post('childref', null);
         if ($ref==null) return $this->error('Не передана ссылка!',4033);
         
         $db = $this->cfg->db;
         $pg_rows = 1*post('pg_rows', $this->cfg->pg_rows); 
         
         // Получим название представления по ссылке
         $sql = 'select v.name, v.vtitle from md_refs r join md_views v on r.view_id = v.id  where r.id=:id';
         $qr = $db->query($sql, array('id'=>$ref));
         $vr =  $db->fetchSingle($qr);
         $v = $vr->name;
         $this->res->view = $v;
      
         $sql = $this->mkSQL($v, true).' limit '.$pg_rows;
         $this->res->sql = $sql;
        
         $qr = $db->query($sql, $this->wk);
         $this->res->rows = $qr->fetchAll(PDO::FETCH_NUM);
         if (!isset($this->res->total)) $this->res->total= $this->calcTotal($this->tname);
    
         $this->res->pg_rows = $pg_rows;
         
         echo json_encode($this->res);
      }
            
      function ajxSaveView()
      { if (!isset($this->seg[3])) return $this->error('Не передано название представления!',4001);
        $v = $this->seg[3];
        $db = $this->cfg->db;
        $qr = $db->query('select id,vtitle,tname from md_views where name=:name and conf_id=:id',
            ['name'=>$v, 'id'=>$this->getConfId() ] 
        );
        $vr =  $db->fetchSingle($qr);
        $insert = post('insert', false);
        $data = $_POST['row'];
        foreach($data as $k=>$v)
        { if ($v=='') $data[$k]=NULL;
        }
        try
        {
           if ($insert) $db->insertObject($vr->tname, $data); else
           $db->updateObject($vr->tname, $data, $_POST['keys']);
        } catch (PDOException $e)
        { $errno = $e->getCode();
          $errmsg = $e->getMessage();
          $tr = $this->loadViewTranslation($vr->id);          
          $this->includePageLocales(__DIR__);
          // Error translation
          switch ($errno)
          {  case 23000:
             case 'HY000':                  
                  $fa =  explode("'", $errmsg);
                  if (strpos($errmsg, 'Duplicate entry')>0)
                  {  $f = $fa[3];
                     $errmsg = sprintf( T('ERR_DUPLICATE_ENTRY'), $tr->$f, $fa[1]);
                  } else 
                  {  $f = $fa[1];
                     $errmsg = T('ERR_REQUIRED_VALUE').' '.$tr->$f;
                  }
             break; 
             case 22001:
                  $fa =  explode("'", $errmsg);
                  $f = $fa[1];
                  $errmsg = T('ERR_STRING_TRUNCATED').' '.$tr->$f;                  
             break;             
          }          
          return $this->error($errmsg, $errno);
        }        
        echo json_encode($this->res);
      }
      
      function ajxRef()
      {  $ref = post('ref',0);
         if ($ref<0) return $this->error('Не передан код ссылки!',4010);
         $db = $this->cfg->db;
         $qr = $db->query('select v.id, v.name, v.vtitle from md_refs r join md_views v on r.master_view_id=v.id where r.id=:id',  array('id'=>$ref) );
         $this->res->mview = $db->fetchSingle($qr);
         $qr = $db->query('select fk_field,pk_field from md_refs_fields where ref_id=:id',  array('id'=>$ref) );
         $this->res->keys = $qr->fetchAll(PDO::FETCH_OBJ);
         echo json_encode($this->res);
      }
      
      function ajxDisplayLinks()
      { $ids = post('ids',null);
        $wk = post('k_ref',null);
        $ks = (object)post('keys',null);
 
        if ($ids==null) return $this->error('Не переданы данные ссылок!',4055);
        if ($wk==null || $ks==null) return $this->error('Не переданы ключи!',4056);
        
        // Узнаем таблицу и список полей
        foreach($ids as $k=>$v) $ids[$k]=1*$v;
        $sql = 'select f.fname, v.tname from md_lookups l join md_fields f on l.display_field_id=f.id join md_views v on f.view_id=v.id  where l.field_id in ('.implode(',', $ids).')';
        $db = $this->cfg->db;
        $qr = $db->query($sql);
        $dnames = $qr->fetchAll(PDO::FETCH_OBJ);
        $fds = array();
        foreach($dnames as $k=>$r) $fds[]=$r->fname.' as c'.$k;
        $keys = array();
        foreach($ks as $r) $keys[ $r['fk_field'] ]=$r['pk_field'];
        $tname = $dnames[0]->tname;
        
        // Составим запрос для получения текстовых полей по ссылкам
        $wh = array();
        foreach($wk as $k=>$v) $wh[]=$keys[$k]."=:$k";
        $sql = 'select '.implode(',',$fds)." from $tname where ".implode(' and ',$wh);
        $qr = $db->query($sql, $wk);
        $this->res->row = $db->fetchSingle($qr);
        //$db = $this->res->sql = $sql;
        echo json_encode($this->res);
      }
      
      // Подготовка SQL
      function mkSQL($v, $metainf = false, $rowkeys='')
      { $search = post('search','');
        $get_total = post('get_total', false);
        $db = $this->cfg->db;
        $conf_id = $this->getConfId();
        $qr = $db->query('select id,name,vtitle,tname,edit_width from md_views where name=:name and conf_id=:id',
        array('name'=>$v, 'id'=>$conf_id) );
        $vr =  $db->fetchSingle($qr);
        
        if (empty($vr))  throw new Exception('Представление не найдено!',404);
        
        $qr->closeCursor();
        $qr = $db->query('select f.*, w.wname from md_fields f '
        .'left outer join md_widgets w on f.widget_id = w.id where view_id=:id order by ordr,id', array('id'=>$vr->id) );
        $h = $qr->fetchAll(PDO::FETCH_OBJ);
        
        $flist = array();
        $slist = array();
        $slinks = array(); // Исключения для where в ссылочных полях
        $rkeys = explode(':', $rowkeys);
        $npk = 0;

        $wh = ''; // добавим условия  поиска
        $wk = array();  // ключи поиска
        $awh = array(); 
        
        // Получим список внешних ключей для фильтра подчинённой таблицы
        $fkeys = post('fkeys',null);
        $childref = post('childref',null);

        if ($fkeys!=null && $childref!=null)
        {   $qr = $db->query('select fk_field from md_refs_fields where ref_id=:id order by id', array('id'=>$childref));
            $i=0;
            $fka = explode(':', $fkeys);
    
            while ($r=$qr->fetch(PDO::FETCH_NUM))
            {  $fk = $r[0];
               $wk[ $fk ] = $fka[$i];
               $awh[]="r.$fk=:$fk";
               $i++;
            }
        }
        
        $asort = post('sort', null);
        $ksort = [];
        $fsort = [];
        if ($asort!==null)
        foreach($asort as $k=>$v) $ksort[$v['id']]=$k;
        
        
        foreach($h as $i=>$r) 
        {  // Соберём массив для отбора строки по первичному ключу
          if ($rowkeys!='' &&  $r->pkey==1 && isset($rkeys[$npk]))
           {  $awh[] = "r.$r->fname=:$r->fname";
              $wk[$r->fname]=$rkeys[$npk];
              $npk++;
           }
           if ($r->widget_id==1) // Ссылочное поле
           {  $ql = $db->query('select f.fname from md_lookups l 
join md_fields f on l.display_field_id=f.id where l.field_id=:id',
              array('id'=>$r->id ) );
              $lf = 'r'.$r->ref_id.'.`'.$db->fetchSingleValue($ql).'`';
              $fn=$lf;
              $flist[] = $lf;
              $slinks[$r->fname] = $lf; // Добавим исключение для where
           } else
           if ($r->widget_id!=2 ) 
           {  $fn = 'r.`'.$r->fname.'`'; 
              $flist[] = $fn; // исключаем ссылочное поле и подчинённые таблицы
           }
           if (isset($ksort[$r->id]))
           {  $si =  $ksort[$r->id];
              $sort_f = $fn;
              if ($asort[$si]['order']==2) $sort_f.=' desc';
              $fsort[ $si ] = $sort_f;
           }
           if ($r->searchable==1) $slist[] = $r->fname;
        }
                
        $order = '';
        if (!empty($fsort)) $order = ' order by '.implode(',', $fsort).' ';

        if ($metainf) 
        {  $this->res->h = $h;
           $this->res->id = $vr->id;
           $this->res->title = $vr->vtitle; 
           $this->res->name = $vr->name;
           $this->res->edit_width = 1*$vr->edit_width; 
           $this->tname = $vr->tname; 
        }
                
        // make joins
        $qr =$db->query('select t.tname, r.id from md_refs r  join md_views t on t.id=r.master_view_id where r.view_id=:id',
        array('id'=>$vr->id) );
        $j = '';
        while ($r= $qr->fetch(PDO::FETCH_OBJ))
        { $ref = "r$r->id";
          $j.= " left outer join $r->tname $ref on ";
          $qf = $db->query('select fk_field,pk_field from md_refs_fields where ref_id=:id',
          array('id'=>$r->id));
          while ($k= $qf->fetch(PDO::FETCH_OBJ))
          { $j.=" r.$k->fk_field = $ref.$k->pk_field and";
          }
          $j = substr($j,0,-3);
        }
        
        $wh = implode(' and ', $awh); // соберём ключи для отбора записи
        
        $this->joins = $j;
        $this->total_joins = false;
        if ($search!='' &&  (count($slist)>0) ) 
        { $search="%$search%";
          foreach($slist as  $s)
          { if (isset($slinks[$s]))
            { $wh.=$slinks[$s]." like :$s or "; 
              // Если ссылочное поле есть в поиске, то включим джойны при 
              // вычислении количества
              $this->total_joins = true;
            } else
            $wh.="$s like :$s or ";
            $wk[$s] = $search;
          }
          $wh=substr($wh,0,-3);
        }
       
         
        $this->wk = $wk;
        if ($wh!='')  $wh = ' where '.$wh;
        
        //if (isset($_POST['search'])) $this->res->total = 
        if ($get_total) $this->res->total = $this->calcTotal($vr->tname, $wh, $wk);
        
        // $this->res->wk = $wk;
        // $this->res->wh = $wh; 
        
        return 'select '.implode(',',$flist).' from '.$vr->tname.' r '.$j.$wh.$order;
      }
      
      // Загрузим данные формы
      function ajxloadForm()
      {  if (!isset($this->seg[3])) return $this->error('Не передано название представления!',4002);
         $v = $this->seg[3];
         $keys = post('keys','');
         if ($keys=='')  return $this->error('Не переданы идентификаторы строки таблицы!',4003);
         $db = $this->cfg->db;
         $sql = $this->mkSQL($v, true, $keys).' limit '.$this->cfg->pg_rows;
         // $this->res->sql = $sql;
        
         $qr = $db->query($sql, $this->wk);
         $rows = $qr->fetchAll(PDO::FETCH_NUM);
         $this->res->row = $rows[0];
          echo json_encode($this->res);
      }
      
      function ajxloadPage()
      {   if (!isset($this->seg[3])) return $this->error('Не передано название представления!',4001);
      
          $n = 1*post('page',-1)-1;
          $pg_rows = 1*post('pg_rows', $this->cfg->pg_rows);
          
          if ($n>=0)
          { $db = $this->cfg->db;
            $sql = $this->mkSQL($this->seg[3]).' limit '.(1*$pg_rows*$n).','.(1*$pg_rows);          
            //$this->res->sql = $sql;      
            $qr = $db->query($sql, $this->wk );                            
            $this->res->rows = $qr->fetchAll(PDO::FETCH_NUM);
            $this->res->page = $n+1;
          }
          echo json_encode($this->res);
      }
  }
?>
