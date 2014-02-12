var base                                      = null;

model.prototype.vars                          = {
  VAR_EVENTS                                  : {
    sync                                      : Event.normal_priority
  },
  VAR_TIMEOUT                                 : 15000,
  VAR_LOCK                                    : false
}

model.prototype.init                          = function(){
  base                                        = this;
  setInterval(base.load, base.get('VAR_TIMEOUT'));
}

model.prototype.load                          = function(){
  Controller.execute('Sync');
}

model.prototype.event_sync                    = function( view, ctrl){
  var timeout_id                              = null;
  if(base.get('VAR_LOCK')) return;
  else{
    base.set('VAR_LOCK', true);
    timeout_id = setTimeout(function(){
      base.set('VAR_LOCK', false)
    }, base.get('VAR_TIMEOUT'));
  }
  view.promise('VAR_URL').then(function(url){
    url	+= "/from:ajax/r:"+Math.round(Math.random()*1000);
    (function($){
      var i, ch, lastpostdate = 0, lastpostdates = [];
      $('#posts_html').children().each(function(){
        var tmpdate = parseInt($(this).attr('postdate'), 10);
        if( tmpdate > lastpostdate ){
          lastpostdate = tmpdate;
        }
      });
      $.post(url, "lastpostdate="+encodeURIComponent(lastpostdate), function(txt) {
        txt = jQuery.trim(txt);
        if( txt.substr(0,3) != "OK:" ) { return; }
        var get_last_post_id	= txt.match(/LAST_POST_ID\:([0-9]+)\:/g); //edit match here
        if( ! get_last_post_id ) { 
          return;
        }
        get_last_post_id	= get_last_post_id.toString().match(/([0-9]+)/g); //edit
        get_last_post_id = parseInt(get_last_post_id, 10);
        last_post_id = get_last_post_id;
        
        txt	= txt.replace(/^OK\:([0-9]+)\:NUM_POSTS\:([0-9]+)\:LAST_POST_ID\:([0-9]+)\:/, ""); 
        
        $("#posts_html").html(txt);
        
        $('#posts_html').children().each(function(){
          var post = this;
          view.promise('VAR_FORM_LOCK').then(function(result){
            if(result){
              $(post).css('display', 'none');
            }
            if( $(post).css('display') == 'none' ){
              $(post).slideToggle('slow');
            }
          });
        });
        
        $("#posts_html input").each(function(){
          postform_forbid_hotkeys_conflicts(this);
        });
        $("#posts_html textarea").each(function(){
          postform_forbid_hotkeys_conflicts(this);
          input_set_autocomplete_toarea(this); 
        });
        clearTimeout(timeout_id);
        
      }).always(function(){
        base.set('VAR_LOCK', false);
        view.set('VAR_FINISHED', true);
      });
    })(jQuery);
  });
}
