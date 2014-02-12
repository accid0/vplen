view.promise('selector').then(function(selector){
  var el = document.querySelector(selector);
  view.set('element.textarea', el.querySelector('textarea'));
  view.set('element.smileys', el.querySelectorAll('.post-smile'));
});

view.asVipStream('element.smileys').then(function(el){
  view.asVipStream('element.textarea').then(function(area){
    el.onclick                  = function(event){
      event.preventDefault();
      //IE support
      if (document.selection) {
        area.focus();
        sel                     = document.selection.createRange();
        sel.text = el.alt;
      }
      //MOZILLA and others
      else if (area.selectionStart || area.selectionStart == '0') {
        var startPos            = area.selectionStart;
        var endPos              = area.selectionEnd;
        area.value = area.value.substring(0, startPos)
          + el.alt
          + area.value.substring(endPos, area.value.length);
      } else {
        area.value += myValue;
      }
      area.focus();
      area.selectionStart = area.selectionEnd + 2;
    };
  });
});
