var base                                      = null;

model.prototype.vars                          = {
  VAR_EVENTS                                  : {
    search_main                               : Event.normal_priority,
    user_main                                 : Event.normal_priority,
    getattachment_main                        : Event.normal_priority
  },
};

model.prototype.init                          = function () {
  base                                        = this;
  base.register_model('Snippet');
  base.register_model('Request').then(function (request) {
    base.register_model('Router').then(function (router) {
      request.promise('VAR_REQUEST_URI').then(function (uri) {
        if ('undefined' !== typeof uri[0] &&
          '' !== uri[0] &&
          null === uri[0].match(/^search/g) &&
          null === uri[0].match(/^getattachment/g)) {
          uri[0]  = 'login:' + uri[0];
          uri.unshift('user');
          request.set('VAR_REQUEST_URI', uri);
        }
        else if ('' === uri[0]) {
          uri[0] = 'search';
        }
        return uri;
      }).then(function () {
        router.run(request);
      });
    });
    request.init_global();
  });
  base.register_model('Drag');
};

model.prototype.event_search_main             = function () {
  base.register_model('Sync');
  base.register_model('Form');
  base.register_model('Search');
};

model.prototype.event_user_main               = function () {
  base.register_model('User');
};

model.prototype.event_getattachment_main      = function () {
  base.register_model('Attachment');
};
