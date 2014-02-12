view.promise('selector').then(function(selector){
  var el = document.querySelector(selector);
  view.set('element.input', el.querySelectorAll('input[type="file"]'));
  view.set('element.preview', el.querySelector('.preview'));
  view.set('element.notify', el.querySelector('.oooops'));
  view.set('element.video', el.querySelector('input[name="atch_videoembed"]'));
  view.set('element.video.submit', el.querySelector('.video-submit'));
  view.set('element.link', el.querySelector('input[name="atch_link"]'));
  view.set('element.link.submit', el.querySelector('.link-submit'));
  view.set('element.image', el.querySelector('input[name="atch_image_url"]'));
  view.set('element.image.submit', el.querySelector('.image-submit'));
  view.set('element.post', el.querySelector('#postbtn'));
});

view.set('hide', true);

view.asEventStream('files').then(function(files){
  if(0 == files.length){
    view.set('hide', true);
    view.promise('element.preview').then(function(el){
      $(el).animate({ height: 0 }, 400, function (){
        if(!view.get('hide')) return;
        $(this).hide().css('height', '');
      });
    });
    return;
  }
  else if(view.get('hide')){
    view.set('hide', false);
    view.promise('element.preview').then(function(el){
      $(el).stop().show().css({
          'display' : 'block',
          'height'  : ''
        });
    });
  }
  var $Queue = $('<div/>'), icon = view.get('icon'), max_size = view.get('max_size');
  $.each(files, function (id, file){
		if( file.size >= max_size ){
			alert('Sorrow.\nMax size 25MB')
		}
		else if( file.size === void 0 ){
			$(view.get('element.notify')).show();
		}
		else {
      var $preview = $('<div id="file-'+file.id+'" class="js-file b-file b-file_'+file.type+'">'+
		  '<div class="js-left b-file__left"></div>'+
			'<div class="b-file__right">'+
				'<div><a class="js-name b-file__name">'+file.name+'</a></div>'+
				'<div class="js-info b-file__info">size: '+file.size_kb+' KB</div>'+
				'<div class="js-progress b-file__bar" style="display: none">'+
					'<div class="b-progress"><div class="js-bar b-progress__bar"></div></div>'+
				'</div>'+
			'</div>'+
			'<i class="js-abort b-file__abort" title="abort">&times;</i>'+
		  '</div>');
      
      $preview.on('click', '.js-abort', function(event){
        event.preventDefault();
        view.set('remove', id);
      });
      file.promise('VAR_ICON', view).then(function(icon){
        $preview.children('.js-left').replaceWith(icon);
      });
      file.asEventStream('VAR_UPLOAD', view).then(function(state){
        if(true === state){
          $preview.addClass('b-file_upload').find('.js-progress')
                  .css({ opacity: 0 }).show().animate({ opacity: 1 }, 100);
        }
        else{
          $preview.removeClass('b-file_upload').find('.js-progress')
                  .animate({ opacity: 0 }, 200, function (){ $(this).hide().find('.js-bar').css('width','0%')});
        }
      });
      file.asEventStream('VAR_PROGRESS', view).then(function(width){
        $preview.find('.js-bar').css('width', width);
      });
      file.asPromiseStream('VAR_STATE', view).then(function(state){
        $preview.find('.js-info').html(state);
      });
      $Queue.append($preview);
		}
	});
  $(view.get('element.preview')).html($Queue);
}).fail(function(reasone){
  console.log(reasone);
});

view.promise('element.video.submit').then(function(el){
  $(el).on('click', function(event){
    event.preventDefault();
    view.set('submit.video', view.get('element.video').value);
  });
});

view.promise('element.link.submit').then(function(el){
  $(el).on('click', function(event){
    event.preventDefault();
    view.set('submit.link', view.get('element.link').value);
  });
});

view.promise('element.image.submit').then(function(el){
  $(el).on('click', function(event){
    event.preventDefault();
    view.set('submit.image', view.get('element.image').value);
  });
});

view.promise('element.post').then(function(el){
  $(el).on('click', function(event){
    view.set('submit.post', true);
  });
});
