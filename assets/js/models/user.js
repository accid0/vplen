var base                                            = null;

model.prototype.vars                                = {
  VAR_EVENTS                                        :{
    user_main                                       : Event.normal_priority
  },
};

model.prototype.init                                = function(){
  base                                              = this;
};

model.prototype.event_user_main                     = function(view, ctrl){
  base.register_model('Router').then(function(router){
    base.register_model('Upload').then(function(upload){
      router.execute('upload/selector:.upload').then(function(uview){
        view.set('upload', uview);
      }).fail(function(reasone){
        console.log(reasone);
      });
    }).fail(function(reasone){
      console.log(reasone);
    });
    base.register_model('Like').then(function(like){
      router.execute('like/selector:.postftr .like-wrap/wrap:#posts_html').then(function(lview){
        view.set('like', lview);
      }).fail(function(reasone){
        console.log(reasone);
      });
    }).fail(function(reasone){
      console.log(reasone);
    });
  });
};
