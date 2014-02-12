var base                                      = null;

model.prototype.vars                          = {
  VAR_EVENTS                                  : {
    sync                                      : Event.high_priority,
    search_save                               : Event.high_priority,
    search_delete                             : Event.high_priority,
    search_change                             : Event.high_priority,
    gui_form                                  : Event.normal_priority
  },
  VAR_FORM                                    : null,
  VAR_SAVE_PAGE                               : true,
  VAR_INPUT_LOOKFOR                           : null,
  VAR_FORM_LOCK                               : false,
  VAR_LOOKIN                                  : 'posts',
  VAR_PTYPE                                   : false,
}

model.prototype.init                          = function(){
  base                                        = this;
  base.load();
}

model.prototype.load                          = function(){
  var lookin                                  = window.location.href.match(/\/tab\:([^\/]+)/);
  if('undefined' !== typeof lookin && null !== lookin){
    base.set('VAR_LOOKIN', lookin[1]);
  }
  (function($){
    base.set('VAR_FORM', $('#pagebody > #invcenter form'));
    base.get('VAR_FORM').submit(base.submit).on('change', 'select', function(event){
      var attr;
      if('sortin' != (attr = $(this).attr('name'))){
        base.set('VAR_LOOKIN', false);
        base.set('VAR_PTYPE', false);
        attr = 'lookin' !== attr ? 'lookin' : 'ptype[]';
        base.get('VAR_FORM').find('select[name="'+attr+'"]').val('');
      }
      base.submit(event);
    });
    base.get('VAR_FORM').find('#searchinput input[name="lookfor"]').on('change', function(event){
      event.preventDefault();
      base.set('VAR_INPUT_LOOKFOR', $(this).val());
    });
    base.get('VAR_FORM').find('.search-link').on('click', function(){
      var selector = $(this).hasClass('search-lookin') ? 'VAR_LOOKIN' : 'VAR_PTYPE'
          , data   = $(this).attr('data-id');
      base.set(selector, data);
      if(selector === 'VAR_PTYPE'){
        base.set('VAR_LOOKIN', 'posts');
      }
      else{
        base.set('VAR_PTYPE', false);
      }
      base.get('VAR_FORM').find('select[name="lookin"], select[name="ptype[]"]').val('');
      base.submit();
    });
  })(jQuery);
  Controller.execute('gui/form');
}

model.prototype.get_url                       = function(){
  var ser   = base.get('VAR_FORM').serializeArray();
  var ptype = '', data = new Array;
  for( var i in ser){
    if('ptype[]' === ser[i].name){
      ptype = ptype.strlen ? ',' + ser[i].value : ser[i].value;
    }
    else{
      data[ser[i].name] = ser[i].value;
    }
  }
  var url = _root_core_path.base + "search", bptype = base.get('VAR_PTYPE'), lookin = base.get('VAR_LOOKIN');
  url += '/ptypes:' + (bptype ? bptype : encodeURIComponent(ptype ? ptype : ''));
  url	+= '/s:' + encodeURIComponent(data.lookfor ? data.lookfor : '');
  url	+= '/tab:' + (lookin ? lookin : encodeURIComponent(data.lookin ? data.lookin : ''));
  url	+= '/comment:' + encodeURIComponent(data.comment ? data.comment : '');
  url += '/sortin:' + encodeURIComponent(data.sortin ? data.sortin : '');
  var purl = base.get('VAR_SAVE_PAGE') ? window.location.href.match(/\/pg\:[^\/]+/g) : null;
  if('undefined' !== typeof purl && null !== purl){
    url += purl[0].toString();
  }
  return url;
}

model.prototype.submit                        = function( event ){
  if('undefined'  !== typeof event)
    event.preventDefault();
  base.set('VAR_SAVE_PAGE', false);
  base.set('VAR_FORM_LOCK', true);
  Controller.execute('Sync').promise('VAR_FINISHED').then(function(){
    base.set('VAR_FORM_LOCK', false);
  });
  Controller.execute('Search_Change');
};

model.prototype.event_sync                    = function(view, ctrl){
  view.set('VAR_URL', base.get_url());
  view.set('VAR_FORM_LOCK', base.get('VAR_FORM_LOCK'));
};

model.prototype.event_search_save             = function(view, ctrl){
  view.set('VAR_URL', base.get_url());
};

model.prototype.event_search_delete           = function(view, ctrl){
  view.set('VAR_URL', base.get_url());
};

model.prototype.event_search_change           = function(view, ctrl){
  view.set('VAR_URL', base.get_url());
};

model.prototype.event_gui_form                = function(view, ctrl){
  base.on('VAR_INPUT_LOOKFOR', function(result){
    result.then(function(query){
      view.set('VAR_INPUT_LOOKFOR', query);
    });
  });
};
