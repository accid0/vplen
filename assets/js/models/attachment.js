var base                                      = this;

model.prototype.vars                          = {
  VAR_EVENTS                                  : {
    getattachment_main                        : Event.normal_priority
  }
};

model.prototype.init                          = function(){
  base                                        = this;
};

model.prototype.event_getattachment_main      = function(view, ctrl){
  ctrl.request.promise('VAR_PARAMETERS').then(function(params){
    var current                               = params.attid;
    view.set('current', parseInt(current, 10));
    view.asPromiseStream('next').then(function(){
      view.set('current', ++current);
    });
    view.asPromiseStream('prev').then(function(){
      view.set('current', --current);
    });
  });
};
