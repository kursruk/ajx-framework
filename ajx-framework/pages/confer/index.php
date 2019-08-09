<style type="text/css">
  div.w-tabpane
  { border-left: solid #ddd 1px;
    border-right: solid #ddd 1px;
    border-bottom: solid #ddd 1px;
    border-radius: 0 0px 4px 4px;
  }
  #flist { overflow-y: scroll;   height: 206pt; }
  .w-scroll { overflow-y: scroll;   height: 200pt; }
</style>

<div>
    <div class="row">
          <div class="col-lg-4">
                <div class="input-group">
                   <select class="form-control s-conf-selector" data-model="/pages/confer/Model/conf">
                   </select>
                   <span class="input-group-btn">
                     <button  class="btn btn-primary bt-add-config" type="button" title="<?=T('ADD_NEW_CONFIG')?>"><?=T('New')?></button>
                   </span>
                </div>
                <div id="tree"></div>
          </div>
          <div class="col-lg-4">
            
            <div class="add-view" style="display:none">
                <div class="form-group">
                     <button type="button" class="btn btn-success b-create-view"><?=T('NEW_VIEW')?></button>
                </div>
            </div>
         
            <div id="editor" class="view-editor">
                <div class="form-group">
                  <label for="tname"><?=T('Table')?></label>
                  <input type="text" class="form-control" id="tname" placeholder="<?=T('Table')?>">
                </div>
                <div class="form-group">
                  <label for="name"><?=T('Identificator')?></label>
                  <input type="text" class="form-control" id="name" placeholder="<?=T('Identificator')?>">
                </div>
                <div class="form-group">
                  <label for="vtitle"><?=T('Title')?></label>
                  <input type="text" class="form-control" id="vtitle" placeholder="<?=T('Title')?>">
                </div>
                 <div class="form-group">
                    <label><?=T('EDIT_FORM')?></label>
                    <select id="edit_width" class="input-large form-control">
                        <option value="1" selected="selected"><?=T('Slim')?></option>
                        <option value="2"><?=T('Normal')?></option>
                        <option value="3"><?=T('Wide')?></option>
                    </select>
                  </div>   
                <div id="flist" class="list-group">
                </div>
                <a class="l-check" target="__blank" href="/view"><?=T('CHECK_TABLE')?></a><br>
                <a class="l-translate" target="__blank" href="javascript:"><?=T('TRANSLATE')?></a>
            </div>
          </div>
          <div class="col-lg-4">
                <div class="view-editor">
                  <!-- Nav tabs -->
                  <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#fileds" aria-controls="fileds" role="tab" data-toggle="tab"><?=T('FIELD_PROPERTIES')?></a></li>
                    <li role="presentation"><a href="#refs" aria-controls="refs" role="tab" data-toggle="tab"><?=T('References')?></a></li>
                    <li role="presentation"><a href="#acl" aria-controls="acl" role="tab" data-toggle="tab"><?=T('ACCESS_RIGHTS')?></a></li>
                  </ul>

                  <!-- Tab panes -->
                  <div class="tab-content">
                  <!-- Представление -->
                    <div role="tabpanel" class="tab-pane active" id="fileds">
                      <div id="fldattr" class="panel-body w-tabpane">
                           <form>
                              <div class="form-group">
                                <label for="fname"><?=T('Field')?></label>
                                <input type="text" class="form-control" id="fname" placeholder="<?=T('Field')?>">
                              </div>
                              <div class="form-group">
                                <label for="ftitle"><?=T('Title')?></label>
                                <input type="text" class="form-control" id="ftitle" placeholder="<?=T('Title')?>">
                              </div>
                              <div class="form-group">
                                <label for="width"><?=T('Width')?></label>
                                <input type="text" class="form-control" id="width" placeholder="<?=T('Width')?>">
                              </div>
                              <div class="checkbox"><label><input id="pkey" type="checkbox"><?=T('PRIMARY_KEY')?></label></div>
                              <div class="checkbox"><label><input id="visable" type="checkbox"><?=T('SHOW_FIELD')?></label></div>
                              <div class="checkbox"><label><input id="ingrid" type="checkbox"><?=T('IN_GRID')?></label></div>
                              <div class="checkbox"><label><input id="searchable" type="checkbox"><?=T('SEARCHABLE')?></label></div>
                              <div class="checkbox"><label><input id="required" type="checkbox"><?=T('REQUIRED')?></label></div>
                              <div class="form-group">
                                <label for="width"><?=T('DEFAULT_VALUE')?></label>
                                <input type="text" class="form-control" id="default_value" placeholder="<?=T('DEFAULT_VALUE')?>">
                              </div>    
                                <div class="form-group">
                                  <label><?=T('WIDGET_TYPE')?></label>
                                  <select id="widget_id" class="input-large form-control">
                                      <option value="null" selected="selected"><?=T('NOT_SELECTED')?></option>
                                      <option value="1"><?=T('REF_FIELD')?></option>
                                      <option value="2"><?=T('CHILD_TABLE')?></option>
                                      <option value="3"><?=T('TEXTAREA')?></option>
                                      <option value="4"><?=T('CHECKBOX')?></option>
                                      <option value="5"><?=T('DATE')?></option>
                                      <option value="6"><?=T('DATE_TIME')?></option>
                                      <option value="7"><?=T('TIME')?></option>
                                  </select>
                                </div>   
                            </form>
                       </div>
                       <button id="btnSave" class="btn btn-primary btn-lg" style="margin-top: 20px;"><?=T('Save')?></button>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="refs">
                       <div id="edit-refs" class="panel-body w-tabpane">
                       </div>
                      
                    </div>

                    <div role="tabpanel" class="tab-pane" id="acl">
                      <div id="edit-acl" class="panel-body w-tabpane">
                       </div>
                    </div>

                  </div>
              </div>
          </div>
     </div>
</div>

<div id="view-add-config"></div>
<div id="translate"></div>



