/* Данные карты */
const mapInfo = JSON.parse(document.getElementById("main").getAttribute("data-map"));
const types = JSON.parse(document.getElementById("main").getAttribute("data-types"));

const 	mapSize = parseInt(mapInfo.size),
		mapID = parseInt(mapInfo.id),
		mapCode = mapInfo.code,
		currentlocurl = `http://phasmomap.online/${mapInfo.code}/`;
	
/* Формирование ссылки страницы */
let = url = '/map/' + mapID;
if (parseInt(document.getElementById("main").getAttribute("data-marker-id")) >= 0) url += '/id/' + document.getElementById("main").getAttribute("data-marker-id");
if (parseInt(document.getElementById("main").getAttribute("data-marker-x")) >= 0) url += '/x/' + document.getElementById("main").getAttribute("data-marker-x");
if (parseInt(document.getElementById("main").getAttribute("data-marker-y")) >= 0) url += '/y/' + document.getElementById("main").getAttribute("data-marker-y");
if (parseInt(document.getElementById("main").getAttribute("data-marker-type")) >= 0) url += '/type/' + document.getElementById("main").getAttribute("data-marker-type");
top.history.pushState(null, null, url);

/* Подгружаем файлы локализации */
const lang = document.getElementById("main").getAttribute("data-lang");

/* Инициализация Leaflet */
const 	maxScreenDimension = window.innerHeight > window.innerWidth ? window.innerWidth : window.innerHeight,
		tileSize = 256,
		maxTiles = Math.floor(maxScreenDimension / tileSize);
let     minZoom = Math.ceil(Math.log(maxTiles) / Math.log(2)),
        mapExtent = [0.00000000, -1.00000000 * mapSize, 1.00000000 * mapSize, 0.00000000],
        mapMinZoom = 2, 
        mapMaxZoom = 4;
const 	maxNativeZoom = mapMaxZoom,
		mapMaxResolution = 1.00000000,
		mapMinResolution = Math.pow(2, mapMaxZoom) * mapMaxResolution,
		tileExtent = [0.00000000, -1.00000000 * mapSize, 1.00000000 * mapSize, 0.00000000],
        southWest = L.latLng(1.00000000 * mapSize, 1.00000000 * 0),
        northEast = L.latLng(1.00000000 * 0, 1.00000000 * mapSize),
        bounds = L.latLngBounds(southWest, northEast);
let     crs = L.CRS.Simple;
crs.transformation = new L.Transformation(1, -tileExtent[0], -1, tileExtent[3]);
crs.scale = function(zoom) { return Math.pow(2, zoom) / mapMinResolution; };
crs.zoom = function(scale) { return Math.log(scale * mapMinResolution) / Math.LN2; };

/* Отрисовка карты */
const map = new L.Map('map', {
    maxZoom: mapMaxZoom,
    minZoom: mapMinZoom,
    crs: crs,
    bounds: mapExtent,
    zoomControl: false
});
L.control.zoom({ position: 'bottomleft' }).addTo(map);
map.doubleClickZoom.disable(); 
const layer = L.tileLayer(`http://phasmomap.online/tools/tiles/${mapCode}/{z}/{x}/{y}`, {
    minZoom: mapMinZoom, maxZoom: mapMaxZoom,
    attribution: 'Support us on <a href="https://boosty.to/iamnotacoder" target="_blank">Boosty.to</a>',
    noWrap: true,
    tms: false,
    reuseTiles : true,
    maxNativeZoom: maxNativeZoom
}).addTo(map);

map.fitBounds([
    crs.unproject(L.point(mapExtent[2], mapExtent[3])),
    crs.unproject(L.point(mapExtent[0], mapExtent[1]))
]);

map.setMaxBounds([
    crs.unproject(L.point(2.00000000 * mapSize, -2.00000000 * mapSize)),
    crs.unproject(L.point(-1.00000000 * mapSize, 1.00000000 * mapSize))
]);

map.on('click',function(e){
    top.history.pushState(null, null, `/${mapCode}/`);
});

/* Отображение маркеров на карте */
var overlay = {};

