<?php

$laravelVersion = '8.0';

$reqList = array(
   
    '8.0' => array(
        'php' => '7.3.0',
        'openssl' => true,
        'fileinfo' => true,
        'pdo' => true,
        'curl' => true,
        'mbstring' => true,
        'tokenizer' => true,
        'xml' => true,
        'ctype' => true,
        'json' => true,
        'bcmath' => true,
        'gd' => true,
        'obs' => ''
    ),
);


$strOk = '<i style="color: #22bb33;" class="fa fa-check"></i>';
$strFail = '<i style=" color: red; " class="fa fa-times"></i>';
$strUnknown = '<i class="fa fa-question"></i>';

$requirements = array();

    
// Version PHP
$requirements['php_version'] = version_compare(PHP_VERSION, $reqList[$laravelVersion]['php'], ">=");

// OpenSSL PHP Extension
$requirements['openssl_enabled'] = extension_loaded("openssl");

// PDO PHP Extension
$requirements['pdo_enabled'] = defined('PDO::ATTR_DRIVER_NAME');

// Mbstring PHP Extension
$requirements['mbstring_enabled'] = extension_loaded("mbstring");

// Curl PHP Extension
$requirements['curl_enabled'] = extension_loaded("curl");

// Tokenizer PHP Extension
$requirements['tokenizer_enabled'] = extension_loaded("tokenizer");

// XML PHP Extension
$requirements['xml_enabled'] = extension_loaded("xml");

// CTYPE PHP Extension
$requirements['ctype_enabled'] = extension_loaded("ctype");

// File PHP Extension
$requirements['fileinfo_enabled'] = extension_loaded("fileinfo");

// gd PHP Extension
$requirements['gd_enabled'] = extension_loaded("gd");

// JSON PHP Extension
$requirements['json_enabled'] = extension_loaded("json");

// BCMath
$requirements['bcmath_enabled'] = extension_loaded("bcmath");

// mod_rewrite
// $requirements['mod_rewrite_enabled'] = null;

// if (function_exists('apache_get_modules')) {
//     $requirements['mod_rewrite_enabled'] = in_array('mod_rewrite', apache_get_modules());
// }

$allValuesAreTrue = (count(array_unique($requirements)) === 1);

?>

@extends('setup.main')
@section('content')

<div class="row">
    <div class="col-12 text-center mt-3">
        <ul class="progressbar"> 
            <li class="active"><a href="/setup">Server Requirements</a></li>
            <li>Settings</li>
            <li>Database</li>
            <li>Summary</li>
        </ul>
    </div>
</div>


<div class="row mt-3 p-5" >
    <div class="col-12">   
    @if (! $allValuesAreTrue)
     <p class="alert alert-danger">Your server doesn't meet the following requirements</p> 
    @endif
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                PHP <?php
                if (is_array($reqList[$laravelVersion]['php'])) {
                    $phpVersions = array();
                    foreach ($reqList[$laravelVersion]['php'] as $operator => $version) {
                        $phpVersions[] = "{$operator} {$version}";
                    }
                    echo implode(" && ", $phpVersions);
                } else {
                    echo ">= " . $reqList[$laravelVersion]['php'];
                }?>
                    <span><?php echo " " . ($requirements['php_version'] ? $strOk : $strFail); ?>
                (<?php echo PHP_VERSION; ?>)</span>
            </li>  

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['openssl']) : ?>
                    <p>OpenSSL PHP Extension</p>
                <?php endif; ?>
                <span><?php echo $requirements['openssl_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['gd']) : ?>
                    <p>Gd PHP Extension</p>
                <?php endif; ?>
                <span><?php echo $requirements['gd_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['fileinfo']) : ?>
                    <p>fileinfo PHP Extension</p>
                <?php endif; ?>
                <span><?php echo $requirements['fileinfo_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['pdo']) : ?>
                    <p>Pdo PHP Extension</p>
                <?php endif; ?>
                <span><?php echo $requirements['pdo_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['mbstring']) : ?>
                <p>Mbstring PHP Extension</p>
                <?php endif ?>
                <span><?php echo $requirements['mbstring_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['curl']) : ?>
                <p>Curl PHP Extension</p>
                <?php endif ?>
                <span><?php echo $requirements['curl_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['tokenizer']) : ?>
                <p>Tokenizer PHP Extension</p>
                <?php endif ?>
                <span><?php echo $requirements['tokenizer_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['xml']) : ?>
                <p>XML PHP Extension</p>
                <?php endif ?>
                <span><?php echo $requirements['xml_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['ctype']) : ?>
                <p>CTYPE PHP Extension</p>
                <?php endif ?>
                <span><?php echo $requirements['ctype_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if ($reqList[$laravelVersion]['json']) : ?>
                <p>JSON PHP Extension</p>
                <?php endif ?>
                <span><?php echo $requirements['json_enabled'] ? $strOk : $strFail; ?></span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if (isset($reqList[$laravelVersion]['bcmath']) && $reqList[$laravelVersion]['bcmath']) : ?>
                <p>BCmath PHP Extension</p>
                <?php endif ?>
                <span><?php echo $requirements['bcmath_enabled'] ? $strOk : $strFail; ?></span>
            </li>

        </ul>
    </div>

    @if ($allValuesAreTrue)
        <div class="offset-6 col-6 col-md-6">
            <a href="/setup/step-1" id="next"  class="btn btn-outline-danger mt-3 float-md-right" > Next Step <i class="fa fa-angle-right"></i></a>
        </div>
    @endif
</div>

@endsection