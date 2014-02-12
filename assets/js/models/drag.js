var base                                      = null;

model.prototype.vars                          = {
  VAR_EVENTS                                  : {}
};

model.prototype.init                          = function(){
  base                                        = this;
  include_once([Autoloader.config.app + 'drag/DragObject.js', 
      Autoloader.config.app + 'drag/dragMaster.js',
      Autoloader.config.app + 'drag/helpers.js'], function(done){
        base.load();
      });
};

model.prototype.load                          = function(){
  if('undefined' === typeof dragMaster){
    setTimeout(base.load, 13);
    return;
  }

  var obj = document.getElementById('flybox_box');
  if(null === obj) return;
  obj     = new DragObject(obj);
  obj.onDragFail = function(){};
};
