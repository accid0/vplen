var base                                      = this;

model.prototype.vars                          = {
  VAR_EVENTS                                  : {
    smile_main                                : Event.normal_priority
  }

};

model.prototype.init                          = function(){
  base                                        = this;
};

model.prototype.event_smile_main              = function(view, ctrl){
  ctrl.request.promise('VAR_PARAMETERS').then(function(params){
    view.set('selector', params.selector);
  });
};
