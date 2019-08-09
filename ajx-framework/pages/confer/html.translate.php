<?php
  //  include(SYS_PATH.'lib/bsforms.php');
  //  $f = new BSformDefault();
?>
<!-- Modal -->
<div class="modal fade" role="dialog" id="translate-form" data-model="/pages/confer/Model/conf">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?=T('TRANSLATE')?></h4>
      </div>
      <div class="modal-body">
            <div class="row w-scroll">
               <table class="table table-striped w-tr-text">
                  <thead>
                     <tr><th><?=T('SOURCE_TEXT')?></th><th><?=T('TRANSLATED_TEXT')?></th></tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>           
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?=T('Close')?></button>
        <button type="button" class="btn btn-default btn-success b-translation-save"><?=T('Save')?></button>
      </div>
    </div>

  </div>
</div>
