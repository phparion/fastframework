<?php
############################
# Written By: Haroon Ahmad #
# www.fastcreators.com    #
############################
#######################################################################
# this class runs the whole application. It communicates with 
# Model (MySQL database).
########################################################################

//load shell interface as we need to implement it

if(file_exists(INCLUDE_PATH . "core/code/php/interfaces/shell.interface.php")) {
  include_once INCLUDE_PATH . "core/code/php/interfaces/shell.interface.php";
}

//load database class as we need to extend it
if(file_exists(INCLUDE_PATH . "core/code/php/classes/database.class.php")) {
include_once INCLUDE_PATH . "core/code/php/classes/database.class.php";
}
 
 
class shell extends database implements shellInterface { 

  private $controller; //controller name
  private $view; //view name        
  private $action; //action name
  private $dbHandle; //database handle, (in case multiple databases are used, this handle will point to the active connection)
  protected $errors; //array of errors
  private $success; //success message array
  private $viewsLineUp; //array of view names, executed in a loop by showOutput() function
  private $menu; //extended (logged in user) OR basic(guest)
  private $contentsLineUp; //views to make contents 
  protected $feedsCollection; //Array of all feeds
  protected $singleFeedData; //Array of a feed's data
  protected $param; //extra params

   
// class constructor   
   public function __construct() { 
     parent::_construct();
	 $this->getController(); //check GET and POST to find the controller name to determine what we are going to do
	 $this->menu = ($this->userStatus()===true) ? "extended" : "basic"; //decide menu type to be displayed
	 $this->errors = NULL; //no errors when application starts                      
	 
	 //this will make the overall GUI layout. 
	 //Later on we add more and more views based on controller events.
	 //at last we execute them all in one go and generate the requested output
	 $this->viewsLineUp[]="header"; //add GUI header view name
	 $this->viewsLineUp[]="menu"; //add GUI menu view name
	 $this->viewsLineUp[]="main"; //add GUI main view name
	 $this->viewsLineUp[]="footer"; //add GUI footer view name
   } //end constructor
   
   //base function that loads the application. 
   //when this application extends for more features we can add more events to this function.
   //for example, adding a popup javascript dialogue box about a survey, a flash ad or 
   //call a web analytics or PPC Tracking script
   function appShell() { 
      $this->bootStrip();	 
   } //end appShell
   
   //find the controller name to decide the application request type.
   function getController() { 
   
    if(isset($_GET['controller']) || isset($_POST['controller'])) {
	 (isset($_REQUEST['opt'])) ? $this->action=$_REQUEST['opt'] : '';
	 $this->controller = $_REQUEST['controller']; 
	} //end if
	else {
	  $this->controller = "index"; //if no controller is called it means user is on the index page
     } //end else
   } //end getController
   
   
   //find the view called from request
   function getView() {
    if(isset($_GET['view']) || isset($_POST['view'])) {
	 $this->view = $_REQUEST['view'];
	} //end if
	else {
	  $this->view = "welcome"; //if no view is requested it means user is a guest. He must login to user application
     } //end else
   } //end getView
   
   
   
   /* decide which views are to be displayed */
   //this function works as the brain of the application. 
   //It loads different event calls based on controller and view type
   
