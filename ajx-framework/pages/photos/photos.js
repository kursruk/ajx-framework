var pager;

var photoForm = null;
    
$(function(){

  if ($('.model-list').length>0)
  { 
    var filterData = new modelFormController('.w-fsearch');
       
    var photoEdit = new modelEditableListView(); 

    
    function addPhoto()
    {  photoForm.clearData();
       $('#photos-form').modal();
    }
    
    photoEdit.onmninsert(addPhoto);  
    $('#btnew').click(addPhoto);

    photoEdit.onmnedit(function(row){
       photoForm.loadrow({id:row.id_photo});
       $('#photos-form').modal();
    });  
    
    
    photoEdit.onmndelete(function(rows){
       if (confirm('Remove selected Photo(s)?'))
       {   var model = $('#photos-form').attr('data-model');
           ajx(model+'/deleteRows', {rows:rows}, function(d){
             if (!d.error)                  
             {  Photos.refresh();
                setOk('Deleted!');
             }
           });
       }
    });
    
    var model = new modelListController('.model-list', photoEdit.draw);
    
    model.load();
    
    model.click(function(e, row){
           // console.log(row);
    });
    
    photoForm = new modelFormController('#photos-form');
   
    var wildList = new modelListController('.wild-list');
    var refList = new modelListController('.ref-list');
   
    photoForm.loaded(function(d){
      // $('#useradd-form #pass2').val('');
      wildList.load({master:d.id_photo});
      refList.load({master:d.id_photo});
      console.log(d);
    });
   
    photoForm.updated(function(d){
             if (!d.error) 
             {  $('#useradd-form').modal('hide');
                users.refresh();
             }
    });
       
       
    // enable pager
    pager = new modelPagination('.model-list .model-pager');
       
    model.total(function(total, rows_lim){
           pager.setTotal(total, rows_lim);
    })
    
    pager.change(function(n){
           model.load(n);
    });
    
    var wpager = new modelPagination('.wild-list .model-pager');
    
    wildList.total(function(total, rows_lim){
           wpager.setTotal(total, rows_lim);
    });
    
    wpager.change(function(n){
           wildList.load(n);
    });

    var rpager = new modelPagination('.ref-list .model-pager');
    
    refList.total(function(total, rows_lim){
           rpager.setTotal(total, rows_lim);
    });
    
    rpager.change(function(n){
           refList.load(n);
    });
    
   } 
   
    // Model select init
    $('.bs-model-select').each(function(i,e){
        var sel = $(e);
        var model = sel.attr('data-model')+'/load';
        ajx(model,{},function(d){
            var s = '<option value=""></option>';
            for (var i=0; i<d.rows.length; i++)
            {   var r = d.rows[i];
                s+='<option value="'+r.id+'">'+r.name+'</option>';
            }
            sel.find('select').html(s);
        });

    });
    
   // Search
   $('.model-list .model-search button.b-search').click(function(){
       var s = $('.model-list .model-search input').val().trim();
       var p = filterData.getData(true);
       console.log(p);
       if (p.sic!=undefined) delete p.sic; //remove unused data
       if (s!='' || p.filter!='')
       {   if (s!='') p.search = '%'+s+'%';
           model.load(p);
       } else model.load();
   });
   
   $('.model-list .model-search input').keyup(function(d){ 
           if (d.keyCode==13)  $('.model-list .model-search button').trigger('click');
   });
    
});
