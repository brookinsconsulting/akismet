<?php

$Module = array( 'name' => 'Akismet', 'variable_params' => false, 'ui_component_match' => 'module' );

$ViewList = array();
$ViewList['submit'] = array(
    'script' => 'submit.php',
    'functions' => array( 'submit' ),
    'unordered_params' => array( 'offset' => 'Offset' ),
    'single_post_actions' => array(
        'SpamSubmitButton' => 'Submit',
        'SpamRemoveButton' => 'Remove' ),
    'post_action_parameters' => array(
        'Submit' => array( 'ObjectIDList' => 'ObjectIDList' ),
        'Remove' => array( 'ObjectIDList' => 'ObjectIDList' ) )
);

$FunctionList = array();
$FunctionList['submit'] = array();

?>