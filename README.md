# PHP-Server-Monitor-API
PHP Server Monitor API for IOS (iPhone &amp; iPad) and Android



**Login API:**

/api/userapi.php?tag=login&email=email@yourdomain.com&app_password=USER_PASS&phone_type=iPhoneORAndroid&devicetoken=xxxxxxxxxx

**Fields Require:**
Tag = login
email = Your PHP Server Monitor user's email address  
app_password = PHP Server Monitor user's password
phone_type = If you are using IOS devices use iPhone and for Android use Android
devicetoken = Get the Device Token for push notification service.

**Response:   **
```json
{
  tag: "login",
  success: 1,
  user: {
	user_id: "2",
	name: "User Name",
	mobile: "23121212121",
	email: "email@domain.com"
	}
}
```


**Get All Servers List:**

/api/monitorapi.php?tag=serverlist&email=email@domain.com&app_password=USER_PASS&user_id=1

**Fields Require:**
Tag = serverlist
email = Your PHP Server Monitor user's email address  
app_password = PHP Server Monitor user's password
user_id = PHP Server Monitor user's ID

**Response:**
```json
{
  tag: "serverlist",
  success: 1,
  server: [
	{
	server_id: "1",
	ip: "https://hostfav.com",
	port: "80",
	label: "Source Forge",
	type: "website",
	status: "on",
	last_online: "2016-03-21 13:10:02",
	last_check: "2016-03-21 13:10:02",
	active: "yes",
	email: "yes",
	pushover: "yes",
	warning_threshold: "1",
	warning_threshold_counter: "0",
	user_id: "2"

	}
	]
}
```



**Get Monitor's Uptime:**

/api/monitorapi.php?tag=serveruptime&email=email@domain.com&app_password=USER_PASS&server_id=6&HoursUnit=25

**Fields Require:**
Tag = serveruptime
email = Your PHP Server Monitor user's email address  
app_password = PHP Server Monitor user's password
server_id = Monitor's ID
HoursUnit = Number or hours


**Response:**
```json
{
  tag: "serveruptime",
  success: 1,
  average_latency: 0.0018558051161538,
  uptime: 100,
server: [
	{
	servers_uptime_id: "1973944",
	server_id: "79",
	date: "2016-07-11 10:50:02",
	status: "1",
	latency: "0.00042489999"
	},
	{
	servers_uptime_id: "1975137",
	server_id: "79",
	date: "2016-07-11 11:50:03",
	status: "1",
	latency: "0.00038028333"
	}
    	]
}
```

**Get Monitor's Log**

/api/monitorapi.php?tag=serverlogs&email=email@domain.com&app_password=USER_PASS&server_id=79&days=7

**Fields Require:**
Tag = serverlogs
email = Your PHP Server Monitor user's email address  
app_password = PHP Server Monitor user's password
server_id = Monitor's ID
days = Number of days log.

**Response:**
```json
{
  tag: "serverlogs",
  success: 1,
  server: [
	{
	type: "status",
	message: "Server 'srv01.yourdomain.com' is RUNNING: ip=srv01.yourdomain.com, port=1",
	datetime: "2016-07-10 13:40:02"
	},
	{
	type: "status",
	message: "Server 'srv01.yourdomain.com' is DOWN: ip=srv01.yourdomain.com, port=1. Error=",
	datetime: "2016-07-10 13:30:15"
	}
	]
}
```

**Add/Update Monitor:**

/api/monitorapi.php?tag=addupdateserver&user_id=3&email=email@domain.com&app_password=USER_PASS&ip=x.x.x.x&port=80&label=Serveralarms.com&type=service&status=off&active=yes&emailalert=yes&warning_threshold=60&timeout=60&server_id=

**Fields Require:**
Tag = addupdateserver
email = Your PHP Server Monitor user's email address  
app_password = PHP Server Monitor user's password
ip = Monitor's IP address
port = Monitor's TCP/UDP Port number
label = Monitor's Name
type = Website, Service or Ping
status = Monitor's Status
active = Monitor Active/Inavtive
emailalert = Enable/Disable Email Alert
warning_threshold= Warning Threshold
timeout= Timeout
server_id = If you are adding new server server_id filed send blank.

**Response:**
```json
{
  tag: "addupdateserver",
  success: 1
}
```

**Delete Monitor:**

https://serveralarms.com/monitor/api/monitorapi.php?tag=deleteserver&email=email@domain.com&app_password=USER_PASS&server_id=

**Fields Require:**
Tag = addupdateserver
email = Your PHP Server Monitor user's email address  
app_password = PHP Server Monitor user's password
server_id = Monitor's ID

**Response:**
```json
{
	tag: "deleteserver",
	success: 1
}
```
