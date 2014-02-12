var base                                                  = null,
    locked                                                = 0;

model.prototype.vars                                      = {
  VAR_EVENTS                                              : {},
  VAR_ID                                                  : null,
  VAR_LIKE                                                : null,
  VAR_MLIKE                                               : null,
  VAR_DISLIKE                                             : null,
  VAR_LIKE_STATUS                                         : null
};

model.prototype.init                                      = function(){
  base                                                    = this;
};

model.prototype.capture                                   = function(injector, id){
  base.ensure(2 !== arguments.length, 'Post required only one argument.');
  base.set('VAR_ID', id);
};

model.prototype.like                                      = function(){
  base.onlike('like', 'VAR_LIKE');
};

model.prototype.mlike                                     = function(){
  base.onlike('mlike', 'VAR_MLIKE');
};

model.prototype.dislike                                   = function(){
  base.onlike('dislike', 'VAR_DISLIKE');
};

model.prototype.onlike                                    = function(state, name){
  if(locked++){
    locked--;
    return;
  }
  base.register_model('Request').promise('VAR_BASE_URL').then(function(base_url){
    var xhr                                               = new _request,
        status                                            = base.get('VAR_LIKE_STATUS'),
        old                                               = ('like' === status ? 'VAR_LIKE' : 
          ('mlike' === status ? 'VAR_MLIKE' : 
            ('dislike' === status ? 'VAR_DISLIKE' : null))),
        op                                                = (state === status ? 'delete' : 'add'),
        del                                               = ('delete' === op);
    xhr.getAsync(base_url+'ajax/like/'+op+'?type='+state+'&id='+base.get('VAR_ID'), function(response){
      var num                                             = parseInt(base.get(name)),
          oldv                                            = (null !== old ? parseInt(base.get(old)) : null),
          data                                            = parseInt(JSON.parse(response).response);
      locked--;
      if((data !== 1 && del) || (data !== 1 && data !== 2 && !del)) return;
      if('delete' !== op)
        base.set(name, ++num);
      if(null !== old)
        base.set(old, oldv > 0 ? --oldv : 0);
      base.set('VAR_LIKE_STATUS', 'add' === op ? state : null);
    });
  });
};