types.forEach((type) => {
    let display = false;
    const _type = L.geoJson(null, {
        pointToLayer: function(feature, latlng) {
            if (type.type == '0') {
                const marker = new L.marker(L.latLng(latlng.lat, latlng.lng), {
                    opacity: 0
                }); 

                let title = JSON.parse(feature.properties.title.replaceAll("'", '"'));
                if (lang == "ru") {
                    title = title.ru;
                } else {
                    title = title.en;
                }

                marker.bindTooltip(title, {
                    permanent: true,
                    className: "my-label",
                    direction: "center",
                    offset: [0, 0],
                    opacity: ((feature.properties.deprecated != undefined) ? 0.5 : 1)
                });

                if (feature.properties.id == document.getElementById("main").getAttribute("data-marker-id")) {
                    display = feature.properties.id;
                }

                marker.bindPopup(`<table id="markerInfoPopup"> <tr> <td id="markerInfo" ></td> <td id="markerComments" > </td> </tr> </table>`);

                return marker;
            } else if (type.type == '1') {
                const marker = new L.marker(L.latLng(latlng.lat, latlng.lng), {
                    opacity: feature.properties.deprecated != undefined ? 0.5 : 1
                });

                const icn = L.icon({
                    iconUrl: `/tools/images/types/${type.name}.png`,
                    iconSize: [48, 48],
                });
                const icn2 = L.icon({
                    iconUrl: `/tools/images/types/${type.name}_h.png`,
                    iconSize: [48, 48],
                });

                if (feature.properties.id == document.getElementById("main").getAttribute("data-marker-id")) {
                    display = feature.properties.id;
                    marker.setIcon(icn2);
                } else {
                    marker.setIcon(icn);
                }

                marker.on('click', function (e) {
                    marker.setIcon(icn2);
                });
        
                map.on('click', function (e) {
                    marker.setIcon(icn);
                });

                marker.bindPopup(`<table id="markerInfoPopup"> <tr> <td id="markerInfo" ></td> <td id="markerComments" > </td> </tr> </table>`);

                return marker;
            } else if (type.type == '2') {
                const polyline = L.polyline([[-1*(mapSize-feature.properties.start[0]), feature.properties.start[1]], [-1 * (mapSize-feature.properties.end[0]), feature.properties.end[1]]], {
                    color: "#FFFFFF",
                    fillColor: "#FFFFFF",
                });

                if (feature.properties.id == document.getElementById("main").getAttribute("data-marker-id")) {
                    display = feature.properties.id;
                }

                polyline.bindPopup(`<table id="markerInfoPopup"> <tr> <td id="markerInfo" ></td> <td id="markerComments" > </td> </tr> </table>`);

                return polyline;
            } else if (type.type == '3') {
                const circle = L.circle(L.latLng(latlng.lat, latlng.lng), {
                    color: "#FFFFFF",
                    fillColor: "#FFFFFF",
                    fillOpacity: 0.1,
                    radius: parseInt(mapSize * 0.05)
                });

                circle.bindPopup(`<table id="markerInfoPopup"> <tr> <td id="markerInfo" ></td> <td id="markerComments" > </td> </tr> </table>`);

                return circle;
            }
        }
    });
    $.getJSON(`http://phasmomap.online/tools/geoJSON/${mapID}/${type.id}.json`, function(data) {
        if (data.length != 0) {
            _type.addData(data);
            if (type.type == '0') _type.addTo(map);
            if (display != false) {
                _type.eachLayer(function (layer) {
                    if (layer.feature.properties.id == display) {
                        setTimeout(() => { 
                            map.setView(L.latLng(layer.getLatLng().lat, layer.getLatLng().lng), mapMinZoom + 1);
                        }, 1000);
                        displayMarkerInfo(layer.feature.properties.id);
                        layer.openPopup();
                    }
                });
            }

            _type.on("click", function (event) {
                displayMarkerInfo(event.layer.feature.properties.id);
            });

            let title = JSON.parse(type.title.replaceAll("'", '"'));
            title = lang == "ru" ? title.ru : title.en;

            overlay[`<img src='/tools/images/types/${type.name}.png' align='center' width='30' height='30'/> ${title}`] = _type;
            updateOverlay();
        }
    });
});

let over;
function updateOverlay() {
    if (over != null) document.getElementById('menuInfo').innerHTML = '';
    over = L.control.layers({}, overlay, {
        collapsed: false
    });
    
    over._map = map;
    document.getElementById('menuInfo').appendChild(over.onAdd(map));
}

/* Превью маркера */

let markX = parseInt(document.getElementById("main").getAttribute("data-marker-x")),
    markY = parseInt(document.getElementById("main").getAttribute("data-marker-y")),
    markType = parseInt(document.getElementById("main").getAttribute("data-marker-type"));
