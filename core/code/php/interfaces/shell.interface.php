<?php

############################
# Written By: Haroon Ahmad #
# www.haroonahmad.co.uk    #
############################
#######################################################################
# Shell Interface implemented by Shell Class 
# it controls overall application. 
########################################################################


interface shellInterface {
//call parseURL, based on which call other functions and run application
function appShell();

//initialize application, execute constructor, parse URL etc
function bootStrip();

//find controller name
function getController();
 
//find view name
function getView();

//load all views which are added to the output buffer during controller events
function bootContents(); 

//show output in the browser
function showOutput();

//free database resources so tha maximum number of parallel connection limit does not exceed
function closeDBHandle();

//check user status
function userStatus();

//load feeds data, option error text message and view name
function loadAllFeeds($errorText='',$viewName=false);

/* display required view: takes view name and load it from the view directory */
function loadHTML($viewName); 

//connect Model with Controller based on request type 
function bridgeModel($type);

}

?>