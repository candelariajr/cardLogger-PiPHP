<?php
/**
 * Created by PhpStorm.
 * User: candelariajr
 * Date: 8/15/17
 * Time: 3:43 PM
 */

/*
 * DOCUMENTATION TERMS:
 *
 * SERVER: The web server this device is running
 * CONTROL SERVER: The central app management/programming portal
 *
 * */



/*
 * The name of the config file
 * */
/** @var string of file $fileName */
$fileName = "config.json";
/** @var string of control server ip $serverIp */
$serverIp = "152.10.136.69";
/** @var string of the server */
$myName = "localhost";
/** @var boolean of $debugState */
$debugState = false;
/** @var array of objects that a config file must have : $configFileTemplate
 *  Keeping this up to date with the config.json file will prevent accidental programatic deletion of
 *  components that are needed for apps operation.
 * */
$configFileTemplate = array("location-list", "offline-list");
/** @var boolean if you get the list of offline transactions, do you want to clear them?
 * This gives some flexibility in writing the client callbacks $autoDeleteOnOfflineGet */
$autoDeleteOnOfflineGet = false;
/**
 * @var string all card numbers must follow regex template (stop SQL injection) regexTemplate
 */
$regexTemplate = "/^[0-9]{9}$/";
/**
/*
 *
 * Start of App - No PHP Params beyond this point.
 * NOTE: Parameters may not be hardwired in the future when profiling is built in
 *
 * It is not recommended that you change anything below this comment:
 *
 *
 * */
/** @var boolean THE FACT $philCollinsIsAwesome */
$philCollinsIsAwesome = true;

