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
 * API to Get Monitors List, Monitor Uptime,  Monitor's Details, Alerts etc.
 * Add/Edit Monitor, Turn on/off Alerts
 * 
 * API will repsonse Success = 1 or Success =0
 * success = 1 is Successfull 
 * success = 0 is failed or no result
 * error_msg  - will give reason. Error message will show if failed. 
 **/

// Following 2 lines are for debug purpose.
/*
ini_set('display_errors', 'On');
error_reporting(E_ALL);
*/

// Verify Tag
 if (isset($_GET['tag']) && $_GET['tag'] != '') {
    // Get tag
    $tag = $_GET['tag'];
    //Include Database handler
    require_once 'Nath_Functions.php';
    $db = new DB_Functions();
    // response Array
    $response = array("tag" => $tag, "success" => 0);


    if ($tag == 'serverlist') {
        $email = $_GET['email'];
        $app_password = $_GET['app_password'];
        $user_id = $_GET['user_id']; 
        
        /*
        * Validate user using email and password
        * Get all List of servers using  User's ID
        * Remember we are not using username for authentication.
        * We are using email and password for authentication.
        */
        $user = $db->loginWithPostData($email, $app_password);
        if ($user != false) {
            /*
            * user found
            * Call Get Server List using user id
            * Only using user id to get server list. Becuase we have already validated user.
            */
            $servers = array();
            $servers = $db->getServerlistbyUserID($user_id);
            if ($servers != false) {
                $response["success"] = 1;
                $response["server"]=$servers;
                echo json_encode($response);
            }else{
                // Servers not found
                $response["success"] = 0;
                $response["error_msg"] = "You don't have Servers under Monitor.";
                echo json_encode($response);
            }    
        } else {
            // User not found
            // Incorrect email or password
            $response["success"] = 0;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
    /*
     * Dashboard API is designed to get overall status.
     * Dashboard will show - 
     * Total Number of Monitors
     * Total Up
     * Total Down
     * Total Alert Enable
     * Total Monitoring
     */
    }else if ($tag == 'dashboard') {
        $email = $_GET['email'];
        $app_password = $_GET['app_password'];
        $user_id = $_GET['user_id']; 
        // Validating user
        $user = $db->loginWithPostData($email, $app_password);
        if ($user != false) {
            /*
            * User found
            * Call Get overall status using User ID
            * Only using user id to get server status. Becuase we have already validated user.
            */
            $servers = $db->getMonitorStatusByUserID($user_id);
            if ($servers != false) {
                $response["success"] = 1;
                $response["server"]=$servers;
                echo json_encode($response);
            }else{
                // Server list not found
                $response["success"] = 0;
                $response["error_msg"] = "You don't have Servers under Monitor.";
                echo json_encode($response);
            }    
        } else {
            // user not found or wrong username/password
            $response["success"] = 0;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
    /*
     * Get List of server's log by Server ID
     */        
    }else if ($tag == 'serverlogs') {
        // Request type is check Login
        $email = $_GET['email'];
        $app_password = $_GET['app_password'];
        $server_id = $_GET['server_id']; 
        $days = $_GET['days'];
        // Validating user
        $user = $db->loginWithPostData($email, $app_password);
        if ($user != false) {
            // user found
            $logs = array();
            $logs = $db->getServerLogs($server_id, $days);
            if ($logs != false) {
                $response["success"] = 1;
                $response["server"]=$logs;
                echo json_encode($response);
            }else{
                // Server Log not found
                $response["success"] = 0;
                $response["error_msg"] = "Logs not found. Check back later.";
                echo json_encode($response);
            }    
        } else {
            // User not found
            // Incorrect email or password
            $response["success"] = 0;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
    /*
     * Server Uptime API 
     * You can use this API using Hours
     * 1 Hour, 24 Hours (1 day) 168 Hours (7 Days)
     * If this monitoring server is running cron job every minutes then 7 days will have too many records
     * I have minimized this total by average hourly latency
     * If you call this service for 1 hour it will show all latency.
     * if you call for more than 2 hours latency, it will show only hourly average. Total records will be 2
     * Example: You are running cron job every 10 minutes
     * If you call this API for 1 hour it will show 6 Records
     * If you call this API for 24 hours it will show 24 records
     */  
    }else if ($tag == 'serveruptime') {
        // Request type is check Server's Uptime
        $email = $_GET['email'];
        $app_password = $_GET['app_password'];
        $server_id = $_GET['server_id'];
        $HoursUnit = $_GET['HoursUnit'];
         // Validating user
        $user = $db->loginWithPostData($email, $app_password);
        if ($user != false) {
            // user found
            $uptime = array();
            // Get uptime by Server ID and Hours
            $uptime = $db->getServerUptime($server_id, $HoursUnit);
            if ($uptime != false) {
                $sum_latency=0;
                $count = count($uptime);
                $count_uptime_on =0;
                foreach ($uptime as $value) {
                    $sum_latency =$sum_latency + $value['latency'];
                    if ($value['status'] == '1') $count_uptime_on++;
                }
                $average_latency=($sum_latency/$count);
                $count_uptime_on = ($count_uptime_on/$count)*100;
                $response["success"] = 1;
                $response["average_latency"] = $average_latency;
                $response["uptime"] = $count_uptime_on;
                $response["server"]=$uptime;
                echo json_encode($response);
            }else{
                // Server Uptime records not found
                $response["success"] = 0;
                $response["error_msg"] = "You don't have Servers under Monitor.";
                echo json_encode($response);
            }    
        } else {
            // User not found
            // Incorrect email or password
            $response["success"] = 0;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
    /*
    * Add/update Server or Service to Monitoring
    * It will check Server ID in the Database. 
    * If found, this API will update otherwise add as a new monitor
    * We will be able add as Ping if you set type = service and port = 1
    * 
    * Check following mod
    * https://sourceforge.net/p/phpservermon/discussion/845823/thread/97e4fd03/?limit=25
    * 
    * To add website you need to supply type= website and domaing with http:// or https:// 
    * Very important to supply correct email address
    */       
    }else if ($tag == 'addupdateserver') {
        // Add new Server
        $user_id = $_GET['user_id'];
        $email = $_GET['email'];
        $app_password = $_GET['app_password'];
        $ip = $_GET['ip'];
        $port = $_GET['port'];
        $label = $_GET['label'];
        $type = $_GET['type'];
        $status = $_GET['status'];
        $active = $_GET['active'];
        $emailalert = $_GET['emailalert'];
        $warning_threshold = $_GET['warning_threshold'];
        $timeout = $_GET['timeout'];
        $server_id = $_GET['server_id'];
        // Validating user
        $user = $db->loginWithPostData($email, $app_password);
        if ($user != false) {
            if ($db->isServerIDExisted($server_id)) {
                // Update Server
                $server = $db->updateservertoMonitor($user_id, $ip, $port, $label, $type, $status, $active, $emailalert, $warning_threshold, $timeout, $server_id);
                if ($server != false) {
                    // Server updated successfully         
                    $response["success"] = 1;
                    echo json_encode($response);
                } else {
                    // user failed to store
                    $response["success"] = 0;
                    $response["error_msg"] = "Unable to update Server, Please try again later.";
                    echo json_encode($response);
                }
            }else{
                // Store New Server
                $server = $db->addservertoMonitor($user_id, $ip, $port, $label, $type, $status, $active, $emailalert, $warning_threshold, $timeout);
                if ($server != false) {
                    // Server added successfully         
                    $response["success"] = 1;
                    echo json_encode($response);
                } else {
                    // user failed to store
                    $response["success"] = 0;
                    $response["error_msg"] = "Unable to add Server, Please try again later.";
                    echo json_encode($response);
                }
            }

        } else {
            // User not found
            // Incorrect email or password
            $response["success"] = 0;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
                /*
         * Delete Server using server id
         */
        }else if ($tag == 'deleteserver') {
        // Delete Server
        $email = $_GET['email'];
        $app_password = $_GET['app_password'];
        $server_id = $_GET['server_id'];
        // check for user
        $user = $db->loginWithPostData($email, $app_password);
        if ($user != false) {
            $deleteServer = $db->deleteservertoMonitor($server_id);
                if($deleteServer != false) {
                    // Server Deleted successfully         
                    $response["success"] = 1;
                    echo json_encode($response);
                } else {
                    // user failed to store
                    $response["success"] = 0;
                    $response["error_msg"] = "Unable to Delete Server, Please try again later.";
                    echo json_encode($response);
                }
        } else {
            // Server not found
            // echo json with error = 1
            $response["success"] = 0;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
    /*
    * Get a Monitor's details by server id
    */
        
    }else if ($tag == 'getserver') {
        $email = $_GET['email'];
        $app_password = $_GET['app_password'];
        $user_id = $_GET['user_id'];
        $server_id = $_GET['server_id'];
        // Validating user
        $user = $db->loginWithPostData($email, $app_password);
        if ($user != false) {
            // Get Monitor's Details by server_id
            $server = $db->getServer($server_id);
            if ($server != false) {
                $response["success"] = 1;                        
                $response["server"]["server_id"] = $server["server_id"];
                $response["server"]["ip"] = $server["ip"];
                $response["server"]["port"] = $server["port"];
                $response["server"]["label"] = $server["label"];
                $response["server"]["type"] = $server["type"];
                $response["server"]["pattern"] = $server["pattern"];
                $response["server"]["status"] = $server["status"];
                $response["server"]["error"] = $server["error"];
                $response["server"]["rtime"] = $server["rtime"];
                $response["server"]["last_online"] = $server["last_online"];
                $response["server"]["last_check"] = $server["last_check"];
                $response["server"]["active"] = $server["active"];
                $response["server"]["email"] = $server["email"];
                $response["server"]["sms"] = $server["sms"];
                $response["server"]["pushover"] = $server["pushover"];
                $response["server"]["warning_threshold"] = $server["warning_threshold"];
                $response["server"]["warning_threshold_counter"] = $server["warning_threshold_counter"];
                $response["server"]["timeout"] = $server["timeout"];
                echo json_encode($response);
            } else {
                $response["success"] = 0;
                $response["error_msg"] = "Unable to get Server Details, Please try again later.";
                echo json_encode($response);
            }
        } else {
            // User not found
            // Incorrect email or password
            $response["success"] = 0;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
    //tag check else    
    } else {
         $response["success"] = 0;
         $response["error_msg"] = "JSON ERROR";
        echo json_encode($response);
    }
} else {
    echo "Server Alarms PHP Server Monitor API";
}
?>
