<?php
header ( 'Content-Type: text/html; charset=utf-8' );
date_default_timezone_set ( 'Europe/Berlin' );
include ('api1/config.php');
$err_level = error_reporting ( 0 );
$my = new mysqli ( $my_host, $my_user, $my_pass );
error_reporting ( $err_level );
if ($my->connect_error)
	die ( "Datenbankverbindung (MySQL) nicht möglich." );
$my->set_charset ( 'utf8' );
$my->select_db ( $my_name );

//$pgr = pg_connect ( $pgr_connectstr ) or die ( "Datenbankverbindung (PostgreSQL) nicht möglich." . pg_last_error () );

session_start();
?>
<!doctype html>
<html>
<head>
<title>ibis - Map View</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="shortcut icon" sizes="16x16 24x24 32x32 48x48 64x64 96x96 128x128" href="https://ibis.jufo.mytfg.de/favicon.ico">
<link href="fontawesome/css/font-awesome.min.css" rel="stylesheet">
<link rel="stylesheet"
	href="leaflet/leaflet.css" />
<link rel="stylesheet" href="leaflet-sidebar-v2/leaflet-sidebar.min.css" />
<link rel="stylesheet" href="leaflet-contextmenu/leaflet.contextmenu.css" />
<style>
	body {
		padding: 0;
		margin: 0;
	}
	html, body, #map {
		height: 100%;
		font: 10pt "Helvetica Neue", Helvetica, sans-serif;
	}
