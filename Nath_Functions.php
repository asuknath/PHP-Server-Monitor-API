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


if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.7 !");
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    // if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
    // (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
    require_once '../src/includes/password_compatibility_library.inc.php';
}

$dbprifix='';

class DB_Functions {
    private $db;
    function __construct() {
        require_once '../config.php';
        require_once 'Nath_Connect.php';
        // connecting to database
        $this->db = new DB_Connect();
        $this->db->connect();
    }
    // destructor
    function __destruct() {
    }

    /**
     * Password reset code
     * @return type
     */
public function random_string(){
    $character_set_array = array();
    $character_set_array[] = array('count' => 7, 'characters' => 'abcdefghijklmnopqrstuvwxyz');
    $character_set_array[] = array('count' => 1, 'characters' => '0123456789');
    $temp_array = array();
    foreach ($character_set_array as $character_set) {
        for ($i = 0; $i < $character_set['count']; $i++) {
            $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
        }
    }
    shuffle($temp_array);
    return implode('', $temp_array);
}

/**
 * Get User's Servers List by User ID
 * @param type $user_id
 * @return boolean
 */
public function getServerlistbyUserID($user_id) {
    $dbprefix = $this->db =PSM_DB_PREFIX;
    $servers = 'servers';
    $users_servers = 'users_servers';

    $r = mysql_query("SELECT a.server_id, a.ip, a.port, a.label, a.type, a.status, a.last_online, a.last_check, a.active,a.email, a.pushover, a.warning_threshold, a.warning_threshold_counter, b.server_id, b.user_id FROM $dbprefix$servers a, $dbprefix$users_servers b WHERE b.user_id='$user_id' AND a.server_id=b.server_id");
    // check for result
    $no_of_rows = mysql_num_rows($r);
    if ($no_of_rows > 0) {
        $result = array();
        while ($row = mysql_fetch_assoc($r)) {
            $result[] = $row;
        }
        return $result;
    } else {
        // Servers not found
        return false;
    }
}

/**
 * Get Monitoring Dashboard
 * @param type $user_id
 * @return boolean
 */
public function getMonitorStatusByUserID($user_id) {
    $dbprefix = $this->db =PSM_DB_PREFIX;
    $servers = 'servers';
    $users_servers = 'users_servers';

    $r = mysql_query("SELECT COUNT(a.server_id) as servercount, count(if(a.status = 'on', a.status, NULL))
 as statusoncount, count(if(a.status = 'off', a.status, NULL))
 as statusoffcount, count(if(a.active = 'no', a.active, NULL))
 as activecount, count(if(a.email = 'yes', a.email, NULL))
 as emailalertcount, b.server_id, b.user_id FROM $dbprefix$servers a, $dbprefix$users_servers b WHERE b.user_id='$user_id' AND a.server_id=b.server_id");
    // check for result
    $no_of_rows = mysql_num_rows($r);
    if ($no_of_rows > 0) {
        // Status found
        $result = mysql_fetch_array($r);
        return $result;
    } else {
        // Status not found
        return false;
    }
}

/**
 * Get Server's Uptime by Server ID
 * @param type $server_id
 * @param type $HoursUnit
 * @return boolean
 */
public function getServerUptime($server_id, $HoursUnit) {
    $dbprefix = $this->db =PSM_DB_PREFIX;
    $servers_uptime = 'servers_uptime';
    if($HoursUnit <= 1){
        $r = mysql_query("SELECT servers_uptime_id, server_id, date, status, latency FROM $dbprefix$servers_uptime WHERE date >=(NOW() - INTERVAL '$HoursUnit' HOUR) AND (server_id='$server_id')");
    }else{
        $r = mysql_query("SELECT servers_uptime_id, server_id, date, status, AVG(latency) as latency FROM $dbprefix$servers_uptime WHERE date >=(NOW() - INTERVAL '$HoursUnit' HOUR) AND (server_id='$server_id') GROUP BY DATE(date), HOUR(date)");
    }

    // check for result
    $no_of_rows = mysql_num_rows($r);
    if ($no_of_rows > 0) {
        $result = array();
        while ($row = mysql_fetch_assoc($r)) {
            $result[] = $row;
        }
        return $result;
    } else {
        // Uptime records not found
        return false;
    }
}


/**
 *  Get Server's Details
 * @param type $server_id
 * @return boolean
 */
public function getServer($server_id) {
    $dbprefix = $this->db =PSM_DB_PREFIX;
    $servers = 'servers';
    
    $result = mysql_query("SELECT * FROM $dbprefix$servers WHERE server_id='$server_id'") or die(mysql_error());
    // check for result
    $no_of_rows = mysql_num_rows($result);
    if ($no_of_rows > 0) {
        $result = mysql_fetch_array($result);
        return $result;
    } else {
        // Server not found
        return false;
    }
}

/**
 * Get Server's Logs by Server ID
 * @param type $server_id
 * @param type $days
 * @return boolean
 */
public function getServerLogs($server_id, $days) {
    $dbprefix = $this->db =PSM_DB_PREFIX;
    $serverlog = 'log';
    
    $r = mysql_query("SELECT type, message, datetime FROM $dbprefix$serverlog WHERE DATE(datetime) > (NOW() - INTERVAL '$days' DAY) AND (server_id='$server_id' AND type='status')");
    // check for result
    $no_of_rows = mysql_num_rows($r);
    if ($no_of_rows > 0) {
        $result = array();
        while ($row = mysql_fetch_assoc($r)) {
            $result[] = $row;
        }
        return $result;
    } else {
        // Logs not found
        return false;
    }
}


/**
 * Add Server to Monitor
 * @param type $user_id
 * @param type $ip
 * @param type $port
 * @param type $label
 * @param type $type
 * @param type $status
 * @param type $active
 * @param type $emailalert
 * @param type $warning_threshold
 * @param type $timeout
 * @return boolean
 */
 public function addservertoMonitor($user_id, $ip, $port, $label, $type, $status, $active, $emailalert, $warning_threshold, $timeout) {
   $dbprefix = $this->db =PSM_DB_PREFIX;
   $servers = 'servers';
   $users_servers = 'users_servers';
   
   // Insert Server's Details 
   $result = mysql_query("INSERT INTO $dbprefix$servers (ip, port, label, type, status, active, email, warning_threshold, timeout) VALUES('$ip', '$port', '$label', '$type', '$status', '$active', '$emailalert', '$warning_threshold', '$timeout')");
    // check for successful store
    if ($result) {
        // Insert Server ID and User ID
        $server_id = mysql_insert_id(); // last inserted id
        $result1 = mysql_query("INSERT INTO $dbprefix$users_servers (user_id, server_id) VALUES('$user_id', '$server_id')");
        // Check result
        if ($result1) {
            return $result1;
        }else{
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Update Server to Monitor
 * @param type $user_id
 * @param type $ip
 * @param type $port
 * @param type $label
 * @param type $type
 * @param type $status
 * @param type $active
 * @param type $emailalert
 * @param type $warning_threshold
 * @param type $timeout
 * @param type $server_id
 * @return boolean
 */
 public function updateservertoMonitor($user_id, $ip, $port, $label, $type, $status, $active, $emailalert, $warning_threshold, $timeout, $server_id) { 
    $dbprefix = $this->db =PSM_DB_PREFIX;
    $servers = 'servers';
    $result = mysql_query("UPDATE $dbprefix$servers SET ip = '$ip', port = '$port', label='$label', type='$type', status='$status', active='$active', email='$emailalert', warning_threshold='$warning_threshold', timeout='$timeout' WHERE server_id = '$server_id'");
    // check for successful store
        if ($result) {
            return true;
        } else {
            return false;
        }
}

/**
 * Delete Server to Monitor
 * @param type $server_id
 * @return boolean
 */
 public function deleteservertoMonitor($server_id) { 
    $dbprefix = $this->db =PSM_DB_PREFIX;
    $servers = 'servers';
    $users_servers = 'users_servers';
    $servers_uptime = 'servers_uptime';
    $serverlog = 'log';
    
    $result = mysql_query("DELETE FROM $dbprefix$servers WHERE server_id = '$server_id'");
    // check for successful Delete
        if ($result) {
             $resuldeluserserver = mysql_query("DELETE FROM $dbprefix$users_servers WHERE server_id = '$server_id'");
             $resuldelUptime = mysql_query("DELETE FROM $dbprefix$servers_uptime WHERE server_id = '$server_id'");
             $resuldelLog = mysql_query("DELETE FROM $dbprefix$serverlog WHERE server_id = '$server_id'");
             return true;
        } else {
            return false;
        }
}

 /*
 * Check Server ID existed or not*
 * @param type $server_id
 * @return boolean
 */
         
public function isServerIDExisted($server_id) {
    $dbprefix = $this->db =PSM_DB_PREFIX;
    $servers = 'servers';
    
    $result = mysql_query("SELECT server_id from $dbprefix$servers WHERE server_id = '$server_id'");
    $no_of_rows = mysql_num_rows($result);
    if ($no_of_rows > 0) {
        // Server existed
        return true;
    } else {
        // Server not existed
        return false;
    }
}

/**
 * Login using Email and Password
 * @param type $email
 * @param type $app_password
 * @return boolean
 */
public function loginWithPostData($email, $app_password) {
    $dbprefix = $this->db =PSM_DB_PREFIX;
    $users = 'users';
    $result = mysql_query("SELECT * from $dbprefix$users WHERE email = '$email'");
    $no_of_rows = mysql_num_rows($result);
    if ($no_of_rows > 0) {
        $result = mysql_fetch_array($result);
        $hash_password = $result['password'];
            if(password_verify($app_password, $hash_password)) {
                return $result;
            }else{
                return false;
            }
    } else {
        // user not Found
        return false;
    }
    }

/**
 * Update iPhone Device Token and phone type in the Database
 * @param type $email
 * @param type $devicetoken
 * @param type $phone_type
 * @return boolean
 */    
public function iphoneDeviceToken($email, $devicetoken, $phone_type) {
        $dbprefix = $this->db =PSM_DB_PREFIX;
        $users = 'users';
        $result = mysql_query("UPDATE $dbprefix$users SET pushover_device = '$phone_type', pushover_key = '$devicetoken'
                          WHERE email = '$email'");
        if ($result) {
            return true;
        }else{
            return false;
        }
    }

}
?>