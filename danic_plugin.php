<?php
/*
 * Plugin Name: Day and Night Image Cycle
 * Description: With this plugin you can easily add a picture that changes depending on whether it is day or night time
 * Version: 1.0.0
 * Author: Motyldrogi
 * Author URI: https://github.com/Motyldrogi
 */


//---------------------------------------------------------------------------------------------------------------
//------------ Activate ---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------

function danic_activation()
{
    flush_rewrite_rules();
    update_option( 'stz', '+0', '', 'yes' );
    update_option( 'dlst', '1', '', 'yes' );
    update_option( 'dstart', '7', '', 'yes' );
    update_option( 'dend', '20', '', 'yes' );
    update_option( 'norapi', '0', '', 'yes' );
    update_option( 'timezone_error', '0', '', 'yes' );
}
register_activation_hook( __FILE__, 'danic_activation' ); 

//---------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------


//---------------------------------------------------------------------------------------------------------------------
//------------ Add Menu Option ----------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------

function danic_menu() {
    add_options_page('DaNIC', 'DaNIC', 'administrator', 'danic', 'danic_options');
}
add_action('admin_menu', 'danic_menu');

//---------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------


//----------------------------------------------------------------------------------------------------------------------
//------------ Add Options Page ----------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------

function danic_options() {
    wp_register_style('danic_style', plugins_url('danic_style.css',__FILE__ ));
    wp_enqueue_style('danic_style');

    // -- Timezone
    if (isset($_POST['stz'])) {
        update_option('stz', sanitize_text_field($_POST['stz']));
        $timez = sanitize_text_field($_POST['stz']);
    } 
    	$timez = get_option('stz', '0');
    // -- Daylight Saving Time
        if (isset($_POST['dlst'])) {
        update_option('dlst', $_POST['dlst']);
        $dlst = $_POST['dlst'];
    } 
        $dlst = get_option('dlst', '0');
    // -- CustomOrApi
        if (isset($_POST['norapi'])) {
        update_option('norapi', $_POST['norapi']);
        $norapi = $_POST['norapi'];
    } 
        $norapi = get_option('norapi', '0');

    if ($norapi == 0) {
        // -- Day Start
        if (isset($_POST['dstart'])) {
            update_option('dstart', sanitize_text_field($_POST['dstart']));
            $dstart = sanitize_text_field($_POST['dstart']);
        } 
        $dstart = get_option('dstart', '7');
        // -- Day End
        if (isset($_POST['dend'])) {
            update_option('dend', sanitize_text_field($_POST['dend']));
            $dend = sanitize_text_field($_POST['dend']);
        } 
        $dend = get_option('dend', '20');
    }
    if ($norapi == 1) {
        // -- Latitude
        if (isset($_POST['latitude'])) {
            update_option('latitude', sanitize_text_field($_POST['latitude']));
            $latitude = sanitize_text_field($_POST['latitude']);
        } 
        $latitude = get_option('latitude', '0');
        // -- Longitude
        if (isset($_POST['longitude'])) {
            update_option('longitude', sanitize_text_field($_POST['longitude']));
            $longitude = sanitize_text_field($_POST['longitude']);
        } 
        $longitude = get_option('longitude', '0');
        // -- Get Start and End
        getSunriseSunset();
        $dapistart = get_option('dapistart', '0');
        $dapiend = get_option('dapiend', '0');
    }
    // -- Get Time
    $time = date('H') + $timez;
    if ($dlst == 1) {
        $time = $time + 1;
    }
    // -- Output Form and Text
    echo '<div id="danic_options"><h1>Day and Night Image Cycle</h1>
    <form method="POST">
        <div id="danic_head">
        <h3>How to use DaNIC?</h3>
            <p>The shortcode of this plugin is <b>[daynightc]</b>, you have <b>5</b> possible <b>parameters</b>.<br>
            You can add a class, width, height, a night image and a day image.<br>
            The parameters are: <b>class, width, height, nightimg and dayimg</b>, for example
            you can do the following:<br><br><b>[daynightc height="400px" width="100%" dayimg="Your Day Image Link" nightimg="Your Night Image Link"]</b></p>
            <div id="danic_current"><b>Current Settings:</b><br>
            <p>It is <b>'.$time.' o&#39;clock.</b> The day starts at <b>';if($norapi == 0) {echo $dstart;} else { echo $dapistart;} echo'</b> and ends at <b>'; if($norapi == 0) {echo $dend;} else { echo $dapiend;} echo'</b> o&#39;clock.</div>
        </div>
    <div id="danic_wrap">
    <h1>Options</h1>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="stz">Timezone</label>
                </th>
                <td>
                    <input type="text" name="stz" id="stz" value="';echo $timez; echo '">
                    <p>Current Timezone: ';echo '<b>UTC'.$timez.'</b></p>';
					$tzerror = get_option('timezone_error', '0');  if($tzerror == 1) { echo '
                    <p class="danic_error">Your current timezone is creating errors with your position or is not in range -10 to +12</p>'; }
                echo '</td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="dlst">Daylight Saving Time</label>
                </th>
                <td>
                    <select name="dlst" id="dlst">
                        <option value="1" ';if($dlst == 1) { echo 'selected="selected"';} echo'>Yes</option>
                        <option value="0" ';if($dlst == 0) { echo 'selected="selected"';} echo'>No</option>
                    </select>
                    <p>Daylight Saving Time: ';if($dlst == 1) {echo '<b>Yes</b>';} else {echo '<b>No</b>';}echo'</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <select name="norapi" id="norapi">
                    <option value="0" ';if($norapi == 0) { echo 'selected="selected"';} echo'>Custom</option>
                    <option value="1" ';if($norapi == 1) { echo 'selected="selected"';} echo'>Position</option>
                    </select><br>'; if($norapi == 0) { echo'
                    <label for="dstart">Day starts at</label>
                </th>
                <td>
                <br><br>
                    <input type="text" name="dstart" id="dstart" value="';echo $dstart; echo '">
                     <p>Day starts at ';echo '<b>'.$dstart.' o&#39;clock</b></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="dend">Day ends at</label>
                </th>
                <td>
                    <input type="text" name="dend" id="dend" value="';echo $dend; echo '">
                    <p>Day ends at <b>'.$dend.' o&#39;clock</b></p>
                </td>
            </tr>'; } else { echo'
            <tr>
                <th scope="row">
                    <label for="latitude">Latitude and Longitude</label>
                </th>
                <td>
                    <input type="text" name="latitude" id="latitude" value="';echo $latitude; echo '">
                    <input type="text" name="longitude" id="longitude" value="';echo $longitude; echo '">
                    <p>Latitude <b>'.$latitude.'</b> and Longitude <b>'.$longitude.'</b></p>
                    <p>Your day starts at <b>'.$dapistart.'</b> and ends at <b>'.$dapiend.'</b></p>
                    </td>
            </tr>'; } echo'
        </tbody>
    </table><br>
    <input type="submit" value="Save options" class="button button-primary button-large">
    </form></div></div>';
} 

//----------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------


//-------------------------------------------------------------------------------------------------------------------------------
//------------ Main Functions / Shortcode ----------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------------

function getSunriseSunset() {
    // -- Get Options
    $latitude = get_option('latitude');
    $longitude = get_option('longitude');
    $timez = get_option('stz');
    $dlst = get_option('dlst');

    // -- Build Link and get Content
    $link = 'https://api.sunrise-sunset.org/json?lat=';
    $link .= $latitude;
    $link .= '&lng=';
    $link .= $longitude;

    $json_ss = file_get_contents($link);

    // -- Get Sunrise Time from json
    $sunrise_prefix = 'sunrise":"';
    $sunrise_index = strpos($json_ss, $sunrise_prefix) + strlen($sunrise_prefix);
    $dapistart = substr($json_ss, $sunrise_index);
    $dapistart = $dapistart + $timez;
    if ($dapistart > 12) {
        $dapistart = $dapistart - 12;
    }

    if ($dlst == 1) {
        $dapistart = $dapistart + 1;
    }
    update_option('dapistart', $dapistart);

    // -- Get Sunset Time from json
    $sunset_prefix = 'sunset":"';
    $sunset_index = strpos($json_ss, $sunset_prefix) + strlen($sunset_prefix);
    $dapiend = substr($json_ss, $sunset_index);
    $dapiend = $dapiend + $timez;
    if ($dapiend > 24) {
        $dapiend = $dapiend - 12;
        update_option('timezone_error', '1');
    }
	else {
		update_option('timezone_error', '0');
	}

	if($timez > 12 || $timez < -10) {
		update_option('timezone_error', '1');
    }
	else {
		update_option('timezone_error', '0');
	}
	
    if ($dlst == 1) {
        $dapiend = $dapiend + 1;
    }
    update_option('dapiend', $dapiend);

}

function daynightc_func( $atts ) {
    // -- Get Options
    $timez = get_option('stz');
    $dlst = get_option('dlst');
    $norapi = get_option('norapi');

    // -- Check wheter API or Custom
    if($norapi == 0) {
        $dstart = get_option('dstart');
        $dend = get_option('dend');
    }
    elseif ($norapi == 1) {
        $dstart = get_option('dapistart');
        $dend = get_option('dapiend');
    }

    // -- Get Time
	$time = date('H') + $timez;
    if ($dlst == 1) {
        $time = $time + 1;
    }

    // -- Check Time and show Image
	$return = '<img class="'.$atts['class'].'" width="'.$atts['width'].'" height="'.$atts['height'].'" src="'.$atts['nightimg'].'" />';
	
	if ($time >= $dstart && $time <= $dend) {
        $return = '<img class="'.$atts['class'].'" width="'.$atts['width'].'" height="'.$atts['height'].'" src="'.$atts['dayimg'].'" />';
	}

    return $return;
}
add_shortcode( 'daynightc', 'daynightc_func' );

//-------------------------------------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------------


//----------------------------------------------------------------------------------------------------------------
//------------ Deactivate ----------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------

function danic_deactivation()
{
    remove_shortcode('daynightc');
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'danic_deactivation' ); 

//----------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------
?>