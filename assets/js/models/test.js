var base$1073 = null;
model.prototype.vars = {
    VAR_EVENTS: {
        like_main: Event.normal_priority,
        posts_synchronize: Event.normal_priority
    },
    VAR_POSTS: [],
    VAR_ADDED: null,
    VAR_LIKE_VIEW: null
};
model.prototype.init = function () {
    base$1073 = this;
};
model.prototype.load_post = function (data$1074, view$1075) {
    Model.register_model('Post');
    base$1073.register_model('Post', parseInt(data$1074.id)).then(function (post$1079) {
        post$1079.asEventStream('VAR_LIKE').then(function () {
            view$1075.set('refresh-like', post$1079);
        });
        post$1079.asEventStream('VAR_MLIKE').then(function () {
            view$1075.set('refresh-mlike', post$1079);
        });
        post$1079.asEventStream('VAR_DISLIKE').then(function () {
            view$1075.set('refresh-dislike', post$1079);
        });
        post$1079.asEventStream('VAR_LIKE_STATUS').then(function () {
            view$1075.set('refresh-state', post$1079);
        });
        post$1079.set('VAR_LIKE', data$1074.like);
        post$1079.set('VAR_MLIKE', data$1074.mlike);
        post$1079.set('VAR_DISLIKE', data$1074.dislike);
        post$1079.set('VAR_LIKE_STATUS', data$1074.user);
    });
};
model.prototype.event_like_main = function (view$1084, ctrl$1085) {
    var xhr$1086 = new _request();
    base$1073.set('VAR_LIKE_VIEW', view$1084);
    ctrl$1085.request.promise('VAR_PARAMETERS').then(function (params$1094) {
        view$1084.set('init-likes', {
            'selector': params$1094.selector,
            'wrap': params$1094.wrap
        });
    });
    view$1084.asPromiseStream('init').then(function (init$1095) {
        var posts$1096 = base$1073.get('VAR_POSTS');
        diff(init$1095, posts$1096);
        base$1073.set('VAR_POSTS', posts$1096.concat(init$1095));
        ctrl$1085.request.promise('VAR_BASE_URL').then(function (base_url$1100) {
            xhr$1086.getAsync(base_url$1100 + 'ajax/like/all?id=' + init$1095.join(), function (response$1101) {
                var data$1102 = JSON.parse(response$1101), i$1103 = 0;
                for (i$1103 in data$1102) {
                    base$1073.load_post(data$1102[i$1103], view$1084);
                }
            });
        });
    });
    view$1084.asEventStream('onlike').then(function (id$1104) {
        base$1073.register_model('Post', parseInt(id$1104)).then(function (post$1106) {
            post$1106.like();
        });
    });
    view$1084.asEventStream('onmlike').then(function (id$1107) {
        base$1073.register_model('Post', parseInt(id$1107)).then(function (post$1109) {
            post$1109.mlike();
        });
    });
    view$1084.asEventStream('ondislike').then(function (id$1110) {
        base$1073.register_model('Post', parseInt(id$1110)).then(function (post$1112) {
            post$1112.dislike();
        });
    });
};
model.prototype.event_posts_synchronize = function () {
    base$1073.get('VAR_LIKE_VIEW').set('sync', true);
};