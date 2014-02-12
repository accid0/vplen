var atts                                            = document.querySelectorAll('.flyboxattachment')
  , ds                                              = document.querySelectorAll('.flyboxdata')
  , next                                            = document.querySelector('.flybox-next')
  , prev                                            = document.querySelector('.flybox-prev')
  //, nextRule                                        = ( document.styleSheets[0].addRule ? (document.styleSheets[0].addRule('.flybox-next:after', 'width: 100%; height:100%; top:0;', 0), 
  //    document.styleSheets[0].rules[0]) : (document.styleSheets[0].insertRule('.flybox-next:after { width: 100%; height:100%; top:0; }', 0),
  //    document.styleSheets[0].cssRules[0]))
  , flybox                                          = window.parent.document.querySelector('.flybox')
  , iframe                                          = flybox.querySelector('.flyboxbody iframe')
  , len                                             = atts.length;
view.asPromiseStream('current').then(function(id){
  var width, height;
  if(0 === id) prev.disabled = true;
  else prev.disabled = false;
  if(id === len-1) next.disabled = true;
  else next.disabled = false;
  for(var i = 0; i < len; i++){
    if(id === i) atts[i].style.display              = 'block';
    else atts[i].style.display                      = 'none';
  }
  for(var i = 0; i < len; i++){
    if(id === i) ds[i].style.display                = 'block';
    else ds[i].style.display                        = 'none';
  }
  width                                             = parseInt(atts[id].children[0].style.width.replace(/px$/g, ''), 10);
  if(isNaN(width)) width                            = parseInt(atts[id].children[0].width, 10);
  atts[id].style.width                              = width + 'px';
  width                                             += 60;
  height                                            = atts[id].clientHeight + ds[id].clientHeight;
  iframe.style.width                                = width + 'px';
  iframe.style.height                               = height + 'px';
  next.style.setProperty("width", width + 'px', 'important');
  next.style.setProperty("height", height + 'px', 'important');
  //nextRule.style.setProperty("top", '-' + ((height - next.clientHeight) / 2) + 'px', 'important');
  var body                                          = window.parent.document.getElementsByTagName('body')[0];
  var left                                          = body.clientWidth - width;
  var top                                           = body.clientHeight - height;
  flybox.style.left                                 = (left > 0 ? left/2 : 0) + 'px';
  flybox.style.top                                  = (top > 0 ? top/2 : 0) + 'px';
});

next.onclick                                        = function(event){
  event.preventDefault();
  view.set('next', true);
};

prev.onclick                                        = function(event){
  event.preventDefault();
  view.set('prev', true);
};
