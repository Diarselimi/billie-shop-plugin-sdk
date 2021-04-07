<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>

    <title><?php /** @noinspection PhpUndefinedVariableInspection */
        echo $title; ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-language" content="en">
    <meta name="robots" content="noindex, nofollow"/>
    <meta name="author" content="Billie GmbH">
    <meta name="publisher" content="Billie GmbH">
    <meta name="copyright" content="Billie GmbH">

    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet"

    <link rel="apple-touch-icon" sizes="57x57" href="https://www.billie.io/assets/images/favicons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="https://www.billie.io/assets/images/favicons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="https://www.billie.io/assets/images/favicons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="https://www.billie.io/assets/images/favicons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114"
          href="https://www.billie.io/assets/images/favicons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120"
          href="https://www.billie.io/assets/images/favicons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144"
          href="https://www.billie.io/assets/images/favicons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152"
          href="https://www.billie.io/assets/images/favicons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180"
          href="https://www.billie.io/assets/images/favicons/apple-icon-180x180.png">

    <link rel="icon" type="image/png" sizes="192x192"
          href="https://www.billie.io/assets/images/favicons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32"
          href="https://www.billie.io/assets/images/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96"
          href="https://www.billie.io/assets/images/favicons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16"
          href="https://www.billie.io/assets/images/favicons/favicon-16x16.png">

    <meta name="theme-color" content="#ffffff">

    <!--
    ReDoc doesn't change outer page styles
    -->
    <style>
        body {
            padding: 0;
            margin: 0;
        }

        .kWDcCe, .kBvmFd code {
            color: inherit !important;
        }

        .loJhdK, .jJgdQU, .fKXPuG, .jTyZVs, .mpbxz {
            color: #248fb2 !important;
        }

        .loJhdK, .mpbxz {
            background-color: #248fb211 !important;
            border: 1px solid #248fb233 !important;
        }

        .mpbxz {
            text-transform: lowercase;
            font-size: 13px;
            line-height: 20px;
            border-radius: 2px;
            margin: 0 5px;
            padding: 0 5px;
        }
    </style>
    <style>
        .api-v1, .api-v2 {
            position:absolute;
            background-color: #f3f3f3;
            cursor: pointer;
        }

        .api-v1:hover, .api-v2:hover {
            background-color: #e5e2e2;
        }

        .active-case {
            background-color: #ff4338;
            color: #1f2530;
        }

        .api-v1 {
            right: 50%;
        }

        .switch-button {
            width: 105px;
            height: 40px;
            text-align: center;
            position: absolute;
            right: 0%;
            top: 0%;
            will-change: transform;
            z-index: 197 !important;
            cursor: pointer;
            transition: 0.3s ease all;
        }

        .switch-button-case {
            display: inline-block;
            background: none;
            width: 50%;
            height: 100%;
            color: #1f2530;
            position: relative;
            border: none;
            transition: 0.3s ease all;
            padding-bottom: 1px;
            cursor: pointer;
        }
        .switch-button-case:focus {
            outline: none;
        }
    </style>
</head>
<body>
<div class="api-switch">
    <div class="switch-button">
        <a class="api-v1" href="">
            <button class="switch-button-case left active-case">API v1</button>
        </a>
        <a class="api-v2" href="">
            <button class="switch-button-case right">API v2</button>
        </a>
    </div>
</div>
<div id="redoc_container">Loading ...</div>
<script src="<?php /** @noinspection PhpUndefinedVariableInspection */
echo $redoc_js_url; ?>"></script>
<script>

// noinspection JSAnnotator
var OPENAPI_SPEC = <?php echo str_replace('`', '\\`', $spec); ?>;
var OPENAPI_SPEC_URL = [
    location.protocol, '//',
    location.host,
    location.pathname.replace(/\/$/, "")
].join('') + '/billie-pad-openapi.yaml';

Redoc.init(
    OPENAPI_SPEC.length > 0 ? OPENAPI_SPEC : OPENAPI_SPEC_URL,
    <?php /** @noinspection PhpUndefinedVariableInspection */
    echo $redoc_config_json; ?>,
        document.getElementById('redoc_container')
);



</script>
<script>

var base_url = window.location.origin;
var switchButton 			= document.querySelector('.switch-button');
var switchBtnRight 			= document.querySelector('.switch-button-case.right');
var switchBtnLeft 			= document.querySelector('.switch-button-case.left');
var activeSwitch 			= document.querySelector('.active');

var url = window.location.href;
if (url.toLowerCase().includes('v2')) {
	switchRight();
} else {
    switchLeft();
}

document.querySelector('.api-v1').setAttribute('href', base_url);
document.querySelector('.api-v2').setAttribute('href', base_url+'/v2');

function switchLeft(){
	document.querySelector('.api-v2').classList.remove('active-case');
	document.querySelector('.api-v1').classList.add('active-case');
}

function switchRight(){
	document.querySelector('.api-v2').classList.add('active-case');
	document.querySelector('.api-v1').classList.remove('active-case');
}

switchBtnLeft.addEventListener('click', function(){
	switchLeft();
}, false);

switchBtnRight.addEventListener('click', function(){
	switchRight();
}, false);




</script>
</body>
</html>
