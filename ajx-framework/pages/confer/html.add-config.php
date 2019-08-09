<?php
    include(SYS_PATH.'lib/bsforms.php');
    $f = new BSformDefault();
?>
<!-- Modal -->
<div class="modal fade" role="dialog" id="add-config-form" data-model="/pages/confer/Model/conf">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?=T('ADD_CONFIG')?></h4>
      </div>
      <div class="modal-body">
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('req,minlen=3'); echo $f->input('conf'); ?></div>
                <div class="col-lg-12"><?php $f->validate('req,minlen=1'); echo $f->input('version'); ?></div>
                <div class="col-lg-12"><?php $f->validate('req,minlen=1'); echo $f->input('minor_version');?></div>                
            </div>           
            <!-- <?=$f->key('id')?>  -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?=T('Close')?></button>
        <button type="button" class="btn btn-default btn-success b-config-save"><?=T('Save')?></button>
      </div>
    </div>

  </div>
</div>
