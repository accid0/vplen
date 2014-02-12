var base                                          = null;

model.prototype.vars                              = {
  VAR_EVENTS                                      : {
    like_main                                     : Event.normal_priority,
    posts_synchronize                             : Event.normal_priority
  },
  VAR_POSTS                                       : [],
  VAR_ADDED                                       : null,
  VAR_LIKE_VIEW                                   : null
};

model.prototype.init                              = function () {
  base                                            = this;
};

model.prototype.load_post                         = function (data, view) {

  register Post(parseInt(data.id)) then post {
    stream post VAR_LIKE:event then {
      view.set('refresh-like', post);
    }    
    stream post VAR_MLIKE:event then {
      view.set('refresh-mlike', post);
    }
    stream post VAR_DISLIKE:event then {
      view.set('refresh-dislike', post);
    }
    stream post VAR_LIKE_STATUS:event then {
      view.set('refresh-state', post);
    }
    post.set('VAR_LIKE', data.like);
    post.set('VAR_MLIKE', data.mlike);
    post.set('VAR_DISLIKE', data.dislike);
    post.set('VAR_LIKE_STATUS', data.user);
  }
};

model.prototype.event_like_main                   = function (view, ctrl) {
  var xhr                                         = new _request();
  base.set('VAR_LIKE_VIEW', view);

  ctrl.request.promise('VAR_PARAMETERS').then(function (params) {
    view.set('init-likes', {'selector': params.selector, 'wrap': params.wrap});
  });

  view.asPromiseStream('init').then(function (init) {
    var posts                                     = base.get('VAR_POSTS');
    diff(init, posts);
    base.set('VAR_POSTS', posts.concat(init));
    ctrl.request.promise('VAR_BASE_URL').then(function (base_url) {
      xhr.getAsync(base_url + 'ajax/like/all?id=' + init.join(), function (response) {
        var data  = JSON.parse(response),
            i     = 0;
        for (i in data) {
          base.load_post(data[i], view);
        }
      });
    });
  });
  
  view.asEventStream('onlike').then(function (id) {
    base.register_model('Post', parseInt(id)).then(function (post) {
      post.like();
    });
  });

  view.asEventStream('onmlike').then(function (id) {
    base.register_model('Post', parseInt(id)).then(function (post) {
      post.mlike();
    });
  });

  view.asEventStream('ondislike').then(function (id) {
    base.register_model('Post', parseInt(id)).then(function (post) {
      post.dislike();
    });
  });
};

model.prototype.event_posts_synchronize           = function () {
  base.get('VAR_LIKE_VIEW').set('sync', true);
};
