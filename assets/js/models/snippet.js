window.diff = function diff(a,b) {
    return a.filter(function(i) {return !(b.indexOf(i) > -1);});
};
