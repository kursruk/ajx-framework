
var view_upd = {}; // Изменения  полей представления

function confer(_id)
{ let conf_id = _id; 
  let fields, active_field=null, active_view=null,
      active_ref=null, active_ref_field=null;    // текущие поля представления
  let fld_upd = {};
  let selected_table = '';
  let last_view = null;
  
  
  function setFieldAttrs(f)
  { $('#fldattr #fname').val(fields[f].fname);
    $('#fldattr #ftitle').val(fields[f].ftitle);
    $('#fldattr #width').val(fields[f].width);
    $('#fldattr #default_value').val(fields[f].default_value);
    $('#fldattr #widget_id').val(fields[f].widget_id);
    
    var i, a = 'pkey,visable,ingrid,searchable,sortable,required'.split(','),
    frm=$('#fldattr');
    for (i in a)
    { var k = a[i];
      // console.log($('#'+a[i]) );      
      if (fields[f][k]==1) frm.find('#'+k)[0].checked=true;
      else frm.find('#'+k)[0].checked=false;
    } 
    // console.log(fld_upd);
  }
  
  
  function onFieldSelect(e)
  { $('#flist a').removeClass('active');
    let i, f = 1*$(e.target).addClass('active').attr('data-id');
    active_field = f;
    drawFieldsByRef();
    setFieldAttrs(f);
    $('#fldattr input[type=text]').unbind().keyup(updateField);
    $('#fldattr select').unbind().change(updateField);
    $('#fldattr input[type=checkbox]').unbind().click(updateField);
  }

  function drawFieldsByRef()
  {  ajx('/pages/confer/GetFieldsByRef', {id:active_ref}, function(d){
       // console.log(d);
       let s='';
       for (let i in d.fields)
       {  let a = '', r = d.fields[i];
          if (i==0) 
          { a=' active';
            active_ref_field = r.id;
          }
          s+='<a href="javascript:" class="list-group-item'+a+'">'+r.fname+' ('+r.title+')\
               <span class="pull-right">\
                  <span class="btn btn-xs btn-default b-add-ref-field" data-id="'
            +r.id+'">\
                     <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>\
                  </span>\
               </span>\
            </a>';
       }
       $('#refflds').html(s);       
       $('.b-add-ref-field').click(function(e){
           let b = $(e.target);
           if (b.attr('data-id')==undefined) b = b.parents('.btn:first');
           let id = b.attr('data-id');
           ajx('/pages/confer/AddFieldsByRef', {master_view_id:active_ref, f_id:id, view_id:active_view}, function(d){
               console.log(d);
               setOk(d.info);
               ajx('/pages/confer/LoadView', {id:active_view}, drawView);
           });
        });
     }); 
  }

  function onRefSelect(e)
  { $('#reflist a').removeClass('active');
    let i, f = 1*$(e.target).addClass('active').attr('data-id');
    active_ref = f;
    drawFieldsByRef();
  }
    
  function afterSave(d)
  { if (d.type=='fld') fld_upd={}; else
    if (d.type=='view') view_upd={};
    setOk(d.info);
  }
  
  function saveAll()
  { // Если есть что сохранять, то сохраним
    if (!$.isEmptyObject(fld_upd))  ajx('/pages/confer/SaveFields', fld_upd, afterSave); 
    if (!$.isEmptyObject(view_upd)) ajx('/pages/confer/SaveView/'+active_view, view_upd, afterSave);
  }
  
  function drawView(d)
  { let i, inp, s='';
    
    last_view = d;
    saveAll();
    
    // Обновим представление
    for (i in d.view)
    { inp = $('#editor #'+i);
      if (inp.length==1)
      { inp.val(d.view[i]);
      }
    }
    fields = d.fields;
    
    $('a.l-check').attr('href','/view/'+d.view.name);
    
    for (i in fields)
    {  let a = '', r = fields[i];
       if (i==0) a=' active';
       s+='<a href="javascript:" class="list-group-item'+a+'" data-id="'+i+'">'+r.fname+'</a>';
    }
    $('#flist').html(s);
    $('#flist a').click(onFieldSelect);
    $('#flist').sortable().bind('sortupdate', function(e, ui) {
        let list = $('#flist a');        
        let order = {};
        for (let i=0; i<list.length; i++)
        { let j = $(list[i]).attr('data-id');
          order[i] = fields[j].id;
        }
        ajx('/pages/confer/SaveFieldsOrder', {order:order}, function(d){
           console.log(d);
        });         
        // Save new order
    });
    
    
    s='';
    for (i in d.refs)
    {  let a = '', r = d.refs[i];
       if (i==0) 
       { a=' active';
         active_ref = r.id;
         drawFieldsByRef();
       }
       s+='<a href="javascript:" class="list-group-item'+a+'" data-id="'+r.id+'">'+r.name+'</a>';
    }
    $('#reflist').html(s);
    $('#reflist a').click(onRefSelect);
    
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
    { if (inp[0].checked)
      { 
        fld_upd[n][id]=1;
        fields[i][id]=1;
        console.log(fields[i]);
      } else 
      { 
         fld_upd[n][id]=0;      
         fields[i][id]=0;         
      }
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
  
  function getLastView(){ return last_view; }
  
  return {setConf:setConf, refresh:refresh, getLastView:getLastView};
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

function translatorForm(selector)
{  let views = new htviewCached();
   let v = null;
   
   function show(view)
   {  v = view;
      if (v==null) return;      
      ajx('/pages/confer/LoadViewTranslation', {view:v.view.id} , function(d){
         let s = '';
         let tx = d.data;
         let val = '';
          if (tx[v.view.name]!==undefined) val=tx[v.view.name];
         s+='<tr><td>'+v.view.name+'</td><td contenteditable="true">'+val+'</td></tr>';         
         for (let i in v.fields)
         {  let r = v.fields[i];
            val = '';
            if (tx[r.fname]!==undefined) val=tx[r.fname];
            s+='<tr><td>'+r.fname+'</td><td contenteditable="true">'+val+'</td></tr>';
         }
         $(selector+' .w-tr-text tbody').html(s);      
         $('#translate-form').modal();
      });
   }
   
   views.view('/pages/confer/translate', selector, function(){
      // Save
      
      $('.b-translation-save').click(function(){
         let rows = $(selector+' .w-tr-text tbody>tr');
         let t = {};         
         for (let i=0; i<rows.length; i++) 
         { let tds =  $(rows[i]).find('td');
           let txt = $(tds[1]).text().trim();
           if (txt!=='') t[ $(tds[0]).text() ] = txt;
         }
         ajx('/pages/confer/SaveViewTranslation', {view:v.view.id, data:t} , function(d){
            if (d.info!=undefined) setOk(d.info);          
         });
         console.log(t);
      });
      
   });  
   return {show:show};
}

$(function()
{  let conf = new confer(1);
   let views = new htviewCached();
   let addConfForm = null;
   
   let translator = translatorForm('#translate');
   
   $('a.l-translate').click(function(){
      translator.show( conf.getLastView() );
   });
      
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
