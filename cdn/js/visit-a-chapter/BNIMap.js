function BNIMap(opts) {

    var pageCountryCode;

    //
    var _m_div_id = param (opts, "div_id", null, true);
    var _m_org_ids = param (opts, "org_ids", null, true);
    var _m_bni_domain = param (opts, "bni_domain", null, true);
    var _m_bni_api_domain_uri = param(opts,"bni_api_domain",null,true);
    var _m_bni_api_chapterinfo  = _m_bni_api_domain_uri+"frontend/consume/chapterInfo/";
    var _m_website_domain = param (opts, "website_domain", null, true);
    var _o_search_suffix = param(opts, "search_suffix", null, false);

    //_m_bni_domain = _m_bni_domain.replace('https','http');

    var _o_region_bias = param(opts, "region_bias", null, false);
    var _o_locale = param(opts, "locale", "en_US", false);

    var _f_debug = param (opts, "debug", false, false);

    var _cl_small;
    var _cl_medium;
    var _cl_large;
    if (opts.cluster_styles != null) {
        _cl_small = param_cluster(opts.cluster_styles, "small");
        _cl_medium = param_cluster(opts.cluster_styles, "medium");
        _cl_large = param_cluster(opts.cluster_styles, "large");
    }

    var _p_hq;
    var _p_country;
    var _p_region;
    var _p_chapter;
    var _p_group;
    var _p_planned;
    var _p_dropped_chapter;
    var _p_suspend_chapter;
    var _p_dropped_group;
    if (opts.markers != null) {
        _p_hq = param(opts.markers, "hq", null, false);
        _p_country = param(opts.markers, "country", null, false);
        _p_region = param(opts.markers, "region", null, false);
        _p_chapter = param(opts.markers, "chapter", null, false);
        _p_group = param(opts.markers, "core_group", null, false);
        _p_planned = param(opts.markers, "planned_group", null, false);
        _p_dropped_chapter = param(opts.markers, "dropped_chapter", null, false);
        _p_dropped_group = param(opts.markers, "dropped_group", null, false);
        _p_suspend_chapter = param(opts.markers, "suspend_chapter", null, false);
    }

    var _func_searchResult = opts.after_search_completed == null ? function() {} : opts.after_search_completed;

    var _google_weights;

    google.maps.Marker.prototype.html = "";

    // Local variables
    var map;
    var webSiteBounds;
    var locationSet = false;
    var geocoder = new google.maps.Geocoder();

    var markerInfowindow = new google.maps.InfoWindow();

    // IE8 fix, console does not exist until F12 tools are ran
    // Create them if they don't exist so calls to console.log don't throw exceptions
    if(!window.console) {
        window.console = { "log" : function(str) {} };
    }

    BNIMap.prototype.log = function () {
        var l = "";
        l += "_m_div_id = " + _m_div_id + "\n";
        l += "_m_org_ids = " + _m_org_ids + "\n";
        l += "_m_bni_domain = " + _m_bni_domain + "\n";
        l += "_m_website_domain = " + _m_website_domain + "\n";
        l += "_o_search_suffix = " + _o_search_suffix + "\n";
        l += "_o_locale = " + _o_locale + "\n";
        l += "_cl_small = = " + (_cl_small == null ? "null\n" : "{opt_textColor : " + _cl_small.opt_textColor +  ", height : " + _cl_small.height + ", width : " + _cl_small.width + ", url : " + _cl_small.url + "} \n");
        l += "_cl_medium = " + (_cl_medium == null ? "null\n" : "{opt_textColor : " + _cl_medium.opt_textColor + ", height : " + _cl_medium.height + ", width : " + _cl_medium.width + ", url : " + _cl_medium.url + "} \n");
        l += "_cl_large = = " + (_cl_large == null ? "null\n" : "{opt_textColor : " + _cl_large.opt_textColor +  ", height : " + _cl_large.height + ", width : " + _cl_large.width + ", url : " + _cl_large.url + "} \n");
        l += "_p_hq = " + _p_hq + "\n";
        l += "_p_country = " + _p_country + "\n";
        l += "_p_region = " + _p_region + "\n";
        l += "_p_chapter = " + _p_chapter + "\n";
        l += "_p_group = " + _p_group + "\n";
        l += "_p_planned = " + _p_planned + "\n";
        l += "_p_dropped_chapter = " + _p_dropped_chapter + "\n";
        l += "_p_dropped_group = " + _p_dropped_group + "\n";
        l += "_p_suspend_chapter = " + _p_suspend_chapter + "\n";
        l += "_o_region_bias = " + _o_region_bias + "\n";
        console.log(l);
    };

    /*
     * Load the BNI Google weighting data.
     */
    BNIMap.prototype.loadWeights = function () {
        $.ajax({
            async: false,
            url: '/web/open/getGoogleWeights',
            type: "GET",
            dataType: "json",
            success: function(data) {
                _google_weights = data;
            }
        });
    };

    /*
     * Return a google marker given a BNI point object
     */
    BNIMap.prototype.getMarker = function(point) {
        var markerOptions = {};
        var coordinates = point.coordinates.split(",");
        markerOptions.map = this.map;
        markerOptions.position = new google.maps.LatLng(coordinates[1],coordinates[0]);
        if (point.orgType == "HQ" && _p_hq != null) markerOptions.icon = _p_hq;
        if (point.orgType == "COUNTRY" && _p_country != null) markerOptions.icon = _p_country;
        if (point.orgType == "REGION" && _p_region != null) markerOptions.icon = _p_region;
        if (point.orgType == "CHAPTER" && _p_chapter != null) markerOptions.icon = _p_chapter;
        if (point.orgType == "CORE_GROUP" && _p_group != null) markerOptions.icon = _p_group;
        if (point.orgType == "SUSPEND_CHAPTER" && _p_suspend_chapter != null) markerOptions.icon = _p_suspend_chapter;
        if (point.orgType == "DROPPED_CHAPTER" && _p_dropped_chapter != null) markerOptions.icon = _p_dropped_chapter;
        if (point.orgType == "DROPPED_GROUP" && _p_dropped_group != null) markerOptions.icon = _p_dropped_group;
        if (point.orgType == "PLANNED_GROUP" && _p_planned != null) markerOptions.icon = _p_planned;

        markerOptions.orgId = point.orgId;
        markerOptions.cmsSecurityHash = point.cmsSecurityHash;

        return new google.maps.Marker(markerOptions);
    };

    /*
     * Return the cluster marker styles, if they have been set.
     */
    BNIMap.prototype.getClusterMarkers = function() {
        var mcOptions = {
            imagePath: "/web/images/map/map_m"
        };

        var clusterOptions = [];
        if (_cl_small != null && _cl_medium != null && _cl_large != null) {
            clusterOptions[0] = _cl_small;
            clusterOptions[1] = _cl_medium;
            clusterOptions[2] = _cl_large;

            mcOptions.styles = clusterOptions;
        }
        return mcOptions;
    };

    BNIMap.prototype.autoSearch = function(text) {
        locationSet = true;
        this.search(text);
    };

    BNIMap.prototype.search = function(text) {

        if (text == "") { _func_searchResult("BNI_NO_SEARCH_TEXT");}
        var highestPoint;
        //var bounds = new google.maps.LatLngBounds();

        if (_o_search_suffix != "") {
            text += ', ' + _o_search_suffix;
        }

        var params = [];
        params['address'] = text;
        params['bounds'] = webSiteBounds;
        if (pageCountryCode) {
            params['componentRestrictions'] = {
                "country" : pageCountryCode
            };
        }
        if (_o_region_bias != null && _o_region_bias != '') params['region'] = _o_region_bias;

        geocoder.geocode(params, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {

                var latitude = results[0].geometry.location.lat();
                var longitude = results[0].geometry.location.lng();

                var highestCurrentScore = 0;
                for (var i =0; i<results.length; i++) {
                    var result = results[i];
                    var currentScore = 0;
                    if (_f_debug) {console.log(" ##### Result " + i + " ###");}
                    if (typeof result.types == null) continue;

                    if (_f_debug) {
                        console.log(JSON.stringify(results));
                        console.log("highestCurrentScore = " + highestCurrentScore);
                    }

                    for (var pt in result.types) {
                        if (_google_weights[result.types[pt]] != null) {
                            currentScore += _google_weights[result.types[pt]];
                        }
                        if (_f_debug) {console.log(i + "> " + result.types[pt] + " " + _google_weights[result.types[pt]] + ">> Current Score : " + currentScore); }
                    }
                    if (_f_debug) {
                        console.log(i + "> currentScore = " + currentScore);
                        console.log(i + "> viewport = " + (result.geometry.viewport != null));
                    }
                    if (highestCurrentScore <= currentScore && result.geometry.viewport != null) {
                        if (_f_debug) {
                            console.log(i + "> " + "webSiteBounds exist = " + (webSiteBounds != null));
                        }
                        if (webSiteBounds != null) {

                            //result is calculated from the text passed, whereas webSiteBounds remains constant withn the search of a country/region site setting the bounds
                            if (webSiteBounds.intersects(result.geometry.viewport)) {
                                if (_f_debug) {
                                    console.log(i + "> webSiteBounds = Intersects");
                                }
                                highestCurrentScore = currentScore;
                                highestPoint = result;
                            } else {
                                if (_f_debug) {
                                    console.log(i + "> webSiteBounds = No Intersection");
                                }
                            }
                        } else {
                            highestCurrentScore = currentScore;
                            highestPoint = result;
                        }

                    }

                    if (_f_debug) {
                        console.log(" #### End of result " + i + " ###");
                    }
                }

                if (highestPoint != null) {
                    map.setCenter(highestPoint.geometry.location);
                    map.setZoom(getZoomByBounds( highestPoint.geometry.viewport));
                    if (_f_debug) console.log("BNI_RESULT_FOUND");
                    _func_searchResult("BNI_RESULT_FOUND");


                } else if (results.length > 0) {
                    if (_f_debug) console.log("BNI_OUT_OF_SCOPE");
                    _func_searchResult("BNI_OUT_OF_SCOPE");

                } else {
                    if (_f_debug) console.log("BNI_ZERO_RESULTS");
                    _func_searchResult("BNI_ZERO_RESULTS");

                }

            } else {
                // Google says no results
                if (_f_debug) console.log("BNI_ZERO_RESULTS");
                _func_searchResult("BNI_ZERO_RESULTS");

            }

        });

    };


    /*
     * Reset the google map for this website.
     */
    BNIMap.prototype.reset = function() {
        map.setCenter(webSiteBounds.getCenter());
        map.setZoom(getZoomByBounds(webSiteBounds));
    };

    getFurtherDetails = function (cmsSecurityHash, cmsSecurityHash) {
        $.ajax({
            async: false,
            url:_m_bni_api_chapterinfo + "?encodedChapterId=" + cmsSecurityHash+"&locale="+_o_locale,
            type: "GET",
            dataType: "json",
            success: function(data) {
                var template = $('#template').html();
                data.cmsSecurityHash=cmsSecurityHash;
                data.encodedName=escape(data.content.chapterDetails.name);
                Mustache.parse(template);   // optional, speeds up future uses
                if(data.content.orgType=="CHAPTER"){
                    data.classForOrgType = "chapter";
                    data.orgTypeURL = "chapterdetail";
                }
                else if (data.content.orgType == "PLANNED_CHAPTER") {
                    data.classForOrgType = "plannedGroup";
                    data.orgTypeURL = "chapterdetail";
                }
                else if(data.content.orgType=="CORE_GROUP"){
                    data.classForOrgType = "coreGroup";
                    data.orgTypeURL = "coregroupdetail";
                }

                if(data.content.chapterDetails.addressLine2 && data.content.chapterDetails.city){
                    data.addressVsCity = data.content.chapterDetails.addressLine2 + ", " + data.content.chapterDetails.city;

                } else if(!data.content.chapterDetails.city && data.content.chapterDetails.addressLine2){
                    data.addressVsCity = data.content.chapterDetails.addressLine2 ;
                } else if(!data.content.chapterDetails.addressLine2 && data.content.chapterDetails.city){
                    data.addressVsCity = data.content.chapterDetails.city ;
                }
                if (data.content.orgType == "PLANNED_CHAPTER"){
                    data.content.isPlannedChapter=true;
                }
                var rendered = Mustache.render(template, data);
                $(".scrollArea").append(rendered);
            }
        });

    };

    /*
       * Render the google map for this website. This should never be called externally.
       */
    BNIMap.prototype.render = function(that) {

        webSiteBounds =  new google.maps.LatLngBounds();
        var ready = false;
        var reference = google.maps.event.addListener(map,'projection_changed',function(){
            if(!locationSet) {
                setTimeout(function() { map.setZoom(getZoomByBounds(webSiteBounds));}, 600);
            }
            google.maps.event.removeListener(reference);
        });
        $.ajax({
            async: false,
            url:  '/web/open/getMapData',
            type: "GET",
            dataType: "json",
            data: {orgIds: _m_org_ids, domain: _m_website_domain, localeString: _o_locale, hideFileExtension:"true", cmsv3 : "true", isOldVersion: "false"},
            success: function(data) {

                var countryCodes = [];
                markers = [];
                $.each(data, function (index, response) {
                    $.each(response, function (index, location) {
                        if (location.countryCode && !countryCodes.includes(location.countryCode)) {
                            countryCodes.push(location.countryCode);
                        }

                        if (typeof location.coordinates == null) return true;
                        var marker = that.getMarker(location);

                        webSiteBounds.extend(marker.position);

                        markerInfowindow=null;
                        google.maps.event.addListener(marker, 'click', (function() {
                            return function(){
                                $(".scrollArea").empty();
                                getFurtherDetails(location.cmsSecurityHash, location.cmsSecurityHash);

                            };
                        })(marker));
                        markers.push(marker);
                    });
                });

                // If there is one and only one country code then use it, otherwise don't
                if (countryCodes.length === 1) {
                    pageCountryCode = countryCodes[0];
                } else {
                    console.log('Expected a single country code but found', countryCodes);
                }

                var markerCluster = new MarkerClusterer(map,markers, that.getClusterMarkers());
                map.setCenter(webSiteBounds.getCenter());

                google.maps.event.addListener(map,'projection_changed',function(){
                    map.setZoom(getZoomByBounds(webSiteBounds));
                });

                markerCluster.onMaxZoom = function(mc) {
                    return multiChoice(mc, markerInfowindow, map);
                };

                markerCluster.onMarkersInSamePlace = function(mc) {
                    return multiChoice(mc, markerInfowindow, map);
                };

                if (_f_debug) {
                    console.log("NE LAT : " + webSiteBounds.getNorthEast().lat());
                    console.log("NE LNG : " + webSiteBounds.getNorthEast().lng());
                    console.log("SW LAT : " + webSiteBounds.getSouthWest().lat());
                    console.log("SW LNG : " + webSiteBounds.getSouthWest().lng());
                    console.log("Param : " + webSiteBounds.getSouthWest().lat() + "," + webSiteBounds.getSouthWest().lng()
                        + "|" + webSiteBounds.getNorthEast().lat() + "," + webSiteBounds.getNorthEast().lng());
                }




            }
        });
    };

    function getWidth(html) {

        // Create temporary div off to the side, containing point.windowHtml:
        var tempDiv = $('<div>' + html + '</div>')
            .css({marginLeft: '-9999px', position: 'absolute'})
            .appendTo($('body'));

        var width = tempDiv.width();
        tempDiv.remove();

        return width > 400 ? 400 : width;
    }


    /*
     * Render the multi cluster information window.
     */

    function multiChoice(cluster, markerInfowindow, map) {

        var markers = cluster.getMarkers();
        $(".scrollArea").empty();
        for(var i = 0; i < markers.length; i++) {
            $("#helpText").remove();
            getFurtherDetails(markers[i].cmsSecurityHash, markers[i].cmsSecurityHash);
        }
        markerClicked = true;
    }

    /*
     * determine the zoom level for a map that has all cluster and points rendered on it.
     */
    function getZoomByBounds(bounds ){
        var MAX_ZOOM = map.mapTypes.get(  map.getMapTypeId() ).maxZoom || 21 ;
        var MIN_ZOOM = map.mapTypes.get(  map.getMapTypeId() ).minZoom || 0 ;

        var ne= map.getProjection().fromLatLngToPoint( bounds.getNorthEast() );
        var sw= map.getProjection().fromLatLngToPoint( bounds.getSouthWest() );

        var worldCoordWidth = Math.abs(ne.x-sw.x);
        var worldCoordHeight = Math.abs(ne.y-sw.y);

        //Fit padding in pixels
        var FIT_PAD = 40;

        for( var zoom = MAX_ZOOM; zoom >= MIN_ZOOM; --zoom ){
            if( worldCoordWidth*(1<<zoom)+2*FIT_PAD < $(map.getDiv()).width() &&
                worldCoordHeight*(1<<zoom)+2*FIT_PAD < $(map.getDiv()).height() ) {
                return zoom;
            }
        }
        return 0;
    }

    /*
     * Parse a parameter
     */
    function param(opts, key, def, mandatory) {
        if (opts[key] == null && mandatory) {
            alert ("BNIMap '" + key + "' is Mandatory");
            return;
        }
        return opts[key] == null ? def :opts[key] ;
    }

    /*
     * Parse a parameter
     */
    function param_cluster (opts, key) {
        if (opts[key] == null) return null;
        var result = {};
        result["opt_textColor"] = 'black'; //param (opts[key], "opt_textColor", null, true);
        result["url"] = param (opts[key], "url", null, true);
        result["height"] = param (opts[key], "height", null, true);
        result["width"] = param (opts[key], "width", null, true);
        return result;
    }


    // Main

    if (_f_debug) {
        this.log();
    }

    var mapOptions = {
        center: new google.maps.LatLng(-59.23867641486403, -104.68956385000001),
        zoom: 5,
        mapTypeId: google.maps.MapTypeId.ROADMAP ,
        styles:[
            {
                featureType: "poi",
                stylers:[ {
                    visibility: "off"
                } ]
            }
        ]
    };
    map = new google.maps.Map(document.getElementById(_m_div_id), mapOptions);
    this.render(this);
    this.loadWeights();

}
