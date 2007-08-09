<?php

include_once( 'extension/akismet/classes/ezcontentobjectakismetinformationextractor.php' );

class eZUserAkismetInformationExtractor extends eZContentObjectAkismetInformationExtractor
{
    function eZUserAkismetInformationExtractor( &$contentObjectVersion )
    {
        $this->eZContentObjectAkismetInformationExtractor( $contentObjectVersion );
    }

    function getAuthor()
    {
        $dataMap = $this->ContentObjectVersion->attribute( 'data_map' );
        $accountInfo = $dataMap['user_account']->attribute( 'content' );

        return $accountInfo->attribute( 'login' );
    }

    function getBody()
    {
        $dataMap = $this->ContentObjectVersion->attribute( 'data_map' );
        return $dataMap['signature']->attribute( 'content' );
    }

    function getEmail()
    {
        $dataMap = $this->ContentObjectVersion->attribute( 'data_map' );
        $accountInfo = $dataMap['user_account']->attribute( 'content' );

        return $accountInfo->attribute( 'email' );
    }

    function getWebsite()
    {

    }
}

?>