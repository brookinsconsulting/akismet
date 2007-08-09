<?php

include_once( 'extension/akismet/lib/akismet/akismet.class.php' );
include_once( 'lib/ezutils/classes/ezini.php' );

class eZAkismet extends Akismet
{
    function eZAkismet( $comment )
    {
        $ini = eZINI::instance( 'akismet.ini' );
        $blogURL = $ini->variable( 'SiteSettings', 'BlogURL' );
        $apiKey = $ini->variable( 'AccountSettings', 'APIKey' );

        $this->Akismet( $blogURL, $apiKey, $comment );

        if ( $this->errorsExist() )
        {
            $errors = $akismet->getErrors();
            $debug = eZDebug::instance();
            foreach ( $errors as $error )
            {
                $debug->writeError( $error, 'eZAkismet constructor' );
            }
        }
    }
}

?>