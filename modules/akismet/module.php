<?php

$Module = array( 'name' => 'Akismet', 'variable_params' => false, 'ui_component_match' => 'module' );

$ViewList = array();
$ViewList['submit'] = array(
    'script' => 'submit.php',
    'functions' => array( 'submit' ),
    'single_post_actions' => array( 'SpamSubmitButton' => 'Submit' ),
    'post_action_parameters' => array( 'Submit' => array( 'ObjectIDList' => 'ObjectIDList' ) )
);

$FunctionList = array();
$FunctionList['submit'] = array();

?>