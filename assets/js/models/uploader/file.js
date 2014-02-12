var base                                    = null;

model.prototype.vars                        = {
  VAR_EVENTS                                : {
  },
  VAR_FILE                                  : null,
  VAR_ICON                                  : null,
  VAR_UPLOAD                                : null,
  VAR_PROGRESS                              : null,
  VAR_STATE                                 : null,
  VAR_STATUS                                : null,
  VAR_ALLOWED_MIME                          : [
    'application/x-bittorrent',
    'application/msword',
    'text/plain',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel',
    'video/x-flv',
    'video/x-ms-wmv',
    'video/webm',
    'video/quicktime',
    'video/ogg',
    'video/mp4',
    'video/mpeg',
    'image/vnd.wap.wbmp',
    'image/vnd.microsoft.icon',
    'image/tiff',
    'image/svg+xml',
    'image/png',
    'image/pjpeg',
    'image/jpeg',
    'image/gif',
    'audio/webm',
    'audio/vnd.wave',
    'audio/vnd.rn-realaudio',
    'audio/x-ms-wax',
    'audio/x-ms-wma',
    'audio/vorbis',
    'audio/ogg',
    'audio/mpeg',
    'audio/mp4',
    'audio/L24',
    'audio/basic',
    'application/pdf',
    'application/zip',
    'application/x-gzip'
  ]
};

model.prototype.init                        = function(){
  base                                      = this;
};

model.prototype.load                        = function(file){
  var icon                                  = {
			def:   Autoloader.config.app+ '../img/uploader/unknown.png'
		, image: Autoloader.config.app+ '../img/uploader/synfig_icon.png'
		, audio: Autoloader.config.app+ '../img/uploader/Music.png'
		, video: Autoloader.config.app+ '../img/uploader/Video.png'
	};
  base.set('VAR_FILE', file);
  base.size                                 = file.size;
  base.id                                   = FileAPI.uid(file);
  if(-1 === base.get('VAR_ALLOWED_MIME').indexOf(file.type)){
    base.set('VAR_STATUS', true);
    base.set('VAR_STATE', '<b class="b-file__error">Restrict Type</b>');
    base.off();
    return;
  }
  base.type                                 = (file.type.split('/')[0]||'file');
  if( /^image/.test(base.type) ){
		FileAPI.Image(file).preview(35).rotate('auto').get(function (err, img){
			if( !err ){
        base.icon                           = $('<div class="js-left b-file__left file__left_border"></div>').append(img);
        base.set('VAR_ICON', base.icon);
			}
		});
	}
  else{
    base.icon                               = ''+(icon[base.type]||icon.def);
    base.icon                               = '<div class="js-left b-file__left">'+
				'<img src="'+base.icon+'" width="32" height="32" style="margin: 2px 0 0 3px"/>'+
			'</div>';
    base.set('VAR_ICON', base.icon);
    base.type                               = 'file';
  }
  base.name                                 = file.name;
  base.size_kb                              = (file.size/FileAPI.KB).toFixed(2);
  base.post_id                              = encodeURIComponent(pf_data.temp_id);
};

model.prototype.upload                      = function(){
  var file                                  = base.get('VAR_FILE');
  if(base.get('VAR_STATUS')){
    return;
  }
  file.xhr = FileAPI.upload({
		url: Autoloader.config.base+'ajax/postform-attachupl',
		files: { file: file },
    data: {keyy: FileAPI.uid(file)},
		upload: function (){
      base.set('VAR_UPLOAD', true);
		},
		progress: function (evt){
      base.set('VAR_PROGRESS', evt.loaded/evt.total*100+'%');
		},
		complete: function (err, xhr){
			var state = (xhr.statusText || err) ? (err || 'wait...') : 'done';
      base.set('VAR_STATUS', (('done' === state || 'wait...' === state.toLowerCase()) ? true : false));
      base.set('VAR_UPLOAD', state);
      base.set('VAR_STATE', '<b class="b-file__'+('error' === state ? 'error' : 'done')+'">'+state+'</b>');
      base.check();
		}
	});
};

model.prototype.check                         = function(){
  var func                                    = function(status){
    if(status instanceof NodeList){
      status     = $('<div />').html(new XMLSerializer().serializeToString(status[0])).text();
    }
    base.set('VAR_STATE', '<b class="b-file__'+('OK' !== status ? 'error' : 'done')+'">'+status+'</b>');
  };
  $.ajax({
    url             : Autoloader.config.base+'ajax/postform-attach/ajaxtp:xml/r:'+Math.round(Math.random()*1000),
    data            : {
      post_temp_id  : base.post_id,
      at_type       : ('image' === base.type ? 'image' : 'file'),
      data          : ('image' === base.type ? 'upl|' : '')+base.id
    },
    dataType        : 'xml',
    type            : 'POST',
    complete        : function(xhr, status){
      if(4 === xhr.readyState && "success" === status){
        var xml     = xhr.responseXML;
        var data	  = xml.getElementsByTagName("result");
				if( !data || !data[0] ) { func('error'); return; }
				data	      = data[0];
        var message	= data.getElementsByTagName("message");
				status	    = data.getElementsByTagName("status");
				if( !status || !status[0] ) { func('error'); return; }
				status	    = status[0].firstChild.nodeValue;
				if( status != "OK" ) {
					if( message && message[0] ) {
						status  = message;
					}
				}
				data	= data.getElementsByTagName("attach");
				if( !data || !data[0] ) { func(status); return; }
				data	= data[0].getAttribute("text");
				if( !data ) { func(status); return; }
				func(status);
			  pf_data.attachments	++;
		    pf_data["at_"+base.type]	= [-1, data];
      }
    }
  });
};

model.prototype.remove                        = function(){
  if(base.get('VAR_STATUS')){
    base.off();
    $.ajax({
      url             : Autoloader.config.base+'ajax/postform-attach/ajaxtp:xml/delete:true/r:'+Math.round(Math.random()*1000),
      data            : {   
        post_temp_id  : base.post_id,
        at_type       : ('image' === base.type ? 'image' : 'file'),
        data          : ('image' === base.type ? 'upl|' : '')+base.id,
        id            : base.id
      },
      type            : 'POST'
    });
  }
};