if (markX >= 0 && markY >= 0) {
    let marker_icn = L.icon({
        iconUrl: `/tools/images/types/default.png`,
        iconSize: [48, 48], 
    });
    
    if (markType >= 0 && markType < types.length) {
        marker_icn = L.icon({
            iconUrl: `/tools/images/types/${types[markType].name}.png`,
            iconSize: [48, 48], 
        });
    }

    L.marker(L.latLng(markY, markX), {
        icon: marker_icn
    }).addTo(map);

    map.setView(L.latLng(markY, markX), 4);
}

// Sound Sensor
const 	soundSensor1 = L.circle([-mapSize/2, mapSize/2], {radius: 271, color: "#fff", fillOpacity: 0.1}),
        soundSensor2 = L.circle([-mapSize/2, mapSize/2], {radius: 271, color: "#fff", fillOpacity: 0.1}),
        soundSensor3 = L.circle([-mapSize/2, mapSize/2], {radius: 271, color: "#fff", fillOpacity: 0.1}),
        soundSensor4 = L.circle([-mapSize/2, mapSize/2], {radius: 271, color: "#fff", fillOpacity: 0.1});

$('input[name=soundSensor1]').change(function() {
    if ($(this).is(':checked')) {
        map.addLayer(soundSensor1);
    } else {
        map.removeLayer(soundSensor1);
    }
});

$('input[name=soundSensor2]').change(function() {
    if ($(this).is(':checked')) {
        map.addLayer(soundSensor2);
    } else {
        map.removeLayer(soundSensor2);
    }
});

$('input[name=soundSensor3]').change(function() {
    if ($(this).is(':checked')) {
        map.addLayer(soundSensor3);
    } else {
        map.removeLayer(soundSensor3);
    }
});

$('input[name=soundSensor4]').change(function() {
    if ($(this).is(':checked')) {
        map.addLayer(soundSensor4);
    } else {
        map.removeLayer(soundSensor4);
    }
});

soundSensor1.on({
    mousedown: function () {
        map.on('mousemove', function (e) {
            map.dragging.disable();
            soundSensor1.setLatLng(e.latlng);
        });
    }
});

soundSensor2.on({
    mousedown: function () {
        map.on('mousemove', function (e) {
            map.dragging.disable();
            soundSensor2.setLatLng(e.latlng);
        });
    }
});

soundSensor3.on({
    mousedown: function () {
        map.on('mousemove', function (e) {
            map.dragging.disable();
            soundSensor3.setLatLng(e.latlng);
        });
    }
});

soundSensor4.on({
    mousedown: function () {
        map.on('mousemove', function (e) {
            map.dragging.disable();
            soundSensor4.setLatLng(e.latlng);
        });
    }
});

map.on('mouseup',function(e){
    map.removeEventListener('mousemove');
    map.dragging.enable()
})









/* Модуль рисования */
const editableLayers = new L.FeatureGroup();
map.addLayer(editableLayers);

const drawPluginOptions = {
  position: 'bottomleft',
  draw: {
    polyline: {
      shapeOptions: {
        color: '#FF6868',
        weight: 10
      }
    },
    polygon: {
      allowIntersection: true, 
      drawError: {
        color: '#e1e100', 
        message: '<strong>'+strings.drawing_poligon_error[lang]+'<strong>' 
      },
      shapeOptions: {
        color: '#68BBFF'
      }
    },
    circle: true, 
    rectangle: {
      shapeOptions: {
        clickable: true
      }
    }
  },
  edit: {
        featureGroup: editableLayers,
        remove: true
    }
};
const drawControl = new L.Control.Draw(drawPluginOptions);

$('input[name=checkDrawing]').change(function() {
    if ($(this).is(':checked')) {
        map.addControl(drawControl);
        map.addLayer(editableLayers);
    } else {
        map.removeControl(drawControl);
        map.removeLayer(editableLayers);
    }
});

