        <!-- jQuery UI 1.11.4 -->
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <script>
          $.widget.bridge('uibutton', $.ui.button)
        </script>
        <!-- Bootstrap 4 -->
        <script src="<?php echo base_url( 'assets/plugins/bootstrap/js/bootstrap.bundle.min.js' ); ?>"></script>
        <!-- Morris.js charts -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
        <script src="<?php echo base_url( 'assets/plugins/morris/morris.min.js' ); ?>"></script>
        <!-- Sparkline -->
        <script src="<?php echo base_url( 'assets/plugins/sparkline/jquery.sparkline.min.js' ); ?>"></script>
        <!-- jvectormap -->
        <script src="<?php echo base_url( 'assets/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js' ); ?>"></script>
        <script src="<?php echo base_url( 'assets/plugins/jvectormap/jquery-jvectormap-world-mill-en.js' ); ?>"></script>
        <!-- jQuery Knob Chart -->
        <script src="<?php echo base_url( 'assets/plugins/knob/jquery.knob.js' ); ?>"></script>
        <!-- daterangepicker -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
        <script src="<?php echo base_url( 'assets/plugins/daterangepicker/daterangepicker.js' ); ?>"></script>
        <!-- color picker -->
         <script src="<?php echo base_url( 'assets/plugins/colorpicker/bootstrap-colorpicker.min.js' ); ?>"></script>
        <!-- datepicker -->
        <script src="<?php echo base_url( 'assets/plugins/datepicker/bootstrap-datepicker.js' ); ?>"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="<?php echo base_url( 'assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js' ); ?>"></script>
        <!-- Slimscroll -->
        <script src="<?php echo base_url( 'assets/plugins/slimScroll/jquery.slimscroll.min.js' ); ?>"></script>
        <!-- FastClick -->
        <script src="<?php echo base_url( 'assets/plugins/fastclick/fastclick.js' ); ?>"></script>
        <!-- AdminLTE App(This is sidebar and nav action) -->
        <script src="<?php echo base_url( 'assets/dist/js/adminlte.js' ); ?>"></script>
        <!-- AdminLTE for demo purposes -->
        <script src="<?php echo base_url( 'assets/dist/js/demo.js' ); ?>"></script>
        <!-- OpenStreet Map -->
        <!-- Load Leaflet from CDN -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
            integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
            crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
            integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
            crossorigin=""></script>

        <!-- Load Esri Leaflet from CDN -->
        <script src="https://unpkg.com/esri-leaflet@2.5.3/dist/esri-leaflet.js"
            integrity="sha512-K0Vddb4QdnVOAuPJBHkgrua+/A9Moyv8AQEWi0xndQ+fqbRfAFd47z4A9u1AW/spLO0gEaiE1z98PK1gl5mC5Q=="
            crossorigin=""></script>

        <!-- Load Esri Leaflet Geocoder from CDN -->
        <link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.3.3/dist/esri-leaflet-geocoder.css"
            integrity="sha512-IM3Hs+feyi40yZhDH6kV8vQMg4Fh20s9OzInIIAc4nx7aMYMfo+IenRUekoYsHZqGkREUgx0VvlEsgm7nCDW9g=="
            crossorigin="">
        <script src="https://unpkg.com/esri-leaflet-geocoder@2.3.3/dist/esri-leaflet-geocoder.js"
            integrity="sha512-HrFUyCEtIpxZloTgEKKMq4RFYhxjJkCiF5sDxuAokklOeZ68U2NPfh4MFtyIVWlsKtVbK5GD2/JzFyAfvT5ejA=="
            crossorigin=""></script>
        <script>
            <?php
                if (isset($item)) {
                    $lat = $item->lat;
                    $lng = $item->lng;
            ?>
                    var itm_map = L.map('itm_location').setView([<?php echo $lat;?>, <?php echo $lng;?>], 5);
            <?php
                } else {
            ?>
                    var itm_map = L.map('itm_location').setView([0, 0], 5);
            <?php
                }
            ?>

            const itm_attribution =
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
            const itm_tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            const itm_tiles = L.tileLayer(itm_tileUrl, { itm_attribution });
            itm_tiles.addTo(itm_map);
            <?php if(isset($item)) {?>
                var itm_marker = new L.Marker(new L.LatLng(<?php echo $lat;?>, <?php echo $lng;?>));
                itm_map.addLayer(itm_marker);
                // results = L.marker([<?php echo $lat;?>, <?php echo $lng;?>]).addTo(mymap);

            <?php } else { ?>
                var itm_marker = new L.Marker(new L.LatLng(0, 0));
                //mymap.addLayer(marker2);
            <?php } ?>
            var itm_searchControl = L.esri.Geocoding.geosearch().addTo(itm_map);
            var results = L.layerGroup().addTo(itm_map);

            itm_searchControl.on('results',function(data){
                results.clearLayers();

                for(var i= data.results.length -1; i>=0; i--) {
                    itm_map.removeLayer(itm_marker);
                    results.addLayer(L.marker(data.results[i].latlng));
                    var itm_search_str = data.results[i].latlng.toString();
                    var itm_search_res = itm_search_str.substring(itm_search_str.indexOf("(") + 1, itm_search_str.indexOf(")"));
                    var itm_searchArr = new Array();
                    itm_searchArr = itm_search_res.split(",");

                    document.getElementById("lat").value = itm_searchArr[0].toString();
                    document.getElementById("lng").value = itm_searchArr[1].toString(); 
                   
                }
            })
            var popup = L.popup();

            function onMapClick(e) {

                var itm = e.latlng.toString();
                var itm_res = itm.substring(itm.indexOf("(") + 1, itm.indexOf(")"));
                itm_map.removeLayer(itm_marker);
                results.clearLayers();
                results.addLayer(L.marker(e.latlng));   

                var itm_tmpArr = new Array();
                itm_tmpArr = itm_res.split(",");

                document.getElementById("lat").value = itm_tmpArr[0].toString(); 
                document.getElementById("lng").value = itm_tmpArr[1].toString();
            }

            itm_map.on('click', onMapClick);
        </script>
        <script>
            <?php
                if (isset($app)) {
                    $lat = $app->lat;
                    $lng = $app->lng;
            ?>
                    var app_map = L.map('app_location').setView([<?php echo $lat;?>, <?php echo $lng;?>], 5);
            <?php
                } else {
            ?>
                    var app_map = L.map('app_location').setView([0, 0], 5);
            <?php
                }
            ?>

            const app_attribution =
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
            const app_tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            const app_tiles = L.tileLayer(app_tileUrl, { app_attribution });
            app_tiles.addTo(app_map);
            <?php if(isset($app)) {?>
                var app_marker = new L.Marker(new L.LatLng(<?php echo $lat;?>, <?php echo $lng;?>));
                app_map.addLayer(app_marker);
                // results = L.marker([<?php echo $lat;?>, <?php echo $lng;?>]).addTo(mymap);

            <?php } else { ?>
                var app_marker = new L.Marker(new L.LatLng(0, 0));
                //mymap.addLayer(marker2);
            <?php } ?>
            var app_searchControl = L.esri.Geocoding.geosearch().addTo(app_map);
            var results = L.layerGroup().addTo(app_map);

            app_searchControl.on('results',function(data){
                results.clearLayers();

                for(var i= data.results.length -1; i>=0; i--) {
                    app_map.removeLayer(app_marker);
                    results.addLayer(L.marker(data.results[i].latlng));
                    var app_search_str = data.results[i].latlng.toString();
                    var app_search_res = app_search_str.substring(app_search_str.indexOf("(") + 1, app_search_str.indexOf(")"));
                    var app_searchArr = new Array();
                    app_searchArr = app_search_res.split(",");

                    document.getElementById("lat").value = app_searchArr[0].toString();
                    document.getElementById("lng").value = app_searchArr[1].toString(); 
                   
                }
            })
            var popup = L.popup();

            function onMapClick(e) {

                var app = e.latlng.toString();
                var app_res = app.substring(app.indexOf("(") + 1, app.indexOf(")"));
                app_map.removeLayer(app_marker);
                results.clearLayers();
                results.addLayer(L.marker(e.latlng));   

                var app_tmpArr = new Array();
                app_tmpArr = app_res.split(",");

                document.getElementById("lat").value = app_tmpArr[0].toString(); 
                document.getElementById("lng").value = app_tmpArr[1].toString();
            }

            app_map.on('click', onMapClick);
        </script>
          <script>
            <?php
                if (isset($location)) {
                    $lat = $location->lat;
                    $lng = $location->lng;
            ?>
                    var mymap = L.map('location_map').setView([<?php echo $lat;?>, <?php echo $lng;?>], 5);
            <?php
                } else {
            ?>
                    var mymap = L.map('location_map').setView([0, 0], 5);
            <?php
                }
            ?>

            const attribution =
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
            const tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            const tiles = L.tileLayer(tileUrl, { attribution });
            tiles.addTo(mymap);
            <?php if(isset($location)) {?>
                var marker2 = new L.Marker(new L.LatLng(<?php echo $lat;?>, <?php echo $lng;?>));
                mymap.addLayer(marker2);
                // results = L.marker([<?php echo $lat;?>, <?php echo $lng;?>]).addTo(mymap);

            <?php } else { ?>
                var marker2 = new L.Marker(new L.LatLng(0, 0));
                //mymap.addLayer(marker2);
            <?php } ?>
            var searchControl = L.esri.Geocoding.geosearch().addTo(mymap);
            var results = L.layerGroup().addTo(mymap);

            searchControl.on('results',function(data){
                results.clearLayers();

                for(var i= data.results.length -1; i>=0; i--) {
                    mymap.removeLayer(marker2);
                    results.addLayer(L.marker(data.results[i].latlng));
                    var search_str = data.results[i].latlng.toString();
                    var search_res = search_str.substring(search_str.indexOf("(") + 1, search_str.indexOf(")"));
                    var searchArr = new Array();
                    searchArr = search_res.split(",");

                    document.getElementById("lat").value = searchArr[0].toString();
                    document.getElementById("lng").value = searchArr[1].toString(); 
                   
                }
            })
            var popup = L.popup();

            function onMapClick(e) {

                var str = e.latlng.toString();
                var str_res = str.substring(str.indexOf("(") + 1, str.indexOf(")"));
                mymap.removeLayer(marker2);
                results.clearLayers();
                results.addLayer(L.marker(e.latlng));   

                var tmpArr = new Array();
                tmpArr = str_res.split(",");

                document.getElementById("lat").value = tmpArr[0].toString(); 
                document.getElementById("lng").value = tmpArr[1].toString();
            }

            mymap.on('click', onMapClick);
        </script>

        <!-- pending map -->

        <script>

            <?php
                if (isset($pending)) {
                    $lat = $pending->lat;
                    $lng = $pending->lng;
            ?>
                    var pending_map = L.map('pending_map').setView([<?php echo $lat;?>, <?php echo $lng;?>], 5);
            <?php
                } else {
            ?>
                    var pending_map = L.map('pending_map').setView([0, 0], 5);
            <?php
                }
            ?>

            const pending_attribution =
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
            const pending_tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            const pending_tiles = L.tileLayer(pending_tileUrl, { pending_attribution });
            pending_tiles.addTo(pending_map);
            <?php if(isset($pending)) {?>
                var pending_marker = new L.Marker(new L.LatLng(<?php echo $lat;?>, <?php echo $lng;?>));
                pending_map.addLayer(pending_marker);
                // results = L.marker([<?php echo $lat;?>, <?php echo $lng;?>]).addTo(mymap);

            <?php } else { ?>
                var pending_marker = new L.Marker(new L.LatLng(0, 0));
                //mymap.addLayer(marker2);
            <?php } ?>
            var results = L.layerGroup().addTo(pending_map);
            
            var popup = L.popup();

            function onMapClick(e) {

                var pending = e.latlng.toString();
                var pending_res = pending.substring(pending.indexOf("(") + 1, pending.indexOf(")"));
                pending_map.removeLayer(pending_marker);
                results.clearLayers();
                results.addLayer(L.marker(e.latlng));   

                var pending_tmpArr = new Array();
                pending_tmpArr = pending_res.split(",");

                document.getElementById("lat").value = pending_tmpArr[0].toString(); 
                document.getElementById("lng").value = pending_tmpArr[1].toString();
            }

            pending_map.on('click', onMapClick);
        </script>

        <!-- disable map -->

        <script>

            <?php
                if (isset($disable)) {
                    $lat = $disable->lat;
                    $lng = $disable->lng;
            ?>
                    var disable_map = L.map('disable_map').setView([<?php echo $lat;?>, <?php echo $lng;?>], 5);
            <?php
                } else {
            ?>
                    var disable_map = L.map('disable_map').setView([0, 0], 5);
            <?php
                }
            ?>

            const disable_attribution =
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
            const disable_tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            const disable_tiles = L.tileLayer(disable_tileUrl, { disable_attribution });
            disable_tiles.addTo(disable_map);
            <?php if(isset($disable)) {?>
                var disable_marker = new L.Marker(new L.LatLng(<?php echo $lat;?>, <?php echo $lng;?>));
                disable_map.addLayer(disable_marker);
                // results = L.marker([<?php echo $lat;?>, <?php echo $lng;?>]).addTo(mymap);

            <?php } else { ?>
                var disable_marker = new L.Marker(new L.LatLng(0, 0));
                //mymap.addLayer(marker2);
            <?php } ?>
            var results = L.layerGroup().addTo(disable_map);
            
            var popup = L.popup();

            function onMapClick(e) {

                var disable = e.latlng.toString();
                var disable_res = disable.substring(disable.indexOf("(") + 1, disable.indexOf(")"));
                disable_map.removeLayer(disable_marker);
                results.clearLayers();
                results.addLayer(L.marker(e.latlng));   

                var disable_tmpArr = new Array();
                disable_tmpArr = disable_res.split(",");

                document.getElementById("lat").value = disable_tmpArr[0].toString(); 
                document.getElementById("lng").value = disable_tmpArr[1].toString();
            }

            disable_map.on('click', onMapClick);
        </script>

        <!-- reject map -->

        <script>

            <?php
                if (isset($reject)) {
                    $lat = $reject->lat;
                    $lng = $reject->lng;
            ?>
                    var reject_map = L.map('reject_map').setView([<?php echo $lat;?>, <?php echo $lng;?>], 5);
            <?php
                } else {
            ?>
                    var reject_map = L.map('reject_map').setView([0, 0], 5);
            <?php
                }
            ?>

            const reject_attribution =
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
            const reject_tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            const reject_tiles = L.tileLayer(reject_tileUrl, { reject_attribution });
            reject_tiles.addTo(reject_map);
            <?php if(isset($reject)) {?>
                var reject_marker = new L.Marker(new L.LatLng(<?php echo $lat;?>, <?php echo $lng;?>));
                reject_map.addLayer(reject_marker);
                // results = L.marker([<?php echo $lat;?>, <?php echo $lng;?>]).addTo(mymap);

            <?php } else { ?>
                var reject_marker = new L.Marker(new L.LatLng(0, 0));
                //mymap.addLayer(marker2);
            <?php } ?>
            var results = L.layerGroup().addTo(reject_map);
            
            var popup = L.popup();

            function onMapClick(e) {

                var reject = e.latlng.toString();
                var reject_res = reject.substring(reject.indexOf("(") + 1, reject.indexOf(")"));
                reject_map.removeLayer(reject_marker);
                results.clearLayers();
                results.addLayer(L.marker(e.latlng));   

                var reject_tmpArr = new Array();
                reject_tmpArr = reject_res.split(",");

                document.getElementById("lat").value = reject_tmpArr[0].toString(); 
                document.getElementById("lng").value = reject_tmpArr[1].toString();
            }

            reject_map.on('click', onMapClick);
        </script>

        <!-- popular item map-->

        <script>

            <?php
                if (isset($popularitem)) {
                    $lat = $popularitem->lat;
                    $lng = $popularitem->lng;
            ?>
                    var popularitem_map = L.map('popularitem_map').setView([<?php echo $lat;?>, <?php echo $lng;?>], 5);
            <?php
                } else {
            ?>
                    var popularitem_map = L.map('popularitem_map').setView([0, 0], 5);
            <?php
                }
            ?>

            const popularitem_attribution =
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
            const popularitem_tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            const popularitem_tiles = L.tileLayer(popularitem_tileUrl, { popularitem_attribution });
            popularitem_tiles.addTo(popularitem_map);
            <?php if(isset($popularitem)) {?>
                var popularitem_marker = new L.Marker(new L.LatLng(<?php echo $lat;?>, <?php echo $lng;?>));
                popularitem_map.addLayer(popularitem_marker);
                // results = L.marker([<?php echo $lat;?>, <?php echo $lng;?>]).addTo(mymap);

            <?php } else { ?>
                var popularitem_marker = new L.Marker(new L.LatLng(0, 0));
                //mymap.addLayer(marker2);
            <?php } ?>
            var results = L.layerGroup().addTo(popularitem_map);
            
            var popup = L.popup();

            function onMapClick(e) {

                var popularitem = e.latlng.toString();
                var popularitem_res = popularitem.substring(popularitem.indexOf("(") + 1, popularitem.indexOf(")"));
                popularitem_map.removeLayer(popularitem_marker);
                results.clearLayers();
                results.addLayer(L.marker(e.latlng));   

                var popularitem_tmpArr = new Array();
                popularitem_tmpArr = popularitem_res.split(",");

                document.getElementById("lat").value = popularitem_tmpArr[0].toString(); 
                document.getElementById("lng").value = popularitem_tmpArr[1].toString();
            }

            popularitem_map.on('click', onMapClick);
        </script>

        <!-- report item map -->

        <script>

            <?php
                if (isset($item)) {
                    $lat = $item->lat;
                    $lng = $item->lng;
            ?>
                    var report_map = L.map('report_map').setView([<?php echo $lat;?>, <?php echo $lng;?>], 5);
            <?php
                } else {
            ?>
                    var report_map = L.map('report_map').setView([0, 0], 5);
            <?php
                }
            ?>

            const report_attribution =
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
            const report_tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            const report_tiles = L.tileLayer(report_tileUrl, { report_attribution });
            report_tiles.addTo(report_map);
            <?php if(isset($item)) {?>
                var report_marker = new L.Marker(new L.LatLng(<?php echo $lat;?>, <?php echo $lng;?>));
                report_map.addLayer(report_marker);
                // results = L.marker([<?php echo $lat;?>, <?php echo $lng;?>]).addTo(mymap);

            <?php } else { ?>
                var report_marker = new L.Marker(new L.LatLng(0, 0));
                //mymap.addLayer(marker2);
            <?php } ?>
            var results = L.layerGroup().addTo(report_map);
            
            var popup = L.popup();

            function onMapClick(e) {

                var report = e.latlng.toString();
                var report_res = report.substring(report.indexOf("(") + 1, report.indexOf(")"));
                report_map.removeLayer(report_marker);
                results.clearLayers();
                results.addLayer(L.marker(e.latlng));   

                var report_tmpArr = new Array();
                report_tmpArr = report_res.split(",");

                document.getElementById("lat").value = report_tmpArr[0].toString(); 
                document.getElementById("lng").value = report_tmpArr[1].toString();
            }

            report_map.on('click', onMapClick);
        </script>

        <script src="https://cdn.ckeditor.com/4.6.2/standard-all/ckeditor.js"></script>
        <?php show_analytic(); ?>
        <script src="<?php echo base_url( 'assets/validator/jquery.validate.js' ); ?>"></script>
         
		<script type="text/javascript">
			
			// functions to run after jquery is loaded
			if ( typeof runAfterJQ == "function" ) runAfterJQ();

			<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>
				
				// functions to run after jquery is loaded
				if ( typeof jqvalidate == "function" ) jqvalidate();

			<?php endif; ?>

            $('.page-sidebar-menu li').removeClass('active');

            // highlight submenu item
            $('li a[href="' + this.location.pathname + '"]').parent().addClass('active');

            // Highlight parent menu item.
            $('ul a[href="' + this.location.pathname + '"]').parents('li').addClass('active');

            

		</script>

        <script>
  
          $(function () {
              //Date range picker for offline paid
           
            $('input[name="daterange"]').daterangepicker();

            })

        </script>

        <script>
  
          $(function () {
              //Date range picker for offline paid
           
            $('#reservation').daterangepicker();

            })

        </script>

		<?php if ( isset( $load_gallery_js )) : ?>

			<?php $this->load->view( $template_path .'/components/gallery_script' ); ?>	

		<?php endif; ?>

	</div>
 <!-- ./ wrapper -->
</body>
</html>