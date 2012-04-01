<?php

include_once('autoload.php');

$Module =& $Params['Module'];
$Offset = $Params['Offset'];
$response = array();

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

$objectIDList = array();

if ( $Module->hasActionParameter( 'ObjectIDList' ) )
{
    $objectIDList = $Module->actionParameter( 'ObjectIDList' );

    if ( $Module->isCurrentAction( 'Submit' ) )
    {
        foreach ( $objectIDList as $objectID )
        {
            $object = eZContentObject::fetch( $objectID );
            $version = $object->attribute( 'current' );

	    $akismetObject = new eZContentObjectAkismet();
            $comment = $akismetObject->akismetInformationExtractor( $version );
            
            if ( $comment )
            {
                eZDebug::writeDebug( $comment );
                $akismet = new eZAkismet( $comment ); 
                if ( $akismet )
                {
                    $feedback = $akismet->submitSpam();
                    $response[] = $feedback[1];
                }
                else
                {
                   $response[] = ezi18n( 'extension/contactivity/akismet/submit', "An error has occured, unable to submit spam to Akismet." );
                }

            }
            else
            {
                  $response[] = ezi18n( 'extension/contactivity/akismet/submit', "An error has occured, unable to submit spam to Akismet." );
            }
        }
    }
    elseif ( $Module->isCurrentAction( 'Remove' ) )
    {
        foreach ( $objectIDList as $objectID )
        {
           $object = eZContentObject::fetch( $objectID );

           if ( !$object->attribute( 'can_remove' ) )
           {
               $response[] = ezi18n( 'extension/contactivity/akismet/submit', "You are not allowed to remove this content object." );
               continue;
           }

           $assignedNodes = $object->attribute( 'assigned_nodes' );

            $nodeIdArray = array( );
            foreach ( array_keys( $assignedNodes ) as $assignedNodeKey )
            {
                if ( $assignedNodes[$assignedNodeKey]->attribute( 'can_remove' ) )
                {
                    $nodeIdArray[] = $assignedNodes[$assignedNodeKey]->attribute( 'node_id' );
                }
                else
                {
                    $nodeIdArray = false;
                    break;
                }
            }

            if ( $nodeIdArray )
            {
                eZContentObjectTreeNode::removeSubtrees( $nodeIdArray, false );
            }
            else
            {
                $object->remove();
                $object->purge();
            }
        }
    }
}

$akismet=new eZContentObjectAkismet();
$extractableNodes = $akismet->getExtractableNodes( $limit, $Offset );
$extractableNodesCount = $akismet->getExtractableNodesCount();

include_once( 'kernel/common/template.php' );
$tpl = templateInit();
$tpl->setVariable( 'object_id_list', $objectIDList );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'nodes', $extractableNodes );
$tpl->setVariable( 'nodes_count', $extractableNodesCount );
$tpl->setVariable( 'limit', $limit );
$tpl->setVariable( 'feedback', $response );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:akismet/submit.tpl' );
$Result['path'] = array(
    array( 'url' => false, 'text' => 'Akismet' ),
    array( 'url' => false, 'text' => 'Report spam' )
);

?>