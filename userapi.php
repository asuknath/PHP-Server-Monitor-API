<?php
/**
 * PHP Server Monitor API for Web, IOS or Android 
 * Monitor your servers and websites.
 *
 * This file is part of PHP Server Monitor API.
 * PHP Server Monitor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHP Server Monitor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHP Server Monitor.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ServerAlarmsAPI
 * @author      Asuk Nath <support@serveralarms.com>
 * @copyright   Copyright (c) 2016 Asuk Nath <support@serveralarms.com>
 * @license     http://www.gnu.org/licenses/gpl.txt GNU GPL v3
 * @version     Release: v1.0
 * @link        https://www.serveralarms.com/
 **/


/**
 * API for Login, Register, Changepassword, Resetpassword Requests and for Email Notifications.
  **/

// Following 2 lines are for debug purpose.
/*
ini_set('display_errors', 'On');
error_reporting(E_ALL);
*/

/*
 * This API will Validate user by email and password 
 * We use this API for Login and Validate user for all API calls
 * 
 * This API will also update IOS device token and phone type.
 * 
 * Very important we are using 
 */

 if (isset($_GET['tag']) && $_GET['tag'] != '') {
    // Get tag
    $tag = $_GET['tag'];
    //Include Database handler
    require_once 'Nath_Functions.php';
    $db = new DB_Functions();
    // response Array
    $response = array("tag" => $tag, "success" => 0);
    // check for tag type
    if ($tag == 'login') {
        // Request type is check Login
        $email = $_GET['email'];
        $app_password = $_GET['app_password'];
        $devicetoken = $_GET['devicetoken'];
        $phone_type =$_GET['phone_type'];
            // check for user
            $user = $db->loginWithPostData($email, $app_password);
        if ($user != false) {
            
            /* 
             * Update IOS device Token for push notification
             * Need to modify src/psm/Util/Server/Updater/StatusNotifier.class.php class file for to trigger Push notificaton
             * I didn't modify Table. I used pushover_key for IOS Device Token/Android Device ID and pushover_device for detect iPhone/Android
             * If you are currently using Pushover servie please comment following line.
             */
            $updateiPhoneDeviceToken= $db->iphoneDeviceToken($email, $devicetoken, $phone_type);
            
            // user found
                $response["success"] = 1;
                $response["user"]["user_id"]= $user["user_id"];
                $response["user"]["name"]= $user["name"];
                $response["user"]["mobile"]= $user["mobile"];
                $response["user"]["email"]= $user["email"];
            echo json_encode($response);
        } else {
            // user not found
            $response["success"] = 0;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }       
    } else {
         $response["success"] = 0;
         $response["error_msg"] = "JSON ERROR";
        echo json_encode($response);
    }
} else {
    echo "Monitoring User API";
}
?>
