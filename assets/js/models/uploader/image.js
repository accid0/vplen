var base                                          = null;

model.prototype.vars                              = {
  VAR_EVENTS                                      : {
  },
  VAR_IMAGE                                       : null,
  VAR_ICON                                        : null,
  VAR_UPLOAD                                      : null,
  VAR_PROGRESS                                    : null,
  VAR_STATE                                       : null,
  VAR_STATUS                                      : null
};

model.prototype.init                              = function(){
  base                                            = this;
};

model.prototype.load                              = function(source_image){
  var image = (source_image instanceof jQuery ? source_image.attr('href') : source_image),
      name  = (source_image instanceof jQuery ? source_image.text() : source_image);
  base.icon                                       = Autoloader.config.app+ '../img/uploader/synfig_icon.png';
  base.icon                                       = '<div class="js-left b-file__left">'+
				'<img src="'+base.icon+'" width="32" height="32" style="margin: 2px 0 0 3px"/>'+
			'</div>';
  base.set('VAR_ICON', base.icon);
  FileAPI.Image.fromDataURL(image, {width:32,height:32,style:'margin: 2px 0 0 3px'}, function(evt){
    base.icon                                     = $('<div class="js-left b-file__left"></div>');
    base.icon.append(evt);
    base.set('VAR_ICON', base.icon);
  });
  base.set('VAR_IMAGE', image);
  base.size                                       = 1;
  base.id                                         = Math.round(Math.random()*1000000);
  base.type                                       = 'image'
  base.name                                       = name;
  base.size_kb                                    = '';
  base.post_id                                    = encodeURIComponent(pf_data.temp_id);
};

model.prototype.upload                            = function(){
  if(base.get('VAR_STATUS')){
    return;
  }
  var func                                        = function(status){
    if(status instanceof NodeList){
      status  = $('<div />').html(new XMLSerializer().serializeToString(status[0])).text();
    }
    base.set('VAR_STATE', '<b class="b-file__'+('OK' !== status ? 'error' : 'done')+'">'+status+'</b>');
  };
  base.set('VAR_UPLOAD', true);
  $.ajax({
    url             : Autoloader.config.base+'ajax/postform-attach/ajaxtp:xml/r:'+Math.round(Math.random()*1000),
    data            : {
      post_temp_id  : base.post_id,
      at_type       : base.type,
      data          : 'url|'+base.get('VAR_IMAGE')
    },
    dataType        : 'xml',
    type            : 'POST',
    complete        : function(xhr, status){
      base.set('VAR_PROGRESS', '100%');
      var state = (xhr.statusText) ? 'wait...' : 'done';
      base.set('VAR_STATUS', (('done' === state || 'wait...' === state.toLowerCase()) ? true : false));
      base.set('VAR_UPLOAD', state);
      base.set('VAR_STATE', '<b class="b-file__'+('error' === state ? 'error' : 'done')+'">'+state+'</b>');
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
				if( !data || !data[0] ) { setTimeout(base.upload, 1000); return; }
				data	= data[0].getAttribute("text");
				if( !data ) { func(status); return; }
				func(status);
			  pf_data.attachments	++;
		    pf_data["at_"+base.type]	= [-1, data];
      }
    }
  });
};

model.prototype.remove                              = function(){
  if(base.get('VAR_STATUS')){
    base.off();
    $.ajax({
      url             : Autoloader.config.base+'ajax/postform-attach/ajaxtp:xml/delete:true/r:'+Math.round(Math.random()*1000),
      data            : {   
        post_temp_id  : base.post_id,
        at_type       : base.type,
        data          : 'url|'+base.get('VAR_IMAGE'),
        id            : base.id
      },
      type            : 'POST'
    });
  }
};
