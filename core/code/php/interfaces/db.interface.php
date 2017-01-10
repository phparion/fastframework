<?php

############################
# Written By: Haroon Ahmad #
# www.haroonahmad.co.uk    #
############################
#######################################################################
# Shell Interface implemented by Shell Class 
# it controls overall application. 
########################################################################


interface dbInterface {

//connect database. bring global variables in function scope
function connectDB();

//handle user login. return is optional
function loginUser($username,$password,$return=true);

//handle feed addition, return is optional
function deleteFeed($feedID,$return=true);

//handles feed updating request. return is optional
function updateFeed($feedID,$return=true);

//handles feed insertion, return is optional
function addFeed($feed,$url,$return=true);

//return feeds array
function getFeedsCollection();

//return an array of single feed data
function getSingleFeed($feedID);

//add feed data. status and update are optional
function addFeedData($feedID,$url,$status=true,$update=0);

}

?>