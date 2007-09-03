<?php

$Module =& $Params['Module'];
$Offset = $Params['Offset'];

if ( $Offset )
{
    $Offset = (int) $Offset;
}

if ( !is_numeric( $Offset ) )
{
    $Offset = 0;
}

if ( isset( $Params['UserParameters'] ) )
{
    $UserParameters = $Params['UserParameters'];
}
else
{
    $UserParameters = array();
}

$viewParameters = array( 'offset' => $Offset );
$viewParameters = array_merge( $viewParameters, $UserParameters );

$limit = 20;

include_once( 'extension/akismet/classes/ezcontentobjectakismet.php' );

$extractableNodes = eZContentObjectAkismet::getExtractableNodes( $limit, $Offset );
$extractableNodesCount = eZContentObjectAkismet::getExtractableNodesCount();

if ( $Module->isCurrentAction( 'Submit' ) && $Module->hasActionParameter( 'ObjectIDList' ) )
{
    $objectIDList = $Module->actionParameter( 'ObjectIDList' );

    foreach ( $objectIDList as $objectID )
    {
        $object = eZContentObject::fetch( $objectID );
        $version = $object->attribute( 'current' );

        include_once( 'extension/akismet/classes/ezcontentobjectakismet.php' );
        $infoExtractor = eZContentObjectAkismet::getInfoExtractor( $version );
        if ( $infoExtractor )
        {
            $comment = $infoExtractor->getCommentArray();

            eZDebug::writeDebug( $comment );

            include_once( 'extension/akismet/classes/ezakismet.php' );
            $akismet = new eZAkismet( $comment );

            if ( !$akismet->errorsExist() )
            {
                $response = $akismet->submitSpam();
                eZDebug::writeNotice( $response, 'Akismet web service response' );
            }
            else
            {
               eZDebug::writeWarning( 'An error has occured, unable to submit spam to Akismet.' );
            }

        }
        else
        {
            eZDebug::writeDebug( 'Unable to find Akismet info extractor' );
        }
    }
}

include_once( 'kernel/common/template.php' );
$tpl =& templateInit();

$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'nodes', $extractableNodes );
$tpl->setVariable( 'nodes_count', $extractableNodesCount );
$tpl->setVariable( 'limit', $limit );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:akismet/submit.tpl' );
$Result['path'] = array(
    array( 'url' => false, 'text' => 'Akismet' ),
    array( 'url' => false, 'text' => 'Submit spam' )
);

?>