var base                                        = null;

model.prototype.vars                            = {
  VAR_EVENTS                                    : {
    search_save                                 : Event.normal_priority,
    search_delete                               : Event.normal_priority,
    search_change                               : Event.normal_priority
  },
  VAR_SAVED_SEARCH                              : null,
  VAR_GUI_SAVE                                  : null,
  VAR_GUI_DELETE                                : null
}

model.prototype.init                            = function(){
  base  = this;
  (function($){
    base.set('VAR_GUI_SAVE', $('#savesearch'));
    base.set('VAR_GUI_DELETE', $('#remsearch'));
    base.get('VAR_GUI_SAVE').on('click', function(event){
      event.preventDefault();
      Controller.execute('search_save');
    });
    base.get('VAR_GUI_DELETE').on('click', function(event){
      event.preventDefault();
      Controller.execute('search_delete');
    });
  })(jQuery);
  base.set('VAR_SAVED_SEARCH', new Array);
}

model.prototype.event_search_save               = function(view, ctrl){
  save_search_on(view.get('VAR_URL') + '/from:ajax/savesearch:on');
  var saved = base.get('VAR_SAVED_SEARCH');
  saved[view.get('VAR_URL')] = true;
}

model.prototype.event_search_delete             = function(view, ctrl){
  save_search_off(view.get('VAR_URL') + '/from:ajax', 'Вы уверены?');
  var saved = base.get('VAR_SAVED_SEARCH');
  saved[view.get('VAR_URL')] = null;
}

model.prototype.event_search_change             = function(view, ctrl){
  var saved = base.get('VAR_SAVED_SEARCH'), url = view.get('VAR_URL');
  if('undefined' !== typeof saved[url] && null !== saved[url]){
    base.get('VAR_GUI_SAVE').css('display', 'none');
    base.get('VAR_GUI_DELETE').css('display', 'block');
  }
  else{
    base.get('VAR_GUI_SAVE').css('display', 'block');
    base.get('VAR_GUI_DELETE').css('display', 'none');
  }
}
