
var view_upd = {}; // Изменения  полей представления

function confer(_id)
{ let conf_id = _id; 
  let fields, active_field=null, active_view=null; // текущие поля представления
  let fld_upd = {};
  let selected_table = '';
  
  
  function setFieldAttrs(f)
  { $('#fldattr #fname').val(fields[f].fname);
    $('#fldattr #ftitle').val(fields[f].ftitle);
    $('#fldattr #width').val(fields[f].width);
    $('#fldattr #default_value').val(fields[f].default_value);
    $('#fldattr #widget_id').val(fields[f].widget_id);
    
    var i, a = 'pkey,visable,ingrid,searchable,required'.split(','),
    frm=$('#fldattr');
    for (i in a)
    { var k = a[i];
      // console.log($('#'+a[i]) );
      if (fields[f][k]==1) frm.find('#'+k)[0].checked=true;
      else frm.find('#'+k)[0].checked=false;
    }    
  }
  
  function onFieldSelect(e)
  { $('#flist a').removeClass('active');
    var i, f = 1*$(e.target).addClass('active').attr('data-id');
    active_field = f;
    setFieldAttrs(f);
    $('#fldattr input[type=text]').unbind().keyup(updateField);
    $('#fldattr select').unbind().change(updateField);
    $('#fldattr input[type=checkbox]').unbind().click(updateField);
  }
  
  function afterSave(d)
  { if (d.type=='fld') fld_upd={}; else
    if (d.type=='view') view_upd={};
    setOk('Изменения сохранены');
  }
  
  function saveAll()
  { // Если есть что сохранять, то сохраним
    if (!$.isEmptyObject(fld_upd))  ajx('/pages/confer/SaveFields', fld_upd, afterSave); 
    if (!$.isEmptyObject(view_upd)) ajx('/pages/confer/SaveView/'+active_view, view_upd, afterSave);
  }
  
  function drawView(d)
  { var i, inp, s='';
    
    saveAll();
    
    // Обновим представление
    for (i in d.view)
    { inp = $('#editor #'+i);
      if (inp.length==1)
      { inp.val(d.view[i]);
      }
    }
    fields = d.fields;
    
    for (i in fields)
    {  var a = '', r = fields[i];
       if (i==0) a=' active';
       s+='<a href="javascript:" class="list-group-item'+a+'" data-id="'+i+'">'+r.fname+'</a>';
    }
    $('#flist').html(s);
    $('#flist a').click(onFieldSelect);
    if (fields.length>0) setFieldAttrs(0); 
    active_view = d.view.id;
  }

  function onTreeSelect(e,d)
  {  console.log(d);
     // If tables selected
     if (d.nodeId==0 || d.parentId==0)
     { $('.view-editor').hide();
       // Table selected
       if (d.parentId!==undefined)
       {  $('.add-view').show();
          selected_table = d.text;
       } else $('.add-view').hide();
     } else 
     {   $('.add-view').hide();
         $('.view-editor').show();
     }
     if (d.id!=undefined) ajx('/pages/confer/LoadView', {id:d.id}, drawView);
  }
  
  function drawData(d)
  { $('#tree').treeview({data: d.tree}).on('nodeSelected', onTreeSelect);
  }
  
  function updateField(e)
  { var inp = $(e.target), i, n, id;
    i = active_field;
    n =  fields[i].id;
    if (fld_upd[n]==undefined) fld_upd[n]={};
    id = inp.attr('id');
    if (inp.attr('type')=='text') fld_upd[n][id] = inp.val();
    if (inp.attr('type')=='checkbox') 
    { if (inp[0].checked) fld_upd[n][id]=1; else fld_upd[n][id]=0;      
    }
    if (inp.prop('tagName').toLowerCase()=='select') fld_upd[n][id] = inp.find('option:selected').val();
  }
  
  function updateView(e)
  { var inp = $(e.target);
    id = inp.attr('id');
    if (inp.attr('type')=='text') view_upd[id] = inp.val(); else
    if (inp.prop('tagName').toLowerCase()=='select') view_upd[id] = inp.find('option:selected').val();
  }

  function refresh()
  {
     ajx('/pages/confer/Load', {conf:conf_id} ,drawData);
  }
  
  function setConf(id)
  {  conf_id = id;
     refresh();
  }
  
  refresh();
  
  $('#editor input[type=text]').keyup(updateView);
  $('#editor select').change(updateView);
  $('#btnSave').click(saveAll);
  
  // Create new view
  $('.b-create-view').click(function(){
       ajx('/pages/confer/CreateView', {table:selected_table} , function(d){
          refresh();
          // location.reload();
       });
  });
  
  return {setConf:setConf, refresh:refresh};
}

function modelSelector(selector, prm) {
   let sel = $(selector);
   let model = sel.attr('data-model');
   let onClick = null;
   let onRefresh = null; 
   let onDraw = null;   
   let onGetName = null;
   let onGetId = null;
   
   function setData(d) {
      if (d.rows!==undefined) 
      { let s='';
        for (let i in d.rows)
        { let name = '';
          let id = '';
          let r = d.rows[i];
          
          if (onGetName!==null) name=onGetName(r);
          else if (r.name!==undefined) name=r.name;
          
          if (onGetId!==null) id=onGetId(r);          
          else if (r.id!==undefined) id=r.id;
          
          s+='<option value="'+id+'">'+name+'</option>';
        }
        sel.html(s);
        if (onRefresh!==null) onRefresh(sel);
        if (onDraw!==null) onDraw(sel);
      }
   }
   
   function click(fu){ onClick=fu; }
   
   function refresh(fu)
   {  if (fu!==undefined) onRefresh = fu;
      ajx(model+'/load', {}, setData);
   }
   
   function draw(fu){ onDraw = fu; }
   
   sel.click(function(e){
      if (onClick!==null) onClick(sel, e);
   });
   
   // Parameteres
   if (prm!==undefined) {
      if (prm.getName!==undefined) onGetName = prm.getName;
      if (prm.getId!==undefined) onGetId = prm.getId;
      if (prm.afterDraw!==undefined) onDraw = prm.afterDraw;
   }
   
   refresh();
   
   return {click:click, refresh:refresh };
}

$(function()
{  let conf = new confer(1);
   let views = new htviewCached();
   let addConfForm = null;
   let confSelect = new modelSelector('.s-conf-selector', {
      getName: function(r){
         return r.conf+' '+r.version+'.'+r.minor_version;
      },
      afterDraw: function(sel) {
          ajx('/pages/confer/GetConfigId',{}, function(d){
            if (d.id>0) { 
               sel.val(d.id);
               conf.setConf( sel.val() );       
            }
          });
      }
   });
   
   confSelect.click(function(sel){
      ajx('/pages/confer/SetConfigId',{id:sel.val()}, function(d){

      });  
      conf.setConf( sel.val() );
   });
   
   views.view('/pages/confer/add-config','#view-add-config', function(){
      
          // Add config form
          addConfForm = new modelFormController('#add-config-form');
                   
          addConfForm.loaded(function(d){
            
            //wildList.load({master:d.id_photo});
            //refList.load({master:d.id_photo});
            //console.log(d);
          });
         
          addConfForm.updated(function(d){
                   if (!d.error) 
                   {                      
                   }
          });
          
         $('.bt-add-config').click(function(){
             $('#add-config-form').modal();
             
         });
         
         $('.b-config-save').click(function(){
            addConfForm.insert(function(d){               
               confSelect.refresh(function(sel){
                  sel.val(d.id);
               });               
               $('#add-config-form').modal('hide');
            });
         });
   });
   
   
});
