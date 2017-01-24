<?php
############################
# Written By: Haroon Ahmad #
# www.fastcreators.com    #
############################
#######################################################################
# It is application Model. It serves data requests to controllers
########################################################################


//load db interface as we need to implement it
if(file_exists(INCLUDE_PATH . "core/code/php/interfaces/db.interface.php")) {
  include_once INCLUDE_PATH . "core/code/php/interfaces/db.interface.php";
}

    //include_once INCLUDE_PATH . "core/config/settings.info.php";


/*class database implements dbInterface {*/
 class database {
  
//when user logs in a controller request, his status is stored in session BUT
//it cannot be used on the same page load as it needs a refresh for the Session
//array to take effect. Hence we use this variable for the same page load event.
protected $userSession = false;
public  $NEXT = "0";
public  $LAST = "0";
public  $PREV = "0";
public  $totalRecs = 0;
//connect to database
//different types of database handling can be used here
//PDO is a preferred one but for this project I have used MySQL
function _construct(){
    $this->NEXT = 0;
    //$this->connectDB(); //use this if working with database applications to get the connection handle ready
}
/*** will be changed to PDO ***/

function connectDB(){

global $dbHost,$dbName,$dbUser,$dbPassword,$dbType; //comes from settings.info.php file

  switch($dbType) {
	case "mysql":

	 $connID = mysql_connect($dbHost,$dbUser,$dbPassword) or die("cant connect".mysql_error());
	 mysql_select_db($dbName) or die(mysql_error());
	break;
	
	case "sql":
	
	break;
	
	case "mysqli":
	
	break;
	
  } //end switch 
  
  //return $connID;
}

public function getMenu($position,$opt='') {

    if($position == "header") {
        $SQL = "SELECT title,link FROM fc_menu WHERE position='header' ORDER BY rank ";
        $res = $this->executeQuery($SQL,'res');
            while($row=mysql_fetch_assoc($res)){
                $menu[] = $row;
            }
    }
    return $menu;
}
//handles user login request from controller
function loginUser($username,$password,$return=true) { 
  
  //purify form data
  if(!$username=$this->filterData("username",$username,"general","username is empty")) {
   $return=false;
   }
  if(!$username=$this->filterData("username",$username,"xss","username is invalid")) {
   $return=false;
   }
  if(!$password=$this->filterData("password",$password,"general","password is invalid")) {
   $return=false;
   }
   if(!$password=$this->filterData("password",$password,"xss","restricted characters in password")) {
   $return=false;
   }
   
   //if validation failed, abort further actions, tell controller to show error output
   //to the user by reading errors array
   
   if(!$return) {
    return false;
   }
   


   
  //make a md5 hash, as password is stored in md5 hash form in the database 
  #$password = md5($password);
  
  //knit query
  //get rid of single quotes to avoid MySQL Injection 
  $SQL = "SELECT staffID,rank,title FROM staff 
          WHERE
		    staffName='".mysql_real_escape_string($username)."'
		  AND
		    staffSecretCode='".mysql_real_escape_string($password)."'"
		  
		  ;
		  
		//echo $SQL;
		   
  $status = $this->executeQuery($SQL,'num');
  //echo "after status rows";
    $rankObj = $this->executeQuery($SQL,'get');
 // echo $status . "-----------------------";
   //var_dump($rankObj);
    //echo $rankObj->rank . "-rank";
  if($status) { 
    $_SESSION['userLogin'] = true; //login successful
    $this->userSession = true;
    $_SESSION['rank'] = $rankObj->rank;
    $_SESSION['staffID'] = $rankObj->staffID;
    $_SESSION['userTitle'] = $rankObj->title;
    
    $spyData = $this->getUserLoc($_SERVER['REMOTE_ADDR'],$username);
    //print_r($spyData);
    //echo $_SERVER['REMOTE_ADDR'] . " ;;;;;; ";
        $this->storeLoginActivity($spyData);
	return true;
  }
  else {
   $this->errors[] = "Authorization rejected. Please check your username and password";
   return false; //login failed
  }

} //end userLogin



function getUserLoc($ip,$username) {
//check, if the provided ip is valid
 
if(!filter_var($ip, FILTER_VALIDATE_IP))
 
{
 
throw new InvalidArgumentException("IP is not valid");
 
}
 
//contact ip-server
 
$response=file_get_contents('http://www.netip.de/search?query='.$ip);
 
if (empty($response))
 
{
 
throw new InvalidArgumentException("Error contacting Geo-IP-Server");
 
}
 
//Array containing all regex-patterns necessary to extract ip-geoinfo from page
 
$patterns=array();
 
$patterns["domain"] = '#Domain: (.*?)&nbsp;#i';
 
$patterns["country"] = '#Country: (.*?)&nbsp;#i';
 
$patterns["state"] = '#State/Region: (.*?)<br#i';
 
$patterns["city"] = '#City: (.*?)<br#i';
 
//Array where results will be stored
 
$ipInfo=array();
 
//check response from ipserver for above patterns
 
foreach ($patterns as $key => $pattern)
 
{
 
//store the result in array
 
$ipInfo[$key] = preg_match($pattern,$response,$value) && !empty($value[1]) ? $value[1] : 'not found';
 
}
/*I've included the substr function for Country to exclude the abbreviation (UK, US, etc..)
To use the country abbreviation, simply modify the substr statement to:
substr($ipInfo["country"], 0, 3)
*/

$ipInfo['ip'] = $ip;
$ipInfo['username'] = $username;
 //print_r($ipInfo);
return $ipInfo;
 
 }






 function storeLoginActivity($data) {
  
  $sDate = date("Y-m-d H:i:s");
   $SQL = "INSERT INTO staffLoginHistory VALUES('','".$data['ip']."',
						   '".$data['state'] . "',
						   '".$sDate . "',
						   '" . $data['username']."',
						   '".$data['city']."',
						   '" . $data['country']."')";
 // echo $SQL;
 $this->executeQuery($SQL,'');
 }

