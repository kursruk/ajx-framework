<h1><?=T('DATABASE_SETUP')?></h1>
<?php

$no_connection = true;
   
function getRaw($cfg)
{  $dbconn = "$cfg->dbtype:host=$cfg->dbhost";       
   $db = new PDO($dbconn, $cfg->dbuser, $cfg->dbpass );
   $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   $db->exec("set names $cfg->dbcharset;");       
   return $db;
}

function alert($msg, $type='info')
{ echo "<div class=\"alert alert-$type\">$msg</div>";
}


function runSQL($db, $scfile)
{   $f = fopen($scfile,'r');
    $delim = ';'; 
    $sql = '';
    $line = 0;

    while ($s = fgets($f))
    {   $line++;
        if (trim($s)!='')
        {  // remove comments
           $uncom = preg_replace('/--(.)*/i', '', $s);
           
           if (trim($uncom!=''))
           { if (($p=stripos($uncom,'DELIMITER'))!==false)
             {  $arg = substr($uncom,$p+10);
                $d = trim($arg);
                $uncom='';
                $l = strlen($d);
                if ($l>0 && $l<3) $delim=$d;
             }
             
             if ( ($p=strpos($uncom, $delim)) !==false)                  
             {  $ds = strlen($delim);
                $sql.=substr($uncom, 0, $p);
                
                try 
                { $db->exec($sql);
                } catch (Exception $e)
                { alert($sql.'<br />'.$e->getMessage()."<br>Line: $line<br>File: $scfile", 'danger');
                  return false;
                }
               
                $sql = ''; //
                $uncom = substr($uncom, $p+$ds);                    
              }
           }
           $sql.=$uncom;               
        }
    }
    fclose($f);

    return true;
}
       

function InstallPages($db, $pstart, $admin=false)
{   // alert(T('PAGES_SETUP'), 'info');    
    $error = false;
    $pnum = 0;
    // Create pages scripts
    if ($handle = opendir($pstart)) 
    {
        $blacklist = array('.', '..');
        while (false !== ($file = readdir($handle))) 
        {   $dir = "$pstart/$file";
            if (!in_array($file, $blacklist) && is_dir($dir)) 
            {   $manifest = "$dir/manifest.js";
                if (file_exists($manifest)) 
                {   // echo $dir.'<br>';
                    $pm = json_decode(file_get_contents($manifest));
                    if (!property_exists($pm,'database'))
                    {   alert(T('WRONG_MANIFEST_FORMAT').': '.$manifest, 'danger');
                    } else
                    {   try
                        {   $st = $db->prepare('select update_no from mc_pages where name=:name');
                            $st->execute(array(':name'=>$file));
                            $row = $st->fetch(PDO::FETCH_OBJ);
                            $st->closeCursor();                            
                            $start=0;
                            if (empty($row))
                            {  if ($pm->database->install)
                               {   alert("New module: <b>$file</b>  $pm->author (c) $pm->created", 'warning');
                                   $install =  "$dir/install.sql";
                                   if ( runSQL($db, $install) )
                                   {   $st = $db->prepare('insert into mc_pages (name,update_no) values (:name,:no)');
                                       $st->execute(array(':name'=>$file,':no'=>$pm->database->update_no));
                                       $pnum++;
                                   }
                               }
                            } else $start = $row->update_no;                                            
                            // If updates exists then start them 
                            if ($admin)
                            {   $st=get($file,-1);
                                if ($st>=0)
                                {   alert("External update No = $st on $file");
                                    $start=$st;
                                }
                            }
                            if ($pm->database->update_no>0)
                            for ($i=$start; $i < $pm->database->update_no; $i++)
                            {   $n = $i+1;
                                $fn = "$dir/update.$n.sql";
                                if (!file_exists($fn))
                                {  alert(T('FILE_NOT_FOUND').' '.$fn);
                                } else if (runSQL($db, $fn))
                                {  alert("Update successful: <b>$fn</b>");
                                   $st = $db->prepare('update mc_pages set update_no=:no where name=:name');
                                   $st->execute(array(':name'=>$file,':no'=>$n));
                                }
                            }
                            
                        } catch (Exception $e)
                        {  alert(T('CAN_NOT_CREATE').' '.$e->getMessage()." $file database", 'danger');
                           $error = true;
                        }
                        //print_r($pm);
                        
                    }
                }
                
            }
        }
        closedir($handle);
    }
    if (!$error && $pnum>0) alert(T('ALL_PAGES_CREATED'), 'success');
}


  
 

function installSystem($db, $cfg, $create_db = true) 
{   alert(T('INSTALL_SYSTEM'));
    try
    {  try
        {
            if ($create_db) $db->query("create database $cfg->dbname;");
            $db->query("use $cfg->dbname;");
            
            if (!runSQL($db,__DIR__.'/install.sql')) 
            {  $db->query("drop database $cfg->dbname;");
               alert(T('ERR_IN_SCRIPT_DB_DROPPED'), 'danger');
            } else 
            {   installPages($db,SYS_PATH.'psys');
                installPages($db,SYS_PATH.'pages');
                alert(T('DATABASE').' <b>'.$cfg->dbname.'</b> '.T('CREATED'), 'success');
            }
        } catch (Exception $e)
        { alert(T('CANT_CREATE_DB')." $cfg->dbname<br>".$e->getMessage(), 'danger');
        }                                      
    } catch (Exception $e)
    {   alert("Error: ".$e->getMessage(), 'danger');
    }
    
}
   $cfg = $this->cfg; 
 
   if ($cfg->db==null)
   {    try
        {
            $db = getRaw($this->cfg);
            if ($db!=null)
            {
                alert( T('DB_CONNECTION_ESTABLISHED') );
                installSystem($db, $cfg);
                installPages($db,SYS_PATH.'psys');
                installPages($db,SYS_PATH.'pages');                
            }
        } catch (Exception $e)
        {  alert(T('CHECK_CONFIG_USER_SETTINGS'), 'danger');
        }
   } else 
   {  alert( T('DATABASE_EXISTS') , 'warning');  
      $sql = "select count(*) as tables from information_schema.tables WHERE TABLE_SCHEMA='$cfg->dbname' and TABLE_NAME in ('mc_users','mc_sessions','mc_usergroups','mc_groups')";
      $qr = $cfg->db->query($sql);
      $row = $qr->fetch(PDO::FETCH_OBJ);
      if (!empty($row) && $row->tables==0)
      {  installSystem($cfg->db->db, $cfg, false);
      }
      $is_admin = $cfg->inGroup('admin');
      installPages($cfg->db->db, SYS_PATH.'psys', $is_admin);
      installPages($cfg->db->db, SYS_PATH.'pages', $is_admin);
      $no_connection = false;
   }
   
   
   if ($no_connection) die();
   // global $_TRANSLATIONS;
   // print_r($_TRANSLATIONS);   
?>