function displayMarkerInfo(markerID) {
    document.cookie = `marker=${markerID};path=/map/${mapID}`; 
    top.history.pushState(null, null, `/${mapCode}/id/${markerID}`);
	
    const request = new XMLHttpRequest();
    request.open('GET', `/tools/ajax/marker.php?id=${markerID}&map=${mapID}`, true);
    request.addEventListener('readystatechange', function() {
        if ((request.readyState == 4) && (request.status == 200)) {
            function innerHTML2(res) {
                setTimeout(() => { 
                    const frame = document.getElementById('markerInfo');
                    if (frame != undefined) {
                        frame.innerHTML = request.responseText;
                    } else {
                        innerHTML2(res);
                    }
                }, 1000);
            }
            innerHTML2(request.responseText);
        }
    });
    request.send();

    const request2 = new XMLHttpRequest();
    request2.open('GET', `/tools/ajax/commentscontainer.php?id=${markerID}&map=${mapID}`, true);
    
    request2.addEventListener('readystatechange', function() {
        if ((request2.readyState == 4) && (request2.status == 200)) {
            function innerHTML(res) {
                setTimeout(() => { 
                    const frame = document.getElementById('markerComments');
                    if (frame != undefined) {
                        frame.innerHTML = request2.responseText;
                        getresult(`/tools/ajax/comments.php?id=${markerID}`);
                    } else {
                        innerHTML(res);
                    }
                }, 1000);
            }
            innerHTML(request2.responseText);
        }
    });
    request2.send();
}

map.on('popupopen', function(e) {
    var px = map.project(e.target._popup._latlng); 
    px.y -= e.target._popup._container.clientHeight*9/10; 
    map.panTo(map.unproject(px),{animate: true});
});

map.on('zoomend', function() {
    document.cookie = "zoom="+map.getZoom()+";path=/map/"+mapID; 
    document.cookie = "lat="+(map.getBounds().getCenter().lat)+";path=/map/"+mapID;  
    document.cookie = "lng="+map.getBounds().getCenter().lng+";path=/map/"+mapID;  
});

map.addEventListener('mousemove', function(ev) {
    document.cookie = "lat="+(map.getBounds().getCenter().lat)+";path=/map/"+mapID; 
    document.cookie = "lng="+map.getBounds().getCenter().lng+";path=/map/"+mapID; 
    document.cookie = "zoom="+map.getZoom()+";path=/map/"+mapID; 
});

if (getcookie("zoom") != 0 && getcookie("lat") != 0 && getcookie("lng") != 0 && getcookie("zoom") != undefined && getcookie("lat") != undefined && getcookie("lng") != undefined) {
    map.setView(L.latLng(getcookie("lat"), getcookie("lng")), getcookie("zoom"));
}

/* Утилиты */
// Копирование в буффер
function appCopyToClipBoard(sText) {
    var oText = false,
        bResult = false;
    try
    {
        oText = document.createElement("textarea");
        $(oText).addClass('clipboardCopier').val(sText).insertAfter('body').focus();
        oText.select();
        document.execCommand("Copy");
        bResult = true;
    }
    catch(e) {
    }

    $(oText).remove();
    if (bResult) {
        alert(strings.js_clipboard[lang]);
    }
    return bResult;
}
// Куки файлы
function getcookie(name = '') {
    let cookies = document.cookie;
    let cookiestore = {};
    
    cookies = cookies.split(";");
    
    if (cookies[0] == "" && cookies[0][0] == undefined) {
        return 0;
    }
    
    cookies.forEach(function(cookie) {
        cookie = cookie.split(/=(.+)/);
        if (cookie[0].substr(0, 1) == ' ') {
            cookie[0] = cookie[0].substr(1);
        }
        cookiestore[cookie[0]] = cookie[1];
    });
    
    return (name !== '' ? cookiestore[name] : cookiestore);
}
// Ajax
function getresult(url) {
    $.ajax({
        url: url,
        type: 'GET',
        data: {
            rowcount: $('#rowcount').val(),
            'pagination_setting': 'prev-next'
        },
        beforeSend: function() {
            $('#overlay').show();
        },
        success: function(data) {
            loadComments(data); 
            function loadComments(data) {
                let frame = document.getElementById('pagination-result');
                if (frame != undefined) {
                    frame.innerHTML = data;
                    setInterval(function() {
                        $('#overlay').hide();
                    }, 500);
                } else {
                    setInterval(loadComments(data), 100000);
                }
            }
        },
        error: function() {}
    });
}

/* Создание меток */
 
map.on('dblclick', (e) => {
    L.popup({closeButton:true}).setLatLng(e.latlng)
        .setContent(`${strings.js_add[lang]} | <a href="/add.php?lat=${e.latlng.lat}&lng=${e.latlng.lng}&map=${mapID}" target="_blank">${strings.js_add_btn[lang]}</a>`)
        .openOn(map);
});