//handles feed deletion
function deleteFeed($feedID,$return=true) { 

//make sure the requested Feed ID is a numeric value and not a Cross Site Forgery Request

 if(!is_numeric($feedID)) { 
  $this->errors[] = "Feed ID must be a numeric value";
  return false;
 }

 //delete the feed data first 
 $delData = "DELETE FROM feedData 
             WHERE 
			  feedID=".$feedID;
 $this->executeQuery($delData,'');

 //now delete the feed itself		  
 $delFeed = "DELETE FROM feeds 
             WHERE 
			  feedID=".$feedID;
  			  
 $this->executeQuery($delFeed,'');

  return true; 
} //end deleteFeed


//handles feed updating request
function updateFeed($feedID,$return=true) { 

//skip XS Forgery Request
if(!is_numeric($feedID)) { 
  $this->errors[] = "Feed ID must be a numeric value";
  return false;
 }

 //delete old data 
 $delData = "DELETE FROM feedData 
             WHERE 
			  feedID=".$feedID;
 $this->executeQuery($delData,'');
 
 //selects feed URL again
 $getFeedURL = "SELECT feedURL FROM feeds 
                WHERE 
			    feedID=".$feedID;
  			  
 $feedURL = $this->executeQuery($getFeedURL,'get');
 
 //read all feed again from the URL
 //in this request 1 is passed as a last param.
 //so that savedDate can be updated too
 
 $this->addFeedData($feedID,$feedURL->feedURL,true,1); 
 
} //end deleteFeed


//get Mozambique cities

function getCities() {
  $citiesArray = array();  
  $SQL = "SELECT * FROM cities ORDER BY cityName";
  $cityResource = $this->executeQuery($SQL,"res");
  while($cityRow=mysql_fetch_array($cityResource)){
    $citiesArray[] = array($cityRow['cityID'],$cityRow['cityName']);
  }
  
  return $citiesArray;
}



//data filtering
function filterData($varName,$val,$type,$errorMSG) { 
 switch($type) { 
   case "general":
    $val = trim($val);
	$val = strip_tags($val);
    if(strlen($val) < 1) { 
	  $this->errors[] = $errorMSG;
	 return false;
	}
   break;
   
   case "url":
    $val = $this->validateURL("feedURL",$val);
	if(strlen($val) < 1 || $val == false) { 
	 return false;
	}
   break;
   
   case "xss":
    if(strlen($val) < 1 ) { 
	 return false;
	}
	$val = $this->RemoveXSS($val);
    if(strlen($val) < 1) { 
	 $this->errors[] = "$varName: XSS attack found.";
	 return false;
	} 
   break;
 } //end switch   
 
 return $val;
} //end filterData


//checks URL pattern
function validateURL($varName,$val) { 
	if( ! preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $val) ) { 
	 $this->errors[] = "$varName: Wrong URL pattern.";
	 return false;
	}
	else { 
	 return $val;
	}
	
} //end validateURL


//purify the URL to remove all possible XSS attack code passed in the URLs
function RemoveXSS($val) {
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
   
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) {
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;

      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
   }
   

   $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $ra = array_merge($ra1, $ra2);
   
   $found = true; // keep replacing as long as the previous round replaced something
   while ($found == true) {
      $val_before = $val;
      for ($i = 0; $i < sizeof($ra); $i++) {
         $pattern = '/';
         for ($j = 0; $j < strlen($ra[$i]); $j++) {
            if ($j > 0) {
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(&#0{0,8}([9|10|13]);)';
               $pattern .= ')*';
            }
            $pattern .= $ra[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
         if ($val_before == $val) {
            // no replacements were made, so exit the loop
            $found = false;
         }
      }
   }
   return $val;
}


//execute queries against Database
function executeQuery($sql_query,$wtr){

    $sql_res=mysql_query($sql_query) or die($sql_query . "(".mysql_error().")");
	//echo mysql_num_rows($sql_res);
	if($wtr=='lastID') { 
	 return mysql_insert_id();
	}
	elseif($wtr=='get'){
		if(1){
           //print_r(mysql_fetch_object($sql_res));
			return mysql_fetch_object($sql_res);
		}
		else {
			return 'executeQuery does not have this option';
		}
	}
	elseif($wtr=='num'){
	//echo mysql_num_rows($sql_res) . "======================";
		return mysql_num_rows($sql_res) or mysql_error();
	}
	elseif($wtr=='res'){
		return $sql_res;
	}
}


//find a value from server arrays
function form_get($value){
	global $HTTP_POST_VARS,$HTTP_GET_VARS,$_SERVER;
	$REQUEST_METHOD=$_SERVER["REQUEST_METHOD"];
	if($REQUEST_METHOD=='POST')
		$get_value=$HTTP_POST_VARS["$value"];
	elseif($REQUEST_METHOD=='GET')
		$get_value=$HTTP_GET_VARS["$value"];

	return $get_value;
}

    function inWords($number) {

        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' and ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->inWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->inWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->inWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->inWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= " US Dollars ". $decimal;
            $cents = " Cents";
            $words = array();

            /*foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }

            $string .= implode(' ', $words); */

            $decimalVal = $this->inWords($fraction);
            $string .=  $decimalVal;
        }

        return $string . $cents;
    }




} // end of class
?>
