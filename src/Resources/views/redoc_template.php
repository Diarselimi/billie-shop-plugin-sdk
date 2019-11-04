<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>

    <title><?php /** @noinspection PhpUndefinedVariableInspection */echo $title; ?></title>

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
    <link rel="apple-touch-icon" sizes="114x114" href="https://www.billie.io/assets/images/favicons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="https://www.billie.io/assets/images/favicons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="https://www.billie.io/assets/images/favicons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="https://www.billie.io/assets/images/favicons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="https://www.billie.io/assets/images/favicons/apple-icon-180x180.png">

    <link rel="icon" type="image/png" sizes="192x192" href="https://www.billie.io/assets/images/favicons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://www.billie.io/assets/images/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="https://www.billie.io/assets/images/favicons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://www.billie.io/assets/images/favicons/favicon-16x16.png">

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
</head>
<body>
<div id="redoc_container">Loading ...</div>
<script src="https://cdn.jsdelivr.net/npm/redoc@next/bundles/redoc.standalone.js"></script>
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
    <?php /** @noinspection PhpUndefinedVariableInspection */echo $redoc_config_json; ?>,
        document.getElementById('redoc_container')
);

</script>
</body>
</html>
