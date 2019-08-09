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

   return this;
}


gl_views = 0;

function view(_div, _onSelectRow)
{ 
  let gl_T = function(v){ return v; } // Global translation function
  let div = _div, title='';
  let v, pkeys = [], pcols = [], pg_rows, edit_width=1,
  n_pages, self = this, fds, gsearch='', formkeys={}, refs={}, get_total = true,
  onSelectRow = _onSelectRow;
  var fkeys = '', childref=null; // Внешние ключи подчинённой таблицы
  var frmEdit = null;
  var frmNew = null;
  var wcl=['','middle','large']; // классы размеров форм
  let lc = {};  // Language translatoin text
  
  gl_views++;
  $(div).attr('id', 'view_'+gl_views);
  let pager = new modelPagination('#view_'+gl_views + ' .w-pager');
       
  function T(txt)
  { if (lc[txt]!==undefined) return lc[txt]; // If text is in local  translation
    return gl_T(txt); // Else global translation 
  }

  function updateDisplayLinks(ref, eForm)
  {  var p=[], i, ar = eForm.dv.find('div.w-ref[data-ref='+ref.ref+']');
     // Расставим подписи ссылок по местам
     function drawLinks(d)
     {  for (i in p)
        { var id = p[i]; 
          var inp =  eForm.dv.find('div.w-ref[data-fid='+id+']').find('input');
          inp.val(d.row['c'+i])
        }       
     }
     
     for (i=0; i<ar.length; i++)
     {  p.push( $(ar[i]).attr("data-fid") );
       // console.log(p);
     }
     ajx('/pages/view/DisplayLinks',{ids:p, k_ref:ref.value, keys:ref.keys}, drawLinks);
  }

  // Функция устанавливает внешние ключи из модального окна таблицы
  // ключи сохраняются в массиве refs, свойстве value
  // для обновления подписей ссылочных полей вызываем updateDisplayLinks
  function setLinkKeys(frm, ref, e, eForm)
  {  $(frm).modal('hide');
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
  { var ref = $(e.target.parentNode).attr('data-ref');    
       
    function drawModal(ref)
    {   var s='', mf, mview = ref.mview.name;
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
         $('#linked-modals #'+mview).modal({width: '80%'});         

         new view( $('#linked-modals #tgt_'+mview)[0], function(e){ setLinkKeys('#linked-modals #'+mview, ref, e, eForm); } );
        } else $( mf[0] ).modal('show');
        //console.log('ref: ', mview, mf.length);
    }
  
    // закешируем данные ссылок
    if (refs[ref]==undefined)
    {  ajx('/pages/view/Ref', {ref:ref} ,
      function(d){ 
          if (!d.error) 
          { delete d.error;
            d.dview = v;
            d.ref = ref;
            refs[ref] = d;            
            drawModal(d);
          }
        });
    } else drawModal(refs[ref]);
  }


  function afterSave()
  {  setOk(title+': '+T('SAVING_COMPLETE'));
     refs={}; // очистим данные ссылок
  }

 // Сохранение формы представления
  function formSave(form, isInsert)
  { var i,j, inputs = form.dv.find('.w-data'), data={keys:formkeys, row:{}}, fname;
    for (i=0; i<inputs.length; i++)
    {   var inp = $(inputs[i]), fname;
        fname=inp.attr('id');
        data.row[fname] = inp.val();
    }
    // Добавим выбранные значения ссылок
    for (i in refs) 
    { var r = refs[i];
      if (r.value!=undefined) for (j in r.value) data.row[j] = r.value[j];
    }
    if (isInsert!=undefined && isInsert) data.insert = true;
    ajx('/pages/view/SaveView/'+v, data , function(){ afterSave(); form.hide(); });
    var p = {page:pager.currentPage(), pg_rows:pg_rows}
    if (gsearch!='') p.search=gsearch;
    if (childref!=null) { p.childref = childref; p.fkeys = fkeys; }
    ajx('/pages/view/loadPage/'+v, p ,drawData);
  }

  this.addNew = function()
  {  // console.log(v, 'New:');
   
     if (frmNew==null) 
     { frmNew = new wModal('frmNew'+v, T('Add')+': '+title,
       '<button type="button" class="btn btn-default w-close">'+T('Close')+'</button>\
  <button type="button" class="btn btn-primary w-btnsave">'+T('Add')+'</button>', wcl[edit_width-1]);
       frmNew.draw(drawFormInputs({},''));
       frmNew.dv.find('.w-close').click( function(){ frmNew.hide(); });
       frmNew.dv.find('.w-setlink').unbind().click(function(e){ setLink(e, frmNew); });
       frmNew.dv.find('.w-btnsave').unbind().click(function(){ formSave(frmNew, true); });
    
       /*
       frmEdit.draw(s, function(div)
       { 
          div.find('.w-btnsave').unbind().click(formSave);         
          div.find('.w-view').each(function(i,div){ new view(div); });
          div.find('.w-setlink').unbind().click(setLink);
       });
       */
      
      
     } else frmNew.show();
  }
  
  function drawFormInputs(d, keys)
  {   function addRow(label, input)
      { return '<div class="row"><div class="col-lg-4">'+label+'</div><div class="col-lg-8">'+input+'</div></div>';
      }
    
      var i, s = '';
      for (i in fds)
      {  var r = fds[i];
         var val = '';
         if (d.row!=undefined && d.row[i]!=undefined) val = d.row[i];
         if (r.pkey==1) formkeys[r.fname] = val;
         if (r.visable==1) 
         {   let t = "text";
             let label = T(r.fname);
             if (r.widget_id>4)
             {   if (r.widget_id==5) t="date"; else
                 if (r.widget_id==6) t="datetime-local";  else
                 if (r.widget_id==7) t="time"; 
             } 
             if (r.widget_id==1) s+=addRow('<label for="tname">'+label+'</label>',
             '<div class="input-group w-ref" data-ref="'+r.ref_id+'" data-fid="'+r.id+'"><input type="text" class="form-control w-link" placeholder="'+label+'"  value="'+val+'">\
  <span class="input-group-addon btn btn-default w-setlink">...</span></div>');
             else if (r.widget_id==2) s+='<div class="w-view" data-childref="'+r.ref_id+'" data-keys="'+keys+'"></div>';
             else    
             s+=addRow('<label for="tname">'+label+'</label>',
             '<input type="'+t+'" class="form-control w-data" id="'+r.fname+'" placeholder="'+label+'" value="'+val+'">');

             // ссылка
             //if (r.widget_id==1)
         }
      }
      return s;
  }
  
  function drawForm(d, keys)
  {   var i,s = '';
     
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
      
      console.log('drawForm '+v);
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
          if (r.width!='') w=' style="width:'+r.width+'pt"';
          if (r.visable==1 && r.ingrid==1 && r.widget_id!=2) 
          { hdr+='<th'+w+'>'+T(r.fname)+'</th>';
            pcols.push(i);
          }
          if (r.pkey==1) pkeys.push(i);
          if (r.searchable==1) sfld.push(T(r.fname));
        }
        hdr+='</tr></thead><tbody>';

// панель
s+='<div class="w-panel">';
s+='<div class="row"><div class="col-lg-3"> <span class="w-label">'+T(d.name)+'</span></div>';

// Кнопки

s+='<div class="col-lg-3"> \
 <div class="input-group">';
s+='<button class="btn btn-default w-btn-new" data-toggle="tooltip" data-placement="top" title="'+T('Add')+'" aria-hidden="true"><span class="glyphicon glyphicon-plus"></span></button>';
s+='<button class="btn btn-default w-btn-del" data-toggle="tooltip" data-placement="top" title="'+T('Delete')+'" aria-hidden="true"><span class="glyphicon glyphicon-minus"></span></button>&nbsp;';
s+='<button class="btn btn-default w-btn-sort" data-toggle="tooltip" data-placement="top" title="'+T('Sort')+'" aria-hidden="true"><span class="glyphicon glyphicon glyphicon-sort-by-attributes"></span></button>';
s+='<button class="btn btn-default w-btn-find" data-toggle="tooltip" data-placement="top" title="'+T('Search')+'" aria-hidden="true"><span class="glyphicon glyphicon-search"></span></button>';
s+=' </div>\
</div>';

// поиск
s+='<div class="col-lg-6">';
if (sfld.length>0) s+='<div class="input-group"> \
<input type="text" class="form-control w-stext" data-toggle="tooltip" data-placement="top" title="'+sfld.join('; ')+'" placeholder="'+T('Search')+'"> \
<span class="input-group-btn"> <button class="btn btn-default w-search" type="button">'+T('Search')+'</button> </span> \
</div>';
 
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
        $(div).find('input.w-stext').keypress(function(e){ if (e.charCode==13) self.search(); }).tooltip();
        $(div).find('.w-panel button').tooltip();        
        $(div).find('.w-chb-all').click( function(cb)
        {  var chk =  cb.target.checked;
           $(div).find('.w-chb').each(function(i,e){ $(e).find('input')[0].checked = chk; })  
        });
        if (onSelectRow!=undefined)  $(div).find('table>tbody>tr').click(onSelectRow);
        else $(div).find('table>tbody>tr').click(editForm);
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
  
  this.search = function()
  { var s = $(div).find('input.w-stext').val();
    gsearch = s;
    var p = {page:1, pg_rows:pg_rows, search:s, get_total:true}
    ajx('/pages/view/loadPage/'+v, p ,drawData);
  }
  
  pager.change(function(n){
     var p = {page:n, pg_rows:pg_rows};
     if (gsearch!='') p.search=gsearch;
     if (childref!=null) { p.childref = childref; p.fkeys = fkeys; }
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
