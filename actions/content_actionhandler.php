<?php

function akismet_ContentActionHandler( &$module, &$http, &$objectID )
{
    $object = eZContentObject::fetch( $objectID );
    $version = $object->attribute( 'current' );
    if ( $http->hasPostVariable( 'AkismetSubmitSpam' ) )
    {
        include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );
        $user = eZUser::currentUser();

        $accessResult = $user->hasAccessTo( 'akismet', 'submit' );

        if ( $accessResult['accessWord'] === 'yes' )
        {
            $mainNode = $object->attribute( 'main_node' );
            $module->redirectTo( $mainNode->attribute( 'url_alias' ) );

            include_once( 'extension/akismet/classes/ezcontentobjectakismet.php' );
            $infoExtractor = eZContentObjectAkismet::getInfoExtractor( $version );
            if ( $infoExtractor )
            {
                $comment = $infoExtractor->getCommentArray();

                include_once( 'extension/akismet/classes/ezakismet.php' );
                $akismet = new eZAkismet( $comment );

                if ( !$akismet->errorsExist() )
                {
                    $response = $akismet->submitSpam();
                    $debug = eZDebug::instance();
                    $debug->writeNotice( $response, 'Akismet web service response' );
                }
                else
                {
                   $debug = eZDebug::instance();
                   $debug->writeWarning( 'An error has occured, unable to submit spam to Akismet.' );
                }

            }
            else
            {
                $debug = eZDebug::instance();
                $debug->writeDebug( 'Unable to find Akismet info extractor' );
            }
        }

        $mainNode = $object->attribute( 'main_node' );
        $module->redirectTo( $mainNode->attribute( 'url_alias' ) );

        return true;
    }
}

?>