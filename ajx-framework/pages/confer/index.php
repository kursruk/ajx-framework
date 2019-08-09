<style type="text/css">
  div.w-tabpane
  { border-left: solid #ddd 1px;
    border-right: solid #ddd 1px;
    border-bottom: solid #ddd 1px;
    border-radius: 0 0px 4px 4px;
  }
  #flist { overflow-y: scroll;   height: 206pt; }
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
                  <label for="vtitle"><?=T('Name')?></label>
                  <input type="text" class="form-control" id="vtitle" placeholder="<?=T('Name')?>">
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
                                <label for="ftitle">Подпись</label>
                                <input type="text" class="form-control" id="ftitle" placeholder="Подпись">
                              </div>
                              <div class="form-group">
                                <label for="width">Ширина</label>
                                <input type="text" class="form-control" id="width" placeholder="Ширина">
                              </div>
                              <div class="checkbox"><label><input id="pkey" type="checkbox">Первичный ключ</label></div>
                              <div class="checkbox"><label><input id="visable" type="checkbox">Показать поле пользователю</label></div>
                              <div class="checkbox"><label><input id="ingrid" type="checkbox">Включить в списочную форму</label></div>
                              <div class="checkbox"><label><input id="searchable" type="checkbox">Включить поиск по полю</label></div>
                              <div class="checkbox"><label><input id="required" type="checkbox">Обязательно для заполнения</label></div>
                              <div class="form-group">
                                <label for="width">Значение по умолчанию</label>
                                <input type="text" class="form-control" id="default_value" placeholder="Значение по умолчанию">
                              </div>    
                                <div class="form-group">
                                  <label>Тип виджета</label>
                                  <select id="widget_id" class="input-large form-control">
                                      <option value="null" selected="selected">не выбран</option>
                                      <option value="1">справочное поле</option>
                                      <option value="2">подчинённая таблица</option>
                                      <option value="3">многострочный текст</option>
                                      <option value="4">флажок</option>
                                      <option value="5">дата</option>
                                      <option value="6">дата/время</option>
                                      <option value="7">время</option>
                                  </select>
                                </div>   
                            </form>
                       </div>
                       <button id="btnSave" class="btn btn-default" style="margin-top: 20px;">Сохранить</button>
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

