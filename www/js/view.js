function wModal(id, title, panel, classN)
{  let div = null;
   let T = function(v){ return v; } // Default translation function
   let self = this;
   
   
   this.draw = function(body, init)
   { gl_Locales.translate('pages/view', function(fu) {
     
        T=fu;
        
         div=$('#linked-modals #'+id);
         if (div.length>0)
         {  div.find('.modal-body').html(body);
            if (init!=undefined) init(div);
            self.show();
         } else
         {  let s='';
            if (classN==undefined) classN='';
        s+='<div class="modal fade w-form '+classN+'" id="'+id+'" tabindex="-1" role="dialog">\
         <div class="modal-dialog">\
           <div class="modal-content">\
             <div class="modal-header">\
               <button type="button" class="close" data-dismiss="modal" aria-label="'+T('Close')+'"><span aria-hidden="true">&times;</span></button>\
               <h4 class="modal-title">'+title+'</h4>\
             </div>\
             <div class="modal-body">';
             s+= body;
             s+='  </div>\
             <div class="modal-footer">';
             s+=panel;
             s+='</div>\
           </div>\
         </div>\
       </div>';
           $('#linked-modals').append(s);
           div=$('#linked-modals #'+id);
           if (init!=undefined) init(div);
           div.modal();
         }
         self.dv = div;
      });
   }
   
   this.show = function()
   {  if (div!=null) div.modal('show');      
   }
   
   this.hide = function()
   { if (div!=null) div.modal('hide');   
   }
   
   this.resetForm = function()
   { div.find('form')[0].reset();
   }

   return this;
}


gl_views = 0;
let gl_frmConfirm = null;

