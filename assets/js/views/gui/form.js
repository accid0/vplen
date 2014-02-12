var $search_feed = $('#sttl');
var text_promt = $search_feed.text().replace(/\:.*$/g, '');
view.on('VAR_INPUT_LOOKFOR', function(result){
  result.then(function(query){
    $search_feed.text(text_promt + ': ' + query);
  });
});
