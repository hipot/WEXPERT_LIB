/**
 * расчет расстояния от МКАД до строкового адреса
 * @version 2.0
 * @author wexpert, 2013
 */


/**
 * получить адрес для поиска по частям
 *
 * @param Array addressParams адрес по-частям
 * @return string полный адрес
 */
var getAdressByParts = function (addressParams) {
	var	regionTxt	= addressParams['regionTxt']	? addressParams['regionTxt'] : '',
		ulizTxt		= addressParams['ulizTxt']		? addressParams['ulizTxt'] : '',
		houseTxt	= addressParams['houseTxt']		? addressParams['houseTxt'] : '',
		korpusTxt	= addressParams['korpusTxt']	? addressParams['korpusTxt'] : '';

	// складываем адресс для поиска на я.карте
	var addresToSearchTxt = '';

	if ($.trim(regionTxt) != '') {
		addresToSearchTxt += regionTxt;
	}
	if ($.trim(ulizTxt) != '') {
		addresToSearchTxt += ' ' + ulizTxt;
	}
	if ($.trim(houseTxt) != '') {
		addresToSearchTxt += ' д. ' + houseTxt;
	}
	if ($.trim(korpusTxt) != '') {
		addresToSearchTxt += ' корп.' + korpusTxt;
	}
	return addresToSearchTxt;
};

