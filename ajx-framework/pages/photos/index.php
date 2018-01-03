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
<div class="modal large fade" role="dialog" id="photos-form" data-model="/pages/photos/Model/photo">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?=T('Photo')?></h4>
      </div>
      <div class="modal-body">
            <div class="row">
                <div class="col-lg-4"><?php $f->validate('req,minlen=2'); echo $f->date('ddate'); ?></div>
                <div class="col-lg-4"><?php $f->validate('req,minlen=2'); echo $f->date('add_date'); ?></div>
            </div>
            <div class="row">
                <div class="col-lg-4"><?=$f->input('firstname')?></div>
                <div class="col-lg-4"><?=$f->input('lastname')?></div>
                <div class="col-lg-4"><?=$f->input('client')?></div>
            </div>
            <div class="row">
                <div class="col-lg-4"><?=$f->input('b_and_w')?></div>
                <div class="col-lg-4"><?php $f->validate(''); echo $f->input('color')?></div>
                <div class="col-lg-4"><?php $f->validate(''); echo $f->input('Digital')?></div>
            </div>
            <div class="row">
                <div class="col-lg-3"><?=$f->chbox('famous')?></div>
                <div class="col-lg-3"><?=$f->chbox('place')?></div>
                <div class="col-lg-3"><?=$f->chbox('concept')?></div>
                <div class="col-lg-3"><?=$f->chbox('thing')?></div>
            </div>

            <div class="row">
                <div class="col-lg-12"><?=$f->textarea('note',3)?></div>
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