if($debugState){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

/*
 * GET: Get requests are computationally faster and require less resources.
 * GET arguments determine which function is called for the each operation. The operations in the future will
 * be determined by app specification once profiles go live
 *
 * This is the start of the app
 *
 * */
if(isset($_GET['action'])){
    $action = $_GET['action'];
    if($action == "getLocations"){
        getLocations();
    }
    if($action == "getServerStatus"){
        getServerStatus();
    }
    if($action == "getOfflineTransactionList"){
        getOfflineTransactionList();
    }if($action == "getConfig"){
        getConfig();
    }
    //TODO: Remove this!
    if($action == "test"){
        test();
    }
}else if(isset($_POST['location-list'])){
    setLocations();
}

/*
 * This is called on condition from the get action argument:
 * Calls getJsonFromConfig file to return the json object from GLOBAL $fileName
 * Outputs the JSON from the location list if applicable.
 * */
function getLocations(){
    $jsonData = getJsonFromConfigFile();
    //return the json array - echo it
    if(sizeof($jsonData["location-list"]) != 0){
        echo json_encode($jsonData["location-list"]);
    }else{
        echo json_encode(array("0", "Default"));
    }
}

/*
 * This is called by the presence of a POST request of location array components
 * Array of location-list structure needs to be present
 * */
function setLocations(){
    //get the JSON from file
    $jsonData = getJsonFromConfigFile();
    //TODO: Finish Writing this function
    //clear the location array
    //save the file
    //close the file
}

/*
 * This is called on condition from the get action argument:
 * Calls getJsonFromConfig file to return the json object from GLOBAL $fileName
 * Reports the Success/OK status code.
 * */
function getServerStatus(){

    //get the JSON from file
    $jsonData = getJsonFromConfigFile();
    $offLineCount = sizeof($jsonData['offline-list']);
    //get the status of this web server. This is supposed to be called remotely.
    echo json_encode(array("status" => 200, "offline_size" => $offLineCount));
}

/*
 * This is called on condition from the get action argument:
 * Calls getJsonFromConfig file to return the json object from GLOBAL $fileName
 * returns a JSON array of offline transactions
 * if autoDeleteOnOfflineGet is enabled, then calls clearOffline to clear out
 * offline transactions. This is optional so that you don't need the callback.
 * */
function getOfflineTransactionList(){
    GLOBAL $autoDeleteOnOfflineGet;
    $jsonData = getJsonFromConfigFile();
    //return the json array - echo it
    if(sizeof($jsonData["offline-list"]) != 0){
        echo json_encode($jsonData["offline-list"]);
        if($autoDeleteOnOfflineGet){
            clearOffline();
        }
    }else{
        echo getReply(false, "no offline transactions");
    }
}

/*
 * Can be called by its own get request, or if autoDeleteOfflineGet is enabled, then by
 * getOfflineTransactionList.
 * This empties the offline-list array in the config.json object
 * */
function clearOffline(){
    GLOBAL $fileName;
    //TODO; Finish writing this function
    $jsonData = getJsonFromConfigFile();

    $jsonData["offline-list"] = array();

    //clears offline transactions
    //this should be attached to a callback on the condition that the card numbers have been
    //received by the server

    //open the file
    //empty offline array
    //save the file
    //close the file
}

/*
 * Can be called by its own get request or a post request.
 * This appends an offline transaction to the offline-list array in config.json. It gets the
 * data from the location.
 * */
function addOfflineTransaction(){
    //get the JSON from file
    //TODO: Finish writing this function
    $jsonData = getJsonFromConfigFile();
    if(isset($_GET['cardNumber']) && isset($_GET['location'])){
        $cardNumber = $_GET['cardNumber'];

    }
    else if(isset($_GET['cardNumber'])){
        $cardNumber = $_GET['cardNumber'];
        echo getReply(true, "card $cardNumber added to list");
        //open the file-
        //append card# and date to file
        //save the file
        //close the file
    }
}

/*
 * Grabs the JSON from the config file-
 * this is called by:
 * getLocations
 * setLocations..
 *
 * EVERY FREAKING PIECE OF THIS PROGRAM USES THIS FUNCTION
 *
 * */
function getJsonFromConfigFile(){
    GLOBAL $fileName;
    //TODO: Wrap this in a try/catch and toss us out an error message with getReply
    //open the file
    $jsonFile = file_get_contents("./".$fileName);
    $jsonData = json_decode($jsonFile, true);
    //return the file
    return $jsonData;
}

/*
 * Takes a json object as argument. Saves said object to the filename
 * listed in fileName on the condition that it matches the template.
 * */
function saveJsonToConfigFile($jsonObject){
    GLOBAL $fileName;
    GLOBAL $configFileTemplate;
    $templateOK = true;
    $missingElements = array();

    //The importance of a template.
    //If an algorithm is deleting info from the config.json file- the template stops it
    foreach ($configFileTemplate as $templateEntity){
        if(!isset($jsonObject[$templateEntity])){
            $templateOK = false;
            array_push($missingElements, $templateEntity);
        }
    }
    if($templateOK){
        file_put_contents("./".$fileName, json_encode($jsonObject));
        echo getReply(true, "");
    }else{
        echo getReply(false, (array("Missing" => $missingElements)));
    }
}

//TODO: Remove this!
function test(){
    //$testObject = array(array("stuff" => "things", "things" => "stuff"), array("otherthing", "ottteerrr"));
    //$testObject = array("location-list" => array(array('1', "location1")));
    //array_push($testObject['location-list'], ["2", "things"]);
    //echo json_encode($testObject);
    $testObject = array("location-list" => array(), "offline-list" => array());
    //echo json_encode($testObject);
    saveJsonToConfigFile($testObject);
    //echo json_encode(getReply(false, array("Problem1" => "stuff", "Problem2" => "things")));
}

/*
 * echos out the state of the global json config object. Mainly for troubleshooting
 * and to act as an analysis aid for the control server (called as a result of get arg)
 * */
function getConfig(){
    GLOBAL $fileName;
    echo file_get_contents("./".$fileName);
}


/*
 * takes a boolean and a message object as arguments:
 * boolean is the success/fail result of the request-
 * message is the reply message/object if any.
 * */
function getReply($success, $message){
    $returnArray = array();
    if($success){
        $returnArray['reply'] = 'success';
    }else{
        $returnArray['reply'] = 'failed';
        //TODO: Toss this in a log somewhere- write some basic log script
    }
    if($message != ""){
        $returnArray['message'] = $message;
    }
    return json_encode($returnArray);
}