   function bootStrip(){
   
     switch($this->controller) { 
	   //user is on entry level
	   case "index":
	    $this->contentsLineUp[]="welcome";
	   break;
	  
	   //user requested one of the feed related view
	   case "show":
	    
	    $this->getView(); //get the view name from server request
	    
	   // echo $this->getView() . "_______";
	   // print_r($_REQUEST);
		
		//now we call different events based on view name
	     switch($this->view) { 
		   
		   //user wants to see all feeds
		   case "addCustomer":
		    $this->contentsLineUp[]="addCustomer";
		   break;

		   
		 } //end switch
	  
	   break; //end show
	   
	  //application has requested such an event where we need MODEL Interaction. 
	   case "action": 
	     switch($this->action) {
		  //process user login
		   case "login":
		     $this->bridgeModel($this->action);
		   break;
		   

         } //end switch
	   break; //end action
	   

	 } //end switch
   } //end bootStrip
   
   
   //read buffer and load all views that were added during different controller events
   //into the application to make an output
   function bootContents() {

	//print_r($this->contentsLineUp);
     foreach($this->contentsLineUp as $view) {
		  include INCLUDE_PATH . "core/design/views/".$view.".view.php";
	 } //end foreach
   
   }
   
   
   //finally load all views and show the output   
   function showOutput() { 
	
	foreach($this->viewsLineUp as $view) { 
	  include INCLUDE_PATH . "core/design/views/".$view.".view.php";
	} //end foreach
   
    $this->closeDBHandle();    //free database resources
   } //end showOutput  
   
   
   //check if database connection was instantiated then close to free server resources
   function closeDBHandle() { 
   
     if(isset($this->dbHandle)) { 
	  mysql_close($this->dbHandle);
	 }
   } //end closeDBHandle
   
   
   
       
       //Get an array with geoip-infodata
       function geoCheckIP($ip) {
               //check, if the provided ip is valid
               if(!filter_var($ip, FILTER_VALIDATE_IP))
               {
                       throw new InvalidArgumentException("IP is not valid");
               }

               //contact ip-server
               $response=file_get_contents('http://api.hostip.info/get_html.php?ip='.$ip);
               if (empty($response))
               {
                       throw new InvalidArgumentException("Error contacting Geo-IP-Server");
               }
	       
                // preg_match_all("%<b>(.*?)</b>%s",$response,$ipInfo);
               //Array containing all regex-patterns necessary to extract ip-geoinfo from page
              
	      /* $patterns=array();
               $patterns["domain"] = '#Domain: (.*?)&nbsp;#i';
               $patterns["country"] = '#Country: (.*?)&nbsp;#i';
               $patterns["state"] = '#State/Region: (.*?)<br#i';
               $patterns["town"] = '#City: (.*?)<br#i';

               //Array where results will be stored
               $ipInfo=array();

               //check response from ipserver for above patterns
               foreach ($patterns as $key => $pattern)
               {
                       //store the result in array
                       $ipInfo[$key] = preg_match($pattern,$response,$value) && !empty($value[1]) ? $value[1] : 'not found';
               }
	      */
	      
	      $info = explode(":",$response);
	      $IpInfo['ip'] = $info['3'];
	      $IpInfo['country'] = str_replace('City','',$info['1']);
	      $IpInfo['city'] = str_replace('IP','',$info['2']);
	      //echo "<textarea rows=60 cols=280>$response</textarea>";
               return $IpInfo;
       }

 
   
   
   //find if user is logged in or not. called from different events
   function userStatus() {
   
     if( (isset($_SESSION['userLogin']) && $_SESSION['userLogin']===true) || $this->userSession === true ) { 
	  
	  $ip = $_SERVER['REMOTE_ADDR'];
	  //print_r($this->geoCheckIP($ip));
	
	  return true;
	 }
	 else { 
	  return false;
	 }
   
   } //end userStatus
   
   
   
   //special security key generator for enhanced security of csv download request
   //this key is decrypted in csv generator script
   
   function securityKey($str)  { 
     $hexCode = bin2hex($str);
	 $securityID = $hexCode . session_id();
	 $_SESSION['csvKey'] = $securityID; 
	 return $securityID;
   } 
   
   
   //as explained above, this loads all feeds, it is reusable.
   function loadAllFeeds($errorText='',$viewName=false) { 
    $this->feedsCollection = $this->bridgeModel("getFeedsCollection");
      if(sizeof($this->feedsCollection) > 0 ) { 
	    $this->contentsLineUp[]="viewAllFeeds";
      }
	else { 
	 if($errorText) {
	   $this->errors[]=$errorText;
       $this->contentsLineUp[] = "errors";
     } //end errorText if
	  if($viewName) {
	   $this->contentsLineUp[] = $viewName;
	  } //end viewName if 
	} //end feedCollection ELSE

 } //end loadAllFeeds
   
   
   //loads a single view from any event 
   //e.g login page when user is not logged in but tries to access a secure page
   
   function loadHTML($view) {
    include INCLUDE_PATH . "core/design/views/".$view.".view.php";
   } //end loadHTML

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

   //This function communicates with MODEL of the application.
   //It sends different Controller requests to Model and take back
   //data to take further actions. It can be called an abstract layer
   //between Controller and Model.
   //this function calls Database class functions
   
   function bridgeModel($type) { 
	$this->dbHandle = $this->connectDB();
	
	switch($type) { 
	 
	 //request a user login
	 case "login":	  
	  $status = $this->loginUser($_POST['username'],$_POST['password']); 
	  
	  if(!$status) { 
	   $this->contentsLineUp[] = "errors";
	   $this->contentsLineUp[] = "login"; 
	  }
	  else { 
	   $this->contentsLineUp[] = "login";
	  }
	 break;



	}//end switch
   
   } //end bridgeModel
   
 } //end class
?>