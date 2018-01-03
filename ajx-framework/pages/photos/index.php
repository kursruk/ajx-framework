<?php
    $allow_edit =  $this->allow_edit;
?>
<!--
<ol class="breadcrumb">
  <li><a href="<?=mkURL('/photos')?>"><?=T('Photos')?></a></li>
  <li class="active" ><?=$this->cfg->title?></li>
</ol>
-->

<!-- <h2 class="uppertitle"><?=$this->cfg->title?></h2> -->

<?php
    include(SYS_PATH.'lib/bsforms.php');
    $f = new BSformDefault();
?>

        
<div class="model-list" data-model="/pages/photos/Model/photo">
    <div class="row">
        <div class="col-lg-12">
            <div class="input-group model-search">
                <input type="text" class="form-control" data-toggle="tooltip" data-placement="top" title="Search" placeholder="<?=T('Search')?>">
                <span class="input-group-btn">
                    <button class="btn btn-default b-search" type="button">
                        <span class="glyphicon glyphicon-search"></span>&nbsp;
                        <?=T('Search')?>
                    </button> 
                    <button class="btn btn-default b-clean" type="button">
                        <span class="glyphicon glyphicon glyphicon-remove"></span>&nbsp;
                        <?=T('Clean')?>
                    </button> 
                </span>
            </div>
        </div>
    </div>
    <div class="row w-fsearch">
        <div class="col-lg-4"><?=$f->modelSelect('wild','/pages/photos/Model/lkwild')?></div>
        <div class="col-lg-4"><?=$f->search3dot('sic_code','sic')?></div>                
        <div class="col-lg-4"></div>  
    </div>
    <div class="model-pager"></div>
    <table class="table table-striped selectable">
        <thead></thead>
        <tbody></tbody>
    </table>
</div>


<div id="search_sic"></div>
<div id="search_sic2"></div>
<div id="search_sic3"></div>
<div id="newdivision"></div>
<div id="editdivisions"></div>



<!-- Modal -->
<div class="modal fade" role="dialog" id="photos-form" data-model="/psys/admin/Model/photo">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?=T('User')?></h4>
      </div>
      <div class="modal-body">
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('req,minlen=2'); echo $f->input('name'); ?></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('req,minlen=2'); echo $f->input('firstname');?></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><?=$f->input('lastname')?></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('email'); echo $f->input('email')?></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><?=$f->input('phone')?></div>
            </div>           
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('minlen=3'); echo $f->input('pass','password');?></div>
            </div> 
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('minlen=3,equalto=#pass'); echo $f->input('pass2','password');?></div>
            </div> 
            <?=$f->key('id')?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?=T('Close')?></button>
        <button type="button" class="btn btn-default btn-success b-useradd"><?=T('Save')?></button>
      </div>
    </div>

  </div>
</div>

