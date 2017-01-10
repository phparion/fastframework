<?php
############################
# Written By: Haroon Ahmad #
# www.fastcreators.com     #
############################
#######################################################################
# header view
########################################################################

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }
        .row::after {
            content: "";
            clear: both;
            display: block;
        }
        [class*="col-"] {
            float: left;
            padding: 15px;
        }
        html {
            font-family: "Lucida Sans", sans-serif;
        }
        .header {
            background-color: #F65959;
            color: #ffffff;
            padding: 15px;
            text-align: center;
        }
        .logo {
            float:left;
        }
        .menu {
            text-align: center;
        }
        .menu a {


            padding: 10px;
            height: 50px;
            color: #757477;
            font-size: 12px;
            text-decoration: none;
            font-family: "Interstate-Bold", "helvetica-neue", helvetica, sans-serif;

        }
        .menu a:active {

            margin: 0;
            padding: 10px;
            height: 50px;
            color: #000000;

        }

        .menu a:hover {
            color: #000000;
            border-bottom: solid 1px  #DF0000;
        }
        .aside {
            background-color: #529214;
            padding: 15px;
            color: #ffffff;
            text-align: center;
            font-size: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        }
        .footer {
            background-color: #529214;
            color: #ffffff;
            text-align: center;
            font-size: 12px;
            padding: 15px;
        }
        /* For desktop: */
        .col-1 {width: 8.33%;}
        .col-2 {width: 16.66%;}
        .col-3 {width: 25%;}
        .col-4 {width: 33.33%;}
        .col-5 {width: 41.66%;}
        .col-6 {width: 50%;}
        .col-7 {width: 58.33%;}
        .col-8 {width: 66.66%;}
        .col-9 {width: 75%;}
        .col-10 {width: 83.33%;}
        .col-11 {width: 91.66%;}
        .col-12 {width: 100%;}

        @media only screen and (max-width: 768px) {
            /* For mobile phones: */
            [class*="col-"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <img class="logo" src="fastcreators_logo.gif"><p>my ideas are my kids. I make them, I grow them, I love them - Haroon Ahmad</p>
</div>