var getWay = function(addr, callback, mapSelector) {

	// съезды с кольца МКАД
	var mkad_sezds = [
		[0, 37.589946, 55.910009],
		[0, 37.674403, 55.894193],
		[0, 37.725901, 55.882134],
		[0, 37.839266, 55.813902],
		[0, 37.842699, 55.777348],
		[0, 37.842013, 55.744245],
		[0, 37.835490, 55.708013],
		[0, 37.830683, 55.687266],
		[0, 37.839266, 55.657386],
		[0, 37.819353, 55.640690],
		[0, 37.782961, 55.617770],
		[0, 37.728716, 55.592115],
		[0, 37.688204, 55.575780],
		[0, 37.596880, 55.576169],
		[0, 37.492235, 55.610990],
		[0, 37.492235, 55.610990],
		[0, 37.459619, 55.638963],
		[0, 37.432497, 55.662259],
		[0, 37.386148, 55.713654],
		[0, 37.386148, 55.713654],
		[0, 37.370012, 55.765047],
		[0, 37.372759, 55.789815],
		[0, 37.396105, 55.832541],
		[0, 37.445543, 55.881203],
		[0, 37.543734, 55.907632]
	];

	//Координаты каждого километра МКАД в массиве
	var mkad_km =  [
		[1, 37.842762, 55.774558],
		[2, 37.842789, 55.76522],
		[3,37.842627,55.755723],
		[4,37.841828,55.747399],
		[5,37.841217,55.739103],
		[6,37.840175,55.730482],
		[7,37.83916,55.721939],
		[8,37.837121,55.712203],
		[9,37.83262,55.703048],
		[10,37.829512,55.694287],
		[11,37.831353,55.68529],
		[12,37.834605,55.675945],
		[13,37.837597,55.667752],
		[14,37.839348,55.658667],
		[15,37.833842,55.650053],
		[16,37.824787,55.643713],
		[17,37.814564,55.637347],
		[18,37.802473,55.62913],
		[19,37.794235,55.623758],
		[20,37.781928,55.617713],
		[21,37.771139,55.611755],
		[22,37.758725,55.604956],
		[23,37.747945,55.599677],
		[24,37.734785,55.594143],
		[25,37.723062,55.589234],
		[26,37.709425,55.583983],
		[27,37.696256,55.578834],
		[28,37.683167,55.574019],
		[29,37.668911,55.571999],
		[30,37.647765,55.573093],
		[31,37.633419,55.573928],
		[32,37.616719,55.574732],
		[33,37.60107,55.575816],
		[34,37.586536,55.5778],
		[35,37.571938,55.581271],
		[36,37.555732,55.585143],
		[37,37.545132,55.587509],
		[38,37.526366,55.5922],
		[39,37.516108,55.594728],
		[40,37.502274,55.60249],
		[41,37.49391,55.609685],
		[42,37.484846,55.617424],
		[43,37.474668,55.625801],
		[44,37.469925,55.630207],
		[45,37.456864,55.641041],
		[46,37.448195,55.648794],
		[47,37.441125,55.654675],
		[48,37.434424,55.660424],
		[49,37.42598,55.670701],
		[50,37.418712,55.67994],
		[51,37.414868,55.686873],
		[52,37.407528,55.695697],
		[53,37.397952,55.702805],
		[54,37.388969,55.709657],
		[55,37.383283,55.718273],
		[56,37.378369,55.728581],
		[57,37.374991,55.735201],
		[58,37.370248,55.744789],
		[59,37.369188,55.75435],
		[60,37.369053,55.762936],
		[61,37.369619,55.771444],
		[62,37.369853,55.779722],
		[63,37.372943,55.789542],
		[64,37.379824,55.79723],
		[65,37.386876,55.805796],
		[66,37.390397,55.814629],
		[67,37.393236,55.823606],
		[68,37.395275,55.83251],
		[69,37.394709,55.840376],
		[70,37.393056,55.850141],
		[71,37.397314,55.858801],
		[72,37.405588,55.867051],
		[73,37.416601,55.872703],
		[74,37.429429,55.877041],
		[75,37.443596,55.881091],
		[76,37.459065,55.882828],
		[77,37.473096,55.884625],
		[78,37.48861,55.888897],
		[79,37.5016,55.894232],
		[80,37.513206,55.899578],
		[81,37.527597,55.90526],
		[82,37.543443,55.907687],
		[83,37.559577,55.909388],
		[84,37.575531,55.910907],
		[85,37.590344,55.909257],
		[86,37.604637,55.905472],
		[87,37.619603,55.901637],
		[88,37.635961,55.898533],
		[89,37.647648,55.896973],
		[90,37.667878,55.895449],
		[91,37.681721,55.894868],
		[92,37.698807,55.893884],
		[93,37.712363,55.889094],
		[94,37.723636,55.883555],
		[95,37.735791,55.877501],
		[96,37.741261,55.874698],
		[97,37.764519,55.862464],
		[98,37.765992,55.861979],
		[99,37.788216,55.850257],
		[100, 37.788522, 55.850383],
		[101, 37.800586, 55.844167],
		[102, 37.822819, 55.832707],
		[103, 37.829754, 55.828789],
		[104, 37.837148, 55.821072],
		[105, 37.838926, 55.811599],
		[106, 37.840004, 55.802781],
		[107, 37.840965, 55.793991],
		[108, 37.841576, 55.785017]
	];

	// расчитываем от съездов
	mkad_km = mkad_sezds;

	var init = function() {

		if (mapSelector=='undefined' || $('#'+mapSelector).length == 0) {
			$('body').append('<div id="myMap" style="width: 600px; height: 400px"></div>');
			mapSelector = 'myMap';
		}
		if (mapSelector != 'adminMap') {
			$('#'+mapSelector).css({position:'fixed', left:'-99999px'});
		} else {
			$('#'+mapSelector).css('height', '400px');
		}

		document.getElementById(mapSelector).innerHTML = '';

		map = new ymaps.Map (mapSelector, {
			center: [55.76, 37.64],
			zoom: 6
		});

		map.controls
			.add('zoomControl', { left: 5, top: 5 })
			.add('typeSelector');

		var geocoder = ymaps.geocode(addr, {boundedBy: map.getBounds(), strictBounds : true});

		geocoder.then(
				function (res) {
					if (res.geoObjects.getLength()) { // Если адрес определен
						var point = res.geoObjects.get(0);

						map.geoObjects.add(point);
						// Создаем полигон МКАД по заданным координатам mkad_km

						var mypoints = [];
						for(i = 0; i < mkad_km.length; i++) {
							mypoints[i] =  [mkad_km[i][2], mkad_km[i][1]];
						}

						// Создаем инстанцию геометрии многоугольника (указываем координаты вершин контуров).
						var polygonGeometry = new ymaps.geometry.Polygon([mypoints, mypoints, mypoints]);


						// Создаем инстанцию геообъекта и передаем нашу геометрию в конструктор.
						var polygonGeoObject = new ymaps.GeoObject({ geometry: polygonGeometry });

						// добавляем визульаный объект на карту
						map.geoObjects.add(polygonGeoObject);

						// Метод contains работает только с корректно заданной картой.
						polygonGeometry.options.setParent(map.options);
						polygonGeometry.setMap(map);

						//console.log(addr);

						if (polygonGeometry.contains( point.geometry.getCoordinates() )) {

							// Адрес внутри МКАД
							callback(0);
							return;
							//document.getElementById('text').innerHTML = "Дистанция от МКАДа: " + 0 + 'км';

						} else {

							// Адрес за МКАД
							// Расчет расстояния до адреса
							var from_km = polygonGeometry.getClosest( point.geometry.getCoordinates() ); // Ближайшая точка МКАД к адресу

							// либо текстуально, либо координатами, пока задаю координатами
							//var fromAdress =  mkad_km[from_km.closestPointIndex][0] + '-й километр, МКАД, Москва, Россия';

							var fromAdress = {
								type	: 'wayPoint',
								point	: [ mkad_km[from_km.closestPointIndex][2], mkad_km[from_km.closestPointIndex][1] ]
							};

							/*console.log(fromAdress);
							console.log(addr);*/

							var router = new ymaps.route([fromAdress, addr], {
								avoidTrafficJams: false,
								mapStateAutoApply: true
							}); // Строим маршрут от МКАД до адреса

							//Если путь найден
							router.then(function(route) {
									/* Задание контента меток в начальной и
									конечной точках */
									var points = route.getWayPoints();
									points.get(0).properties.set("iconContent", "А");
									points.get(1).properties.set("iconContent", "Б");
									// Добавление маршрута на карту
									map.geoObjects.add(route);

									var distance = Math.ceil(route.getLength() / 1000); // Получаем расстояние в км и округляем
									callback(distance);
									return;
									//document.getElementById('text').innerHTML = "Дистанция от МКАДа: "+distance+'км';
								},
								// Обработка ошибки
								function (error) {
									//alert("Возникла ошибка постоения маршрута: " + error.message);
									callback(false);
								}
							)

						}
					} else {

						// Адрес не удалось определить
						callback(false);

					}
				},
				// Обработка ошибки
				function (error) {
					callback(false);
				}
			)
	}

	ymaps.ready(init);
	var map;
};

callback = function(way) {
	if (way)
		$('#text').val(way);
};