function view(_div, _onSelectRow)
{ 
  let gl_T = function(v){ return v; } // Global translation function
  let div = _div, title='';
  let v, pkeys = [], pcols = [], pg_rows, edit_width=1,
  n_pages, self = this, fds, gsearch='', formkeys={}, refs={}, get_total = true,
  onSelectRow = _onSelectRow;
  let fkeys = '', childref=null; // Внешние ключи подчинённой таблицы
  let frmEdit = null;
  let frmNew = null;
  let wcl=['','middle','large']; // классы размеров форм
  let lc = {};  // Language translatoin text
  
  gl_views++;
  $(div).attr('id', 'view_'+gl_views);
  let last_sort = null;
  let pager = new modelPagination('#view_'+gl_views + ' .w-pager');
       
  function T(txt)
  { if (lc[txt]!==undefined) return lc[txt]; // If text is in local  translation
    return gl_T(txt); // Else global translation 
  }

  function updateDisplayLinks(ref, eForm)
  {  let p=[], i, ar = eForm.dv.find('div.w-ref[data-ref='+ref.ref+']');
     // Расставим подписи ссылок по местам    ;
     function drawLinks(d)
     {  for (let i in p)
        { let id = p[i]; 
          let inp =  eForm.dv.find('div.w-ref[data-fid='+id+']').find('input');        
          inp.val(d.row['c'+i])
        }       
     }
     
     for (let i=0; i<ar.length; i++)
     {  p.push( $(ar[i]).attr("data-fid") );
       // console.log(p);
     }
     ajx('/pages/view/DisplayLinks',{ids:p, k_ref:ref.value, keys:ref.keys}, drawLinks);
  }

  // Функция устанавливает внешние ключи из модального окна таблицы
  // ключи сохраняются в массиве refs, свойстве value
  // для обновления подписей ссылочных полей вызываем updateDisplayLinks
  function setLinkKeys(frm, ref, e)
  {  eForm = $('#'+ref.mview.name).prop('returnForm');     
     $(frm).modal('hide');
     var fkeys = $(e.target.parentNode).attr('data-key');
     var val={}, i, a = fkeys.split(':'), id=ref.ref; // данные ключей
     for (i in a)
     { var fk = ref.keys[i].fk_field;
       val[fk] = a[i];
     }
     refs[id].value = val;
     updateDisplayLinks(refs[id], eForm);
  }
  

  // Установка значения справочного поля из справочника
  function setLink(e, eForm)
  { let ref_id = $(e.target.parentNode).attr('data-ref'); 
    // console.log('setLink', eForm.dv.selector);
    
    function drawModal(ref, eForm)
    {  //  console.log('drawForm', eForm.dv.selector);
        var s='', mf, mview = ref.mview.name;
        // поищем мастер форму ввода данных в ссылочное поле
        mf = $('#linked-modals #'+mview);
        // Создадим новую
        if (mf.length==0)
        { s+='<div class="modal fade large" id="'+mview+'" tabindex="-1" role="dialog">\
  <div class="modal-dialog">\
    <div class="modal-content">\
      <div class="modal-header">\
        <button type="button" class="close" data-dismiss="modal" aria-label="'+T('Close')+'"><span aria-hidden="true">&times;</span></button>\
        <h4 class="modal-title"></h4><div class="w-view" id="tgt_'+mview+'" data-view="'+mview+'"></div>\
      </div>\
      <div class="modal-body">';
      
        s+='  </div>\
      <div class="modal-footer">\
        <button type="button" class="btn btn-default" data-dismiss="modal">'+T('Close')+'</button>\
        <button type="button" class="btn btn-primary w-btnsave">'+T('Save')+'</button>\
      </div>\
    </div>\
  </div>\
</div>';
         $('#linked-modals').append(s);
         $('#linked-modals #'+mview)
            .prop('returnForm', eForm)
            .modal({width: '80%'}); 
                 

         new view( $('#linked-modals #tgt_'+mview)[0], function(e){
            // on select target row               
            if (e.offsetX>30) setLinkKeys('#linked-modals #'+mview, ref, e); 
         });
         
        } else 
        {  $( mf[0] ).prop('returnForm', eForm).modal('show') ;
        }
        //console.log('ref: ', mview, mf.length);
    }
      
    // закешируем данные ссылок
    if (refs[ref_id]==undefined)
    {  ajx('/pages/view/Ref', {ref:ref_id} , function(d) { 
          if (!d.error) 
          { delete d.error;
            d.dview = v;
            d.ref = ref_id;
            refs[ref_id] = d;            
            drawModal(d, eForm);
          }
        });
    } else drawModal(refs[ref_id], eForm);
  }


  function afterSave()
  {  setOk(title+': '+T('SAVING_COMPLETE'));
     refs={}; // очистим данные ссылок
  }

  function refreshTable(get_total)
  { let p = {page:pager.currentPage(), pg_rows:pg_rows}
    if (get_total==undefined) get_total=false;
    if (gsearch!='') p.search=gsearch;
    if (last_sort!=null) p.sort = last_sort;
    if (childref!=null) { p.childref = childref; p.fkeys = fkeys; }
    ajx('/pages/view/loadPage/'+v, p ,drawData);
  }

  this.refreshTable = refreshTable;
  
  // Сохранение формы представления
  function formSave(form, isInsert, close)
  { if (close==undefined) close = true;
    let i,j, inputs = form.dv.find('.w-data'), data={keys:formkeys, row:{}};
    for (i=0; i<inputs.length; i++)
    {   let inp = $(inputs[i]), fname;
        fname=inp.attr('id');
        data.row[fname] = inp.val();
    }
    // Добавим выбранные значения ссылок
    for (i in refs) 
    { var r = refs[i];
      if (r.value!=undefined) for (j in r.value) data.row[j] = r.value[j];
    }
   
    let get_total = false;
    
    if (isInsert!=undefined && isInsert) 
    { data.insert = true;
      get_total = true;
    }    
    
    ajx('/pages/view/SaveView/'+v, data , function(){ 
         afterSave();
         if (close) form.hide(); 
         else form.resetForm();
         // reload data
         refreshTable(get_total);
    });    
  }
  
  this.confirm = function(text, _onConfirm)
  {  if (gl_frmConfirm==null) 
     { gl_frmConfirm = new wModal('frm_confirm',  T('Confirm'),
        '<button type="button" class="btn btn-success b-Yes">'+T('B_YES')+'</button>'
       +'<button type="button" class="btn btn-primary b-No">'+T('B_NO')+'</button>',
       wcl[0]);
       gl_frmConfirm.draw('<p class="confirm-text"></p>');       
       gl_frmConfirm.dv.find('.b-Yes').click( function(){ 
            if (gl_frmConfirm.onConfirm!==undefined) gl_frmConfirm.onConfirm();
            gl_frmConfirm.hide(); 
       }); 
       gl_frmConfirm.dv.find('.b-No').click( function(){ 
            gl_frmConfirm.hide(); 
       }); 
     }     
     gl_frmConfirm.dv.find('.confirm-text').html(text);
     gl_frmConfirm.onConfirm = _onConfirm; 
     gl_frmConfirm.show();
  }
  
  this.deleteRows = function()
  { let rows = $(div).find('table tbody tr');
    let drows = [];
    let rows_names = '';
    for (let i=0; i<rows.length; i++)
    {  let cb = $(rows[i]).find('td.w-chb > input')[0];
       if (cb.checked) 
       { drows.push( $(rows[i]).attr('data-key') );
         rows_names+=$(rows[i]).find('td:eq(1)').html()+'<br>';
       }
       
    }
    if (drows.length>0)
    this.confirm(T('DELETE_ROWS')+"<br><blockquote>"+rows_names+'</blockquote>', function(){ 
        ajx('/pages/view/Delete/'+v, {rows:drows}, function(d){
           setOk(d.info);
           refreshTable(true);
        });        
    });
  }

  this.addNew = function()
  {  // console.log('addNew: '+v);     
     // Удалим прежние выбранные значения ссылочных полей
     for (let i in refs) delete refs[i].value;
      
     if (frmNew==null) 
     { frmNew = new wModal('frmNew'+v, T('Add')+': '+title,
       '<button type="button" class="btn btn-default w-close">'+T('Close')+'</button>'
      +'<button type="button" class="btn btn-success w-btnsave-close">'+T('ADD_AND_CLOSE')+'</button>'
      +'<button type="button" class="btn btn-primary w-btnsave">'+T('Add')+'</button>',
       wcl[edit_width-1]);
       
       frmNew.draw(drawFormInputs({},'', true));
       frmNew.dv.find('.w-close').click( function(){ frmNew.hide(); });
       frmNew.dv.find('.w-setlink').unbind().click(function(e){ 
            setLink(e, frmNew); 
       });
       frmNew.dv.find('.w-btnsave-close').unbind().click(function(){ formSave(frmNew, true); });
       frmNew.dv.find('.w-btnsave').unbind().click(function(){ formSave(frmNew, true, false); });
    
       /*
       frmEdit.draw(s, function(div)
       { 
          div.find('.w-btnsave').unbind().click(formSave);         
          div.find('.w-view').each(function(i,div){ new view(div); });
          div.find('.w-setlink').unbind().click(setLink);
       });
       */
      
      
     } else 
     {  frmNew.resetForm();
        frmNew.show();
     }
  }
  
  function drawFormInputs(d, keys, isAdd)
  {  if (isAdd ==undefined) isAdd = false;
      
     function addRow(label, input)
      { return '<div class="form-group">'+label+'<div class="col-sm-8">'+input+'</div></div>';
      }
    
      let i, s = '<form class="form-horizontal">';
      let l_class = ' class="col-sm-4   control-label"';
      for (i in fds)
      {  let r = fds[i];
         let val = '';
         if (d.row!=undefined && d.row[i]!=undefined) val = d.row[i];
         if (isAdd) 
         { val = '';
           if (r.default_value!=null) val = r.default_value;
         }
         if (r.pkey==1) formkeys[r.fname] = val;
         if (r.visable==1) 
         {   let t = "text";
             let label = T(r.fname);
             if (r.widget_id>4)
             {   if (r.widget_id==5) t="date"; else
                 if (r.widget_id==6) t="datetime-local";  else
                 if (r.widget_id==7) t="time"; 
             } 
             if (r.widget_id==1) s+=addRow('<label'+l_class+'>'+label+'</label>',
             '<div class="input-group w-ref" data-ref="'+r.ref_id+'" data-fid="'+r.id+'"><input type="text" class="form-control w-link" placeholder="'+label+'"  value="'+val+'">\
  <span class="input-group-addon btn btn-default w-setlink">...</span></div>');
             else if (r.widget_id==2) s+='<div class="w-view" data-childref="'+r.ref_id+'" data-keys="'+keys+'"></div>';
             else    
             s+=addRow('<label'+l_class+'>'+label+'</label>',
             '<input type="'+t+'" class="form-control w-data" autocomplete="none" id="'+r.fname+'" placeholder="'+label+'" value="'+val+'">');

             // ссылка
             //if (r.widget_id==1)
         }
      }
      return s+'</form>';
  }
  
  function drawForm(d, keys)
  {   var i,s = '';
  
      // Удалим прежние выбранные значения ссылочных полей
      for (let i in refs) delete refs[i].value;
    
      s = drawFormInputs(d, keys);

      if (frmEdit==null) 
      { frmEdit = new wModal('frm'+v, T('Edit')+': '+T(d.name),
       '<button type="button" class="btn btn-default w-close">'+T('Close')+'</button>\
<button type="button" class="btn btn-primary w-btnsave">'+T('Save')+'</button>', wcl[edit_width-1]);
        first = true;
      }
      
      frmEdit.draw(s, function(div)
      {  div.find('.w-close').unbind().click(function()
         {  frmEdit.hide();
         });             
         div.find('.w-btnsave').unbind().click(function(){ formSave(frmEdit); });
         div.find('.w-view').each(function(i,div){ new view(div); });
         div.find('.w-setlink').unbind().click(function(e){ setLink(e, frmEdit); });
      });      
      // console.log('drawForm '+v);
  }

  function editForm(r)
  {   var keys = $(r.target.parentNode).attr('data-key');  // Первичные ключи
      if (!$(r.target).hasClass('w-chb') &&  keys!=undefined) ajx('/pages/view/loadForm/'+v, {keys:keys} , function(d){ drawForm(d,keys); });
  }

  function drawTableRows(d)
  { var i,j, s='';
    for (i in d.rows)
    {  var ks = [];
       for (j in  pkeys) ks.push(d.rows[i][  pkeys[j] ]);
        s+='<tr data-key="'+ks.join(':')+'"><td class="w-chb"><input type="checkbox" /></td>';
       for (j in pcols)
       { var id = pcols[j], r = fds[id];
         if (r.widget_id==1) s+='<td class="w-link">'+d.rows[i][id]+'</td>'; else
         if (d.rows[i][id]!=undefined) s+='<td>'+d.rows[i][id]+'</td>';
         else s+='<td>-</td>';
       }
       s+='</tr>';
    }
    return s;
  }

  function draw(d)
  {  if (d.error!=undefined && !d.error)
     {   // Append translations
         if (d.locale!==undefined)  lc = $.extend(lc, d.locale);
        
         var i, j, pag='', s ='', sfld=[], hdr='';
         fds = d.h;
         title = T(d.name);
         if (d.view!=undefined) v=d.view; // Если загрузились по коду
         if (d.edit_width!=undefined) edit_width = d.edit_width;
                           
        // заголовок таблицы
        hdr+='<table class="table table-striped"><thead>';
        hdr+='<tr><th style="width:40pt"><input class="w-chb-all" title="'+T('SELECT_ALL')+'" type="checkbox" /></th>';
        for (i in d.h) 
        { let r = d.h[i];
          let w = '';
          let sort='';
          if (r.sortable==1) sort=' class="c-sort"';
          if (r.width!='' && r.width!=null) w=' style="width:'+r.width+'pt"';
          if (r.visable==1 && r.ingrid==1 && r.widget_id!=2) 
          { hdr+='<th'+w+sort+'>'+T(r.fname)+'</th>';
            pcols.push(i);
          }
          if (r.pkey==1) pkeys.push(i);
          if (r.searchable==1) sfld.push(T(r.fname));
        }
        hdr+='</tr></thead><tbody>';

// панель
s+='<div class="w-panel">';
s+='<div class="row"><div class="col-lg-4 col-md-4"> <span class="w-label">'+T(d.name)+'</span></div>';


s+='<div class="col-lg-8 col-md-8">';

s+='<div class="input-group">';
s+=' </div>';

// Кнопки
// s+='<button class="btn btn-default w-btn-sort" data-toggle="tooltip" data-placement="top" title="'+T('Sort')+'" aria-hidden="true"><span class="glyphicon glyphicon glyphicon-sort-by-attributes"></span></button>';
// s+='<button class="btn btn-default w-btn-find" data-toggle="tooltip" data-placement="top" title="'+T('Search')+'" aria-hidden="true"><span class="glyphicon glyphicon-search"></span></button>';

s+='<div class="input-group"> \
<span class="input-group-btn"><button class="btn btn-default w-btn-new" data-toggle="tooltip" data-placement="top" title="'+T('Add')+'" aria-hidden="true"><span class="glyphicon glyphicon-plus"></span></button></span>\
<span class="input-group-btn"><button style="margin-right:5pt" class="btn btn-default w-btn-del" data-toggle="tooltip" data-placement="top" title="'+T('Delete')+'" aria-hidden="true"><span class="glyphicon glyphicon-minus"></span></button></span>';
 // поиск
 if (sfld.length>0) s+= '<input type="text" class="form-control w-stext" data-toggle="tooltip" data-placement="top" title="'+sfld.join('; ')+'" placeholder="'+T('Search')+'"> \
 <span class="input-group-btn"> <button class="btn btn-default w-search" type="button">'+T('Search')+'</button> </span>';
s+='</div>';
 
s+='</div>\
</div>\
</div>';

        s+=hdr;
        
        // тело таблицы
        s+=drawTableRows(d);
        s+='</tbody></table>';
        s+='<div class="w-pager"></div>';        
        
        div.innerHTML=s;
        
        // console.log('draw', d);        
        if (d.pg_rows!==undefined) pg_rows = d.pg_rows;
        if (d.total!==undefined) 
        {  pager.setTotal(d.total, pg_rows);
        }  
      
       // console.log(d, pkeys, pcols);
       //  $(div).find('nav.w-pager li').click(function(li){ self.pgClick(li); });
        $(div).find('button.w-search').click(function(){ self.search(); });
        $(div).find('button.w-btn-new').click(function(){ self.addNew(); });
        $(div).find('button.w-btn-del').click(function(){ self.deleteRows(); });        
        $(div).find('input.w-stext').keypress(function(e){ if (e.charCode==13) self.search(); }).tooltip();
        $(div).find('.w-panel button').tooltip();        
        $(div).find('.w-chb-all').click( function(cb)
        {  var chk =  cb.target.checked;
           $(div).find('.w-chb').each(function(i,e){ $(e).find('input')[0].checked = chk; })  
        });
        if (onSelectRow!=undefined)  $(div).find('table>tbody>tr').click(onSelectRow);
        else $(div).find('table>tbody>tr').click(editForm);
        $(div).find('.c-sort').click( self.sort );
        
     }  
  }
  
  function drawData(d)
  {   var s = drawTableRows(d);
      $(div).find('tbody').html(s);  
      // console.log('DrawData', d);
      if (d.pg_rows!==undefined) pg_rows = d.pg_rows;
      if (d.total!==undefined) 
      {  pager.setTotal(d.total, pg_rows);
      }      
      if (onSelectRow!=undefined)  $(div).find('table>tbody>tr').click(onSelectRow);
      else $(div).find('table>tbody>tr').click(editForm);     
  }
  
  this.sort = function(e)
  {  let f = fds[  pcols[ e.currentTarget.cellIndex-1] ];
     let sort_type = 0;
     if ($(e.target).prop('srt')!=undefined) sort_type = $(e.target).prop('srt');
     sort_type = (sort_type+1) % 3;     
     
     let td = $(e.target);
     td.parent().find('.c-sort').prop('srt', 0)
      .removeClass('sort-desc')
      .removeClass('sort-asc');
     
     $(e.target).prop('srt', sort_type);
     
     switch (sort_type)
     {  case 1: td.addClass('sort-asc');  break;
        case 2: td.addClass('sort-desc');  break;        
     }
         
     let p = {page:1, pg_rows:pg_rows}
     let s = $(div).find('input.w-stext').val();
     if (s!='') p.search = s;     
     if (sort_type>0) 
     {  let sort = {};
        sort.id = f.id;
        sort.order = sort_type;
        p.sort = [sort];
     }
     
     if (p.sort!=undefined) last_sort = p.sort;
     else last_sort = null;
     
     ajx('/pages/view/loadPage/'+v, p ,drawData);     
  }
  
  this.search = function()
  { var s = $(div).find('input.w-stext').val();
    gsearch = s;
    var p = {page:1, pg_rows:pg_rows, search:s, get_total:true}
    if (last_sort!=null) p.sort=last_sort;
    ajx('/pages/view/loadPage/'+v, p ,drawData);
  }
  
  pager.change(function(n){
     var p = {page:n, pg_rows:pg_rows};
     if (gsearch!='') p.search=gsearch;
     if (childref!=null) { p.childref = childref; p.fkeys = fkeys; }
     if (last_sort!=null) p.sort=last_sort;
     ajx('/pages/view/loadPage/'+v, p ,drawData); 
  });
  
  function load() 
  {  v = $(div).attr('data-view');
     if (v!=undefined) ajx('/pages/view/load/'+v, draw); else
     { fkeys = $(div).attr('data-keys'), childref = $(div).attr('data-childref'); // lfyys
       if (childref==undefined) childref=null;
       if (childref!=null) ajx('/pages/view/loadChild',{childref:childref, fkeys:fkeys, get_total:true}, draw);
     }
  }
  
  gl_Locales.translate('pages/view', function(fu) {
     gl_T=fu; // set global translation function
     load();     
  });
  
}
   
$(function()
{  jQuery('.w-view').each(function(i,div){// console.log(div); 
   new view(div); });
}
);
