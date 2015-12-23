// create the 2 icons
var dep_icon = new GIcon();
dep_icon.image = "http://fgfs.i-net.hu/departure.gif";
dep_icon.iconSize = new GSize(20, 20);
dep_icon.iconAnchor = new GPoint(9, 20);
dep_icon.infoWindowAnchor = new GPoint(15, 1);

var arr_icon = new GIcon();
arr_icon.image = "http://fgfs.i-net.hu/arrival.gif";
arr_icon.iconSize = new GSize(20, 20);
arr_icon.iconAnchor = new GPoint(9, 20);
arr_icon.infoWindowAnchor = new GPoint(15, 1);

// show the icons
var point_dep = new GLatLng(lat,lon);
var point_arr = new GLatLng(lat,lon);
map.addOverlay(new GMarker(point_dep, dep_icon));
map.addOverlay(new GMarker(point_arr, arr_icon));
