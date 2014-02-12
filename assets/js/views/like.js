var data              = {};

view.promise('init-likes').then(function(event){
  var wrap            = document.querySelector(event.wrap);
  if(wrap){
    //view.asRequestStream(wrap).then(function(id){
      view.set('selector', event.selector);
    //}).fail(function(reasone){
      //console.log(reasone);
    //});
  }
});

promise view sync then{
  view.trigger('selector');
}

view.asPromiseStream('selector').then(function(selector){
  var el              = Array.prototype.slice.call(document.querySelectorAll(selector)),
      result          = [],
      init            = [],
      temp            = null,
      node            = null;
  for(var i=0, l=el.length; i<l; ++i){
    node              = el[i].querySelector('.like-group .like-button');
    temp              = {
      like            : node,
      'feed-like'     : (node = node.nextElementSibling),
      mlike           : (node = node.nextElementSibling),
      'feed-mlike'    : (node = node.nextElementSibling),
      dislike         : (node = node.nextElementSibling),
      'feed-dislike'  : (node = node.nextElementSibling),
      id              : jQuery(node).closest('.post').attr('id').replace(/^post_(?:public|private)_/i, '')
    };
    result[i]         = temp;
    init[i]           = temp.id;
    data[temp.id]     = temp;
  }
  view.set('likes', result);
  view.set('init', init);
});

view.asVipStream('likes').then(function(list){
  view.set('like-button', [list.like, list.id]);
  view.set('mlike-button', [list.mlike, list.id]);
  view.set('dislike-button', [list.dislike, list.id]);
});

view.asEventStream('like-button').then(function(like){
  like[0].onclick                 = function(event){
    event.preventDefault();
    view.set('onlike', like[1]);
  };
});

view.asEventStream('mlike-button').then(function(mlike){
  mlike[0].onclick                = function(event){
    event.preventDefault();
    view.set('onmlike', mlike[1]);
  };
});

view.asEventStream('dislike-button').then(function(dislike){
  dislike[0].onclick              = function(event){
    event.preventDefault();
    view.set('ondislike', dislike[1]);
  };
});

view.asEventStream('refresh-like').then(function(post){
  var el                          = data[post.get('VAR_ID')];
  el['feed-like'].innerHTML       = post.get('VAR_LIKE');
});

view.asEventStream('refresh-mlike').then(function(post){
  var el                          = data[post.get('VAR_ID')];
  el['feed-mlike'].innerHTML      = post.get('VAR_MLIKE');
});

view.asEventStream('refresh-dislike').then(function(post){
  var el                          = data[post.get('VAR_ID')];
  el['feed-dislike'].innerHTML    = post.get('VAR_DISLIKE');
});

view.asEventStream('refresh-state').then(function(post){
  var el                          = data[post.get('VAR_ID')],
      state                       = post.get('VAR_LIKE_STATUS');
  el.like.className               = 'like-state like-button';
  el.mlike.className              = 'like-state mlike-button';
  el.dislike.className            = 'like-state dislike-button';
  if(null !== state){
    el[state].className           = 'like-state checked ' +state+ '-button';
  }
});
