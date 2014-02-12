var base                                          = null;

model.prototype.vars                              = {
  VAR_EVENTS                                      : {
    upload_main                                   : Event.normal_priority,
    upload_edit                                   : Event.normal_priority
  },
  VAR_UPLOAD_SELECTOR                             : '',
  VAR_FILES                                       : new Array,
  VAR_MAX_ATTACHED                                : 10,
  VAR_MODEL_SMILE                                 : null,
  VAR_MODEL_ROUTER                                : null
};

model.prototype.init                              = function(){
  base                                            = this;
  window.FileAPI = {
      debug: false   // debug mode, see Console
    , cors: false    // if used CORS, set `true`
    , media: false   // if used WebCam, set `true`
    , staticPath: Autoloader.config.app + 'uploader/dist/' // path to '*.swf'
    , postNameConcat: function (name, idx){
        // Default: object[foo]=1&object[bar][baz]=2
        // .NET: https://github.com/mailru/FileAPI/issues/121#issuecomment-24590395
      return  name + (idx != null ? '['+ idx +']' : '');
    }
  };
};

model.prototype.event_upload_main                 = function(view, ctrl){
  ctrl.request.promise('VAR_PARAMETERS').then(function(params){
    base.set('VAR_UPLOAD_SELECTOR', params.selector);
    view.set('selector', params.selector);
  });
  base.register_model('Smile');
  base.register_model('Router').then(function(router){
    router.execute('smile/selector:#postform .post-message').then(function(sview){
      view.set('smile', sview);
    });
  });
  include_once(Autoloader.config.app + 'uploader/dist/FileAPI.min.js', function(done){
    view.asPromiseStream('element.input').then(function(el){
      $.each(el, function(id, el){
        FileAPI.event.on(el, 'change paste', function (evt){
          evt.preventDefault();
          base.append_files(FileAPI.getFiles(evt));
          FileAPI.reset(evt.currentTarget);
        });
      });
    }).fail(function(reasone){
      console.log(reasone);
    });
  });
  
  view.set('max_size', 25*FileAPI.MB);
  base.asEventStream('VAR_FILES', view).then(function(files){
    view.set('files', files);
    view.set('submit', true);
  });
  view.asEventStream('remove').then(function(id){
    base.remove(id);
  });
  view.asEventStream('submit').then(function(){
    var files = base.get('VAR_FILES');
    for(var i in files){
      files[i].upload();
    }
    postform_attachbox_close();
  });
  view.asEventStream('submit.video').then(function(video){
    base.append_video(video);
  });
  view.asEventStream('submit.link').then(function(link){
    base.append_link(link);
  });
  view.asEventStream('submit.image').then(function(image){
    base.append_image(image);
  });
  view.asEventStream('submit.post').then(function(){
    base.set('VAR_FILES', new Array);
  });
};

model.prototype.remove                            = function(id){
  var current                                     = base.get('VAR_FILES');
  current[id].remove();
  current.splice(id,1);
  base.set('VAR_FILES', current);
};

model.prototype.append_image                      = function(image){
  var current                                     = base.get('VAR_FILES');
  if(current.length >= base.get('VAR_MAX_ATTACHED')) return;
  return Model.factory('uploader/image').then(function(obj){
    obj.load(image);
    current.unshift(obj);
    base.set('VAR_FILES', current);
    return obj;
  });
};

model.prototype.append_video                      = function(video){
  var current                                     = base.get('VAR_FILES');
  if(current.length >= base.get('VAR_MAX_ATTACHED')) return;
  return Model.factory('uploader/video').then(function(obj){
    obj.load(video);
    current.unshift(obj);
    base.set('VAR_FILES', current);
    return obj;
  });
};

model.prototype.append_link                       = function(link){
  var current                                     = base.get('VAR_FILES');
  if(current.length >= base.get('VAR_MAX_ATTACHED')) return;
  return Model.factory('uploader/link').then(function(obj){
    obj.load(link);
    current.unshift(obj);
    base.set('VAR_FILES', current);
    return obj;
  });
};

model.prototype.append_files                      = function(files){
  var current                                     = base.get('VAR_FILES'),
      file                                        = null,
      len                                         = (base.get('VAR_MAX_ATTACHED') > files.length ? base.get('VAR_MAX_ATTACHED') : files.length),
      task                                        = [];
  if(current.length >= base.get('VAR_MAX_ATTACHED')) return;
  for(var i in files){
    if(current.length >= base.get('VAR_MAX_ATTACHED')) return;
    task.push(base.load_file(files[i]));
  }
  return RSVP.all(task).then(function(data){
    base.set('VAR_FILES', base.get('VAR_FILES').concat(data));
    return data;
  }).fail(function(reasone){
    console.log(reasone);
  });
};

model.prototype.load_file                         = function(file){
  var current                                     = base.get('VAR_FILES');
  return new RSVP.Promise(function(resolve, reject){
    Model.factory('uploader/file').then(function(model){
      if(current.length >= base.get('VAR_MAX_ATTACHED')) return;
      model.load(file);
      resolve(model);
    }).fail(function(reasone){
      reject('Error while load file');
    });
  });
};

model.prototype.event_upload_edit                 = function(view, ctrl){
  base.set('VAR_FILES', new Array);
  ctrl.request.promise('VAR_PARAMETERS').then(function(params){
    var i, tp, id, txt, data = params.data, postid = params.postid,
        st                                        = null;
    for(i=0; i<data.length; i++) {
      tp	= data[i].getAttribute("type");
      id	= data[i].getAttribute("id");
      txt	= data[i].getAttribute("text");
      if( !tp || !id || !txt ) {
        continue;
      }
      switch(tp){
        case 'link':
        st = base.append_link($(txt).attr('href'));
        break;
        case 'file':
        st = base.append_link($(txt).attr('href')).then(function(file){
          file.type                               = 'file';
          return file;
        });
        break;
        case 'videoembed':
        st = base.append_video($(txt));
        break;
        case 'image':
        st = base.append_image($(txt));
        break;
      }
      base.upload_edit(id,tp,txt,st);
    }
  });
};

model.prototype.upload_edit                       = function(id,tp,txt,st){
  return st.then(function(obj){
    obj.set('VAR_STATE', '<b class="b-file__done">OK</b>');
    obj.set('VAR_STATUS', true);
    obj.id                                        = id;
    pf_data["at_"+tp]	                            = [id, txt];
    pf_data.attachments++;
    return obj;
  });
};