</style>
</head>
<body style="height: 100%;">
	<div id="sidebar" class="sidebar collapsed">
		<!-- Nav tab(s) -->
		<ul class="sidebar-tabs" role="tablist">
			<li><a href="#gettrack_pane" role="tab"><i class="fa fa-map-marker"></i></a></li>
			<li><a href="#routing_pane" role="tab"><i class="fa fa-location-arrow"></i></a></li>
			<li><a href="#showtopo_pane" role="tab"><i class="fa fa-cloud"></i></a></li>
			<li><a href="#admin_pane" role="tab"><i class="fa fa-cogs"></i></a></li>
			<li><a href="#profile_pane" role="tab"><i class="fa fa-area-chart"></i></a></li>
			<li><a href="#cleanmap_pane" role="tab"><i class="fa fa-eraser"></i></a></li>
		</ul>
		<!-- Tab pane(s) -->
		<div class="sidebar-content active">
			<div class="sidebar-pane" id="gettrack_pane">
				<h1>iBis Tracks Anzeigen</h1>
				<form id="show_track">
					<label for="track_select">Track(s) anzeigen</label>
					<br />
					<select id="track_select" multiple="multiple" size="25" style="overflow: hidden; width: 100%;">

					</select>
					<br />
					<input type="submit" value="Tracks Anzeigen">
				</form>
				<br />
				<form id="track_select_num_form">
					<label for="track_select_num">Track Auswahl:</label>
					<p id="track_select_num_p">Es sind unbekannt viele Tracks vorhanden</p>
					<select id="track_select_num">
						<option value="0">0..24</option>
					</select>
					<input type="submit" value="Wechseln">
				</form>
			</div>
			
			<div class="sidebar-pane" id="routing_pane">
				<h1>iBis Routing</h1>
				<p>Zum Auswählen des Start und Ziel-Punktes in der Karte mittels Rechts-Klick den Start- und Ziel-Punkt festlegen!</p>
				<form id="generate_route">
				 <table>
					<tr><td colspan="2"><p id="routing_start_p">Von</p></td></tr>
					<tr><td><label for="start_lat">Breite:</label></td><td><input type="text" name="start_lat" id="start_lat"></td></tr>
					<tr><td><label for="start_lon">Länge:</label></td><td><input type="text" name="start_lon" id="start_lon"></td></tr>
					<tr><td colspan="2"><p id="routing_end_p">Nach</p></td></tr>
					<tr><td><label for="end_lat">Breite:</label></td><td><input type="text" name="end_lat" id="end_lat"></td></tr>
					<tr><td><label for="end_lon">Länge:</label></td><td><input type="text" name="end_lon" id="end_lon"></td></tr>
					<tr><td><label for="route_profile_latlon">Profil:</label></td><td>
						<select id="route_profile_latlon">
						</select>
					</td></tr>
					<tr><td><label for="route_optimize_latlon">Anhand von Nutzerdaten optimieren</label></td><td><input type="checkbox" name="route_optimize_latlon" id="route_optimize_latlon"></td></tr>
					<tr><td>&nbsp;</td><td><input type="submit" value="Route generieren"></td></tr>
				 </table>
				</form>
				<hr />
				<h3>Oder mittels Adresseingabe:</h3>
				<form id="generate_route_2">
				 <table>
					<tr><td><label for="start">Von</label></td><td><input type="text" name="start" id="start"></td></tr>
					<tr><td><label for="end">Nach</label></td><td><input type="text" name="end" id="end"></td></tr>
					<tr><td><label for="route_profile_address">Profil:</label></td><td>
						<select id="route_profile_address">
						</select>
					</td></tr>
					<tr><td><label for="route_optimize_address">Anhand von Nutzerdaten optimieren</label></td><td><input type="checkbox" name="route_optimize_address" id="route_optimize_address"></td></tr>
					<tr><td>&nbsp;</td><td><input type="submit" value="Route generieren"></td></tr>
				 </table>
				</form>
				<hr />
				<p><b>Info:</b> Die Routenberechnung ist derzeit nur in Nordrhein-Westfalen möglich.</p>
			</div>
			
			<div class="sidebar-pane" id="showtopo_pane">
				<h1>iBis Topologie Anzeigen</h1>
				<p>Overlays anzeigen aus Daten der Routing-Datenbank (zum Visualisieren)</p>
				<h3>In der DB enthaltene Kanten visualisieren </h3>
				<form id="showedges_simple">
					<input type="submit" value="Visualisieren (blau)">
				</form>
				<h3>In der DB enthaltene Kanten mit statischen Kosten visualisieren </h3>
				<form id="showedges_staticcost">
					<input type="submit" value="Visualisieren (farbig rot-gelb-grün)">
					<br />
					<label for="showedges_staticcost_profile">Profil wählen: </label>
					<select id="showedges_staticcost_profile">

					</select>
				</form>
				<h3>In der DB enthaltene Kanten mit dynamische Kosten visualisieren </h3>
				<form id="showedges_dyncost">
					<input type="submit" value="Visualisieren (farbig rot-gelb-grün)">
				</form>
			</div>
			
			<div class="sidebar-pane" id="admin_pane">
				<h1>iBis Administration</h1>
				<div id="admin_content">
				<!-- dynamic content with JS -->
				</div>
			</div>

			<div class="sidebar-pane" id="profile_pane">
				<h1>iBis Routing Profile</h1>
				<p>(<i>Authentifizierung ist zur Bearbeitung erforderlich.</i>)</p>
				<form id="profile_select_form">
					<label for="profile_select">Profil</label>
					<select id="profile_select">

					</select>
					<input type="submit" value="Bearbeiten">
				</form>
				<hr />
				<div id="profile_content">
				<!-- dynamic content with JS -->
				</div>
			</div>

			<div class="sidebar-pane" id="cleanmap_pane">
				<h1>iBis Overlays</h1>
				<h3>Alle Overlays entfernen</h3>
				<form id="cleanmap_form">
					<input type="submit" value="Entfernen">
				</form>
			</div>
		</div>
	</div>
	
	<div id="map" class="sidebar-map"></div>
	
	<script type="text/javascript" src="jquery/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="leaflet/leaflet.js"></script>
	<script type="text/javascript" src="leaflet-sidebar-v2/leaflet-sidebar.min.js"></script>
	<script type="text/javascript" src="leaflet-contextmenu/leaflet.contextmenu.js"></script>
	<script type="text/javascript">
		$.ajaxSetup({'async': false});

		$( document ).ready( function() {
			setTrackSelectOptions($("#track_select_num").val())

			// Set profile select options
			setProfileOptions();

			// admin pane:
			$.getJSON("api1/login.php?status", function(json) {
				if(json.status=="bad") {
					adminContentLogin();
				} else {
					adminContentDelete();
				}
			});
			
		});

		function adminContentLogin() {
			$.getJSON("admin_content.php?content_get=login", function(json) {
				if(json.content) {
					$("#admin_content").html(json.content);
				} else {
					alert("Cannot get admin content");
				}
			});
		}
		function adminContentDelete() {
			$.getJSON("admin_content.php?content_get=delete", function(json) {
				if(json.content) {
					$("#admin_content").html(json.content);
				} else {
					alert("Cannot get admin content");
				}
			});
		}
		
		function deleteTracks() {
			var url = "api1/deletetrack.php?deletetrack&track_ids=";
			$('#admin_delete_select option:selected').each(function() {
				url += $(this).val() + ";";
			});
			
			$.getJSON(url, function(json) {
				if(json.error){
					alert("Fehler: "+json.error);
				} else if(json.success) {
					alert("Erfolg: "+json.success);
				} else {
					alert("Unbekannter Ausnahmefehler ... Aaaaaahh!");
				}
				
				// refresh admin pane:
				adminContentDelete();
			});
		}
		
		function loginUser() {
			var url = "api1/login.php?login&user="+$("#login_user").val()+"&password="+$("#login_pw").val();
			$.getJSON(url, function(json) {
				if(json.error){
					alert("Fehler: "+json.error);
				} else if(json.success) {
					//alert("Erfolg: "+json.success);
					// refresh admin pane:
					adminContentDelete();
				} else {
					alert("Unbekannter Ausnahmefehler ... Aaaaaahh!");
				}
			});
		}
		
		function logoutUser() {
			$.getJSON("api1/login.php?signout", function(json) {
				if(json.error){
					alert("Fehler: "+json.error);
				} else if(json.success) {
					// refresh admin pane:
					adminContentLogin();
				} else {
					alert("Unbekannter Ausnahmefehler ... Aaaaaahh!");
				}
			});
		}
		
		$("#track_select_num_form").submit( function () {
			setTrackSelectOptions($("#track_select_num").val());
		});

		function setTrackSelectOptions(num) {
			var options_uri = "api1/gettrack.php?tracklist=tracklist&num=" + num;
			$.getJSON(options_uri, function (json) {
				var options = "";
				for (var i = 0; i< json.length; i++) {
					options += "<option value=\"" + json[i].track_id + "\">" + json[i].name + "</option>";
				}
				$('#track_select').find("option").remove().end()
				.append(options);
			});
			var num_uri = "api1/gettrack.php?tracknum=tracknum";
			$.getJSON(num_uri, function (json) {
				$('#track_select_num_p').replaceWith("<p id=\"track_select_num_p\">Es sind " + json.num + " Tracks vorhanden.</p>");
				var options = "";
				for (var i = 0; i < json.num; i = i+25) {
					options += "<option value=\"" + i + "\">" + i + "..." + (Math.min((i+24),json.num)) + "</option>";
				}
				var s_num = $("#track_select_num").val();
				$('#track_select_num').find("option").remove().end()
				.append(options);
				$("#track_select_num").val(s_num);
			});
		}

		function setProfileOptions() {
			var options_uri = "api1/updatecost.php?getprofiles";
			$.getJSON(options_uri, function (json) {
				var options = "";
				for (var key in json) {
					options += "<option value=\"" + key + "\">" + json[key] + "</option>";
				}
				$('#profile_select').find("option").remove().end()
				.append(options);
				$('#showedges_staticcost_profile').find("option").remove().end()
				.append(options);
				$('#route_profile_latlon').find("option").remove().end()
				.append(options);
				$('#route_profile_address').find("option").remove().end()
				.append(options);
			});
		}

		// extend the default marker class (custon icon)
		var StartIcon = L.Icon.Default.extend({
		options: {
				//iconUrl: 'leaflet/marker-start.png'
		}
		});
		var startIcon = new StartIcon();

		var DestIcon = L.Icon.Default.extend({
		options: {
				iconUrl: 'leaflet/images/dest-Pin-2x.png'
		}
		});
		var destIcon = new DestIcon();

		function setStart(e) {
			$("#start_lat").val(e.latlng.lat);
			$("#start_lon").val(e.latlng.lng);
			startMark.setLatLng(e.latlng).bindPopup("Start (" + e.latlng.toString() + ")").update();
			/*popup_start
				.setLatLng(e.latlng)
				.setContent("Start at " + e.latlng.toString())
				.openOn(map);*/
		}

		function setDest(e) {
			$("#end_lat").val(e.latlng.lat);
			$("#end_lon").val(e.latlng.lng);
			destMark.setLatLng(e.latlng).bindPopup("Ziel (" + e.latlng.toString() + ")").update();
			/*popup_end
				.setLatLng(e.latlng)
				.setContent("End at " + e.latlng.toString())
				.openOn(map);*/
		}

		function onMapClick(e) {
			if(clickTyp == 0){
				setStart(e);
				clickTyp = clickTyp+1;
			} else if(clickTyp == 1){
				setDest(e);
				clickTyp = clickTyp+1;
			} else if(clickTyp >= 2){
				clickTyp = 0;
			}
		}
			
		function drawPolyline(urlJsonData){
			// Get points of selected track an show it on map
			// Create array of lat,lon points
			var line_points = [];
			$.getJSON(urlJsonData, function (json) {
				if(json.points) {
					for (var i = 0; i < json.points.length; i++) {
						line_points.push(L.latLng(parseFloat(json.points[i].lat), parseFloat(json.points[i].lon)));
					}
					// create a red polyline from an array of LatLng points
					var polyline = L.polyline(line_points, {color: 'red'}).addTo(map);
					// add lat and lon to array:
					lats.push(polyline.getBounds().getSouth());
					lats.push(polyline.getBounds().getNorth());
					lons.push(polyline.getBounds().getWest());
					lons.push(polyline.getBounds().getEast());
				} else {
					alert("Keine Punkte in Routendaten enthalten.");
				}
				if(json.distance) {
					if(json.distance>1000) {
						// Display distance in km with 2 decimal places
						alert("Länge der Route beträgt: "+Math.round(json.distance/10)/100+" Kilometer.");
					} else {
						// Display distance in m without any decimal places
						alert("Länge der Route beträgt: "+Math.round(json.distance)+" Meter.");
					}
				}
			});
		}
			
		function drawMultiPolyline(urlJsonData){
			// Get points of selected track an show it on map
			// Create array of lat,lon points
			var line_points = [];
			$.getJSON(urlJsonData, function (json) {
				for (var j = 0; j < json.length; j++) {
					line_points = [];
					for (var i = 0; i < json[j].length; i++) {
						if (json[j][i].lat)
							line_points.push(L.latLng(parseFloat(json[j][i].lat), parseFloat(json[j][i].lon)));
					}
					// create a red polyline from an array of LatLng points
					var polyline = L.polyline(line_points, {color: 'blue'}).addTo(map);
					// Polylines should be inside current bounds
					/* // add lat and lon to array:
					lats.push(polyline.getBounds().getSouth());
					lats.push(polyline.getBounds().getNorth());
					lons.push(polyline.getBounds().getWest());
					lons.push(polyline.getBounds().getEast()); */
				}
			});
		}
		
		function distance(lat1, lon1, lat2, lon2) {
			var radlat1 = Math.PI * lat1/180;
			var radlat2 = Math.PI * lat2/180;
			var radlon1 = Math.PI * lon1/180;
			var radlon2 = Math.PI * lon2/180;
			var theta = lon1-lon2;
			var radtheta = Math.PI * theta/180;
			var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
			dist = Math.acos(dist);
			dist = dist * 180/Math.PI;
			dist = dist * 60 * 1.1515;
			dist = dist * 1.609344;
			return dist * 1000;
		}
		
		function drawColorPolyline(urlJsonData){
			$.getJSON(urlJsonData, function (json) {
				for (var i = 0; i < json.length-1; i++) {
					var line_points = [2];
					// Line from point i to point i+1
					line_points[0] = L.latLng(parseFloat(json[i].lat), parseFloat(json[i].lon)); 
					line_points[1] = L.latLng(parseFloat(json[i+1].lat), parseFloat(json[i+1].lon));
					// Distance between point i and point i+1
					var dist = distance(json[i].lat, json[i].lon, json[i+1].lat, json[i+1].lon); // in meters
					// Speed calculation based on  timestamp difference and distance
					var dtime = json[i+1].timestamp-json[i].timestamp; 		// in seconds
					var speed = dist/dtime;		// in m/s (meter/second)
					// Color of line dependung on Speed
					var color;
					var speed_co = 0.500;
					if(speed<1*speed_co) {
						color = "#FF0000";
					} else if(speed<(3*speed_co)) {
						color = "#FF4000";
					} else if(speed<(5*speed_co)) {
						color = "#FF8000";
					} else if(speed<(8*speed_co)) {
						color = "#FFC000";
					} else if(speed<(11*speed_co)) {
						color = "#FFFF00";
					} else if(speed<(14*speed_co)) {
						color = "#C0FF00";
					} else if(speed<(17*speed_co)) {
						color = "#80FF00";
					} else if(speed<(20*speed_co)) {
						color = "#40FF00";
					} else if(speed<(25*speed_co)) {
						color = "#10FF00";
					} else {
						color = "#0000FF";
					}
					var polyline = L.polyline(line_points, {color: color}).addTo(map);
					lats.push(polyline.getBounds().getSouth());
					lats.push(polyline.getBounds().getNorth());
					lons.push(polyline.getBounds().getWest());
					lons.push(polyline.getBounds().getEast());
				}
			});
		}
		
		function drawMultiColorPolyline(urlJsonData){
			$.getJSON(urlJsonData, function (json) {
				for (var j = 0; j < json.length-1; j++) {
					line_points = [];
					
					var cost = 100000;
					
					for (var i = 0; i < json[j].length; i++) {
						if (json[j][i].cost)
							cost = parseFloat(json[j][i].cost);
						if (json[j][i].lat)
							line_points.push(L.latLng(parseFloat(json[j][i].lat), parseFloat(json[j][i].lon)));
					}
					
					// Color of line dependung on Speed
					var color;
					if(cost<0.55) {
						color = "#00FF00";
					} else if(cost<(0.65)) {
						color = "#40FF00";
					} else if(cost<(0.75)) {
						color = "#80FF00";
					} else if(cost<(0.90)) {
						color = "#C0FF00";
					} else if(cost<(1.05)) {
						color = "#FFFF00";
					} else if(cost<(1.25)) {
						color = "#FFC000";
					} else if(cost<(1.60)) {
						color = "#FF8000";
					} else if(cost<(2.50)) {
						color = "#FF4000";
					} else if(cost<(100.0)) {
						color = "#FF0000";
					} else {
						color = "#000000";
					}
					
					// create a red polyline from an array of LatLng points
					var polyline = L.polyline(line_points, {color: color}).addTo(map);
				}
			});
		}
		
		function clearMap() {
			for(i in map._layers) {
				if(map._layers[i]._path != undefined) {
					try {
						map.removeLayer(map._layers[i]);
					} catch(e) {
						console.log("problem with " + e + map._layers[i]);
					}
				}
			}
		}

		var map = L.map('map', {
				contextmenu: true,
				contextmenuWidth: 120,
				contextmenuItems: [{
					text: 'Startpunkt setzen',
					//icon: 'images/zoom-in.png',
					callback: setStart
				}, {
					text: 'Ziel setzen',
					callback: setDest
				}]
			});

		map.setView([50, 7], 7);

		// http://{s}.tile.thunderforest.com/cycle (OpenCycleMap) ist leider nicht über https verfügbar
		// -> leider MixedContent 
		//L.tileLayer('http://{s}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png', {
		L.tileLayer('https://tiles.rleh.de/tiles/thunderforest/{z}/{x}/{y}.png', {
			maxZoom: 18
		}).addTo(map);

		navigator.geolocation.getCurrentPosition( function GetLocation(location) {
			map.panTo([location.coords.latitude, location.coords.longitude]);
			map.zoomIn(2);
		});

		var sidebar = L.control.sidebar('sidebar').addTo(map);

	
		var clickTyp = 0;
		var popup_start = L.popup();
		var popup_end = L.popup();

		var lats = [];
		var lons = [];

		var startMark = L.marker([0, 0], {icon: startIcon}).addTo(map);
		var destMark = L.marker([0, 0], {icon: destIcon}).addTo(map);

		map.on('click', onMapClick);

		$( "#show_track" ).submit(function( event ) {
			// Remove all polylines
			clearMap();
			lats = [];
			lons = [];
			// Draw ploylines for any sleected track
			$('#track_select option:selected').each(function() {
				drawColorPolyline("api1/gettrack.php?gettrack=gettrack&track_id=" + $(this).val());
			});
			$('#track_select option:selected').promise().done(function() {
				var latSouth = Math.max.apply(Math, lats);
				var latNorth = Math.min.apply(Math, lats);
				var lngWest = Math.max.apply(Math, lons);
				var lngEast = Math.min.apply(Math, lons);
				var southWest = L.latLng(latSouth, lngWest);
				var northEast = L.latLng(latNorth, lngEast);
				map.fitBounds(L.latLngBounds(southWest, northEast));
			});
			// prevent reload
			event.preventDefault();
			if (!(window.matchMedia('(min-width: 768px)').matches)) {
				sidebar.close();
			}
		});
		
		$( "#generate_route" ).submit(function( event ) {
			event.preventDefault();
			// Remove all polylines
			clearMap();
			lats = [];
			lons = [];
			var optimize = "&optimize=0";
			if( $("#route_optimize_latlon").prop("checked") ) {
				optimize = "&optimize=1";
			}
			// draw polyline for route
			drawPolyline( "api1/getroute.php?getroute=getroute"
				+"&start_lat="+$("#start_lat").val()
				+"&start_lon="+$("#start_lon").val()
				+"&end_lat="+$("#end_lat").val()
				+"&end_lon="+$("#end_lon").val()
				+"&profile="+$("#route_profile_latlon").val()
				+optimize);
			// prevent reload
			var latSouth = Math.max.apply(Math, lats);
			var latNorth = Math.min.apply(Math, lats);
			var lngWest = Math.max.apply(Math, lons);
			var lngEast = Math.min.apply(Math, lons);
			var southWest = L.latLng(latSouth, lngWest);
			var northEast = L.latLng(latNorth, lngEast);
			map.fitBounds(L.latLngBounds(southWest, northEast));
		});

		$( "#generate_route_2" ).submit(function( event ) {
			event.preventDefault();
			// Remove all polylines
			clearMap();
			lats = [];
			lons = [];
			var optimize = "&optimize=0";
			if( $("#route_optimize_address").prop("checked") ) {
				optimize = "&optimize=1";
			}
			// draw polyline for route
			drawPolyline( "api1/getroute.php?getroute=getroute"
				+"&start="+$("#start").val()
				+"&end="+$("#end").val()
				+"&profile="+$("#route_profile_address").val()
				+optimize);
			// prevent reload
			var latSouth = Math.max.apply(Math, lats);
			var latNorth = Math.min.apply(Math, lats);
			var lngWest = Math.max.apply(Math, lons);
			var lngEast = Math.min.apply(Math, lons);
			var southWest = L.latLng(latSouth, lngWest);
			var northEast = L.latLng(latNorth, lngEast);
			map.fitBounds(L.latLngBounds(southWest, northEast));
		});

		$( "#showedges_simple" ).submit(function( event ) {
			// Remove all polylines
			clearMap();
			// Get Bound of current leaflet map:
			var bounds = map.getBounds();
			// draw polyline for every edge
			drawMultiPolyline( "api1/gettopo.php?getedges=getedges"
				+"&start_lat="+bounds.getNorth()
				+"&start_lon="+bounds.getWest()
				+"&end_lat="+bounds.getSouth()
				+"&end_lon="+bounds.getEast() );
			// (Polylines should be inside current bounds)
			// prevent reload
			event.preventDefault();
		});
		
		$( "#showedges_staticcost" ).submit(function( event ) {
			// Remove all polylines
			clearMap();
			// Get Bound of current leaflet map:
			var bounds = map.getBounds();
			// draw polyline for every edge
			drawMultiColorPolyline( "api1/gettopo.php?getedges=getedges&cost=static"
				+"&profile="+$("#showedges_staticcost_profile").val()
				+"&start_lat="+bounds.getNorth()
				+"&start_lon="+bounds.getWest()
				+"&end_lat="+bounds.getSouth()
				+"&end_lon="+bounds.getEast() );
			// (Polylines should be inside current bounds)
			// prevent reload
			event.preventDefault();
		});
		
		$( "#showedges_dyncost" ).submit(function( event ) {
			// Remove all polylines
			clearMap();
			// Get Bound of current leaflet map:
			var bounds = map.getBounds();
			// draw polyline for every edge
			drawMultiColorPolyline( "api1/gettopo.php?getedges=getedges&cost=dynamic"
				+"&start_lat="+bounds.getNorth()
				+"&start_lon="+bounds.getWest()
				+"&end_lat="+bounds.getSouth()
				+"&end_lon="+bounds.getEast() );
			// (Polylines should be inside current bounds)
			// prevent reload
			event.preventDefault();
		});
		
		$( "#cleanmap_form" ).submit(function( event ) {
			// Remove all polylines
			clearMap();
		});

		$( "#profile_select_form" ).submit(function( event ) {
			// Check if user is authenticated
			// TODO
			// Load profile editor
			$.getJSON("profile_content.php?profile="+$("#profile_select").val(), function(json) {
				if(json.content) {
					$("#profile_content").html(json.content);
				} else {
					alert("Cannot get profile editor");
				}
			});
		});

		function updateProfile(event) {
			var url = "api1/updatecost.php?profile="+$("#profile_profile").val();
			$("#profile_update_form .cost").each(function(){
				url += "&" + $(this).attr("id") + "=" + $(this).val();
			});
			$.getJSON(url, function(json) {
				if(json.error){
					alert("Fehler: "+json.error);
				} else if(json.success) {
					alert("Erfolg: "+json.success);
				} else {
					alert("Unbekannter Ausnahmefehler ... Aaaaaahh!");
				}
			});
			// Prevent reload:
			event.preventDefault();
			return false;
		}

		/*
		$("#routing_start_p").click(function() {
			var uri = "api1/getroute.php?getid=getid&lat=" + $("#start_lat").val() + "&lon=" + $("#start_lon").val();
			$.getJSON(uri, function (json) {
			});
		});
		*/
		
		/*
		$("#routing_end_p").click(function() {
			var uri = "api1/getroute.php?getid=getid&lat=" + $("#end_lat").val() + "&lon=" + $("#end_lon").val();
			$.getJSON(uri, function (json) {
			});
		});
		*/
		
	</script>
<?php
//pg_close ( $pgr );
$my->close ();
?>
</body>
</html>
