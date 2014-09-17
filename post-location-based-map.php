<?php
/*
Plugin Name:  post-location-based-map
Plugin URI:   http://www.jlsoft.de/
Description:  Displays a Google Map including markers for every location found in a post. Just add [post-location-based-map] to each page/post you want to show the map.
Version:      0.1
License:      GPLv2 or later
Author:       Jan Loeffler
Author URI:   http://www.jlsoft.de/
Author Email: mail@jlsoft.de
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Online: http://www.gnu.org/licenses/gpl.txt
*/

// Add a callback to WordPress to execute our function render_map() when a page is rendered
add_shortcode( 'post-location-based-map', 'generate_google_maps_code' );

// Use [post-location-based-map] in every post or page where to show the Google map. All posts with geo_latitute/geo_longitute values will be displayed as markers.
// Set Google markers
// var marker = new google.maps.Marker({
//     position: new google.maps.LatLng(lat_value,lng_value);,
//     title:"Hello World!"
// });
function generate_google_maps_code( $args ) {
    // your google maps api key
    $google_api_key = get_option( 'jlsoft-google-api-key', 'AIzaSyAGpiaemBBEpix7nJMru_kVjuECup6cGso' );

    // generate markers
    $markers = generate_markers();

    wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $google_api_key . '&sensor=false&extension=.js' );
    wp_enqueue_script( 'google-maps-code', plugins_url( 'google-maps-code.js', __FILE__ ) );
    wp_localize_script( 'google-maps-code', 'locations', array(
         'markers' => $markers
    ) );

    return '<div id="map_canvas" style="width: 570px; height: 500px; z-index:100;"></div>';
}

// List markers like:
// [['Title 1', 48.99764529999999, 8.3774976, 'http://domain.tld/link_to_post1'],['Title 2', 48.99764529999999, 8.3774976, 'http://domain.tld/link_to_post2']]
function generate_markers() {
    $i          = 0;
    $item_count = 0;
    $updated    = 0;
    $markers    = array();
    $args       = array(
         'post_type' => 'post',
        'nopaging' => 'true'
    );

    // create a new WordPress query as subloop
    $post_query = new WP_Query( $args );

    // interate through all posts
    while ( $post_query->have_posts() ) {

        // get the next post
        $post_query->the_post();
        $post_id       = get_the_ID();
        $post_url      = get_permalink( $post_id );
        $geo_latitude  = "";
        $geo_longitude = "";
        $i++;

        // read custom field "geo_latitude"
        $geo_latitude = get_post_meta( $post_id, 'geo_latitude', true );
        if ( $geo_latitude ) {
            // read custom field "geo_longitude"
            $geo_longitude = get_post_meta( $post_id, 'geo_longitude', true );
        } else {
            // read custom field "geo_longitude"
            $location = get_post_meta( $post_id, 'location', true );
            if ( $location ) {
                $location    = urlencode( $location );
                $request_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=" . $location . "&sensor=true";
                try {
                    $xml = simplexml_load_file( $request_url ) or die( "url not loading" );
                    $status = $xml->status;
                    if ( $status == "OK" ) {
                        $geo_latitude  = $xml->result->geometry->location->lat;
                        $geo_longitude = $xml->result->geometry->location->lng;

                        // update post and add geo_location
                        echo '<!--[' . $i . '] Update post ' . $post_id . ' (' . get_the_title() . ') Location: ' . $location . ' (' . $geo_latitude . ',' . $geo_longitude . ")-->\n";
                        update_post_meta( $post_id, 'geo_latitude', (string) $geo_latitude );
                        update_post_meta( $post_id, 'geo_longitude', (string) $geo_longitude );
                        $updated++;
                    } else {
                        echo '<!--[' . $i . '] Error at post ' . $post_id . ' (' . get_the_title() . ') Location: ' . $location . ':' . $status . "-->\n";
                    }
                }
                catch ( Exception $e ) {
                    echo '<!--[' . $i . '] Exception at post ' . $post_id . ' (' . get_the_title() . ') Location: ' . $location . ' (' . $geo_latitude . ',' . $geo_longitude . '): ', $e->getMessage(), "-->\n";
                }
            }
        }

        if ( $geo_latitude && $geo_latitude ) {
            $markers[] = array(
                 htmlspecialchars_decode( urldecode( get_the_title() ) ),
                $geo_latitude,
                $geo_longitude,
                $post_url
            );
            $item_count++;
        }
    }

    echo "<!--Posts total: " . $i . "-->\n";
    echo "<!--Posts with location: " . $item_count . "-->\n";
    echo "<!--Posts updated: " . $updated . "-->\n";

    wp_reset_postdata();

    return $markers;
}

?>