<?php

class eZContentObjectAkismet
{
    /*!
     \static
     Returns an instance of a content object akismet information extractor
     suitable for the content object version $contentObjectVersion.
    */
    function getInfoExtractor( $contentObjectVersion )
    {
        include_once( 'lib/ezutils/classes/ezini.php' );
        $ini = eZINI::instance( 'akismet.ini' );
        $infoExtractors = $ini->variable( 'InformationExtractorSettings', 'ClassMap' );

        $contentObject = $contentObjectVersion->attribute( 'contentobject' );
        $classIdentifier = $contentObject->attribute( 'class_identifier' );
        if ( !array_key_exists( $classIdentifier, $infoExtractors ) )
        {
            $debug = eZDebug::instance();
            $debug->writeWarning( 'No Akismet content object information extractor configured for content class with identifier: ' . $classIdentifier );
            return false;
        }

        $type = $infoExtractors[$classIdentifier];

        include_once( 'lib/ezutils/classes/ezextension.php' );
        $out = array();
        $parameters = array(
            'ini-name' => 'akismet.ini',
            'repository-group' => 'InformationExtractorSettings',
            'repository-variable' => 'RepositoryDirectories',
            'extension-group' => 'InformationExtractorSettings',
            'extension-variable' => 'ExtensionDirectory',
            'extension-subdir' => 'akismet/informationextractor',
            'type-directory' => false,
            'suffix-name' => 'akismetinformationextractor.php',
            'type' => $type
        );
        $success = eZExtension::findExtensionType( $parameters, $out );

        if ( $success )
        {
            $className = $out['type'] . 'akismetinformationextractor';
            include_once( $out['found-file-path'] );
            $impl = new $className( $contentObjectVersion );
            return $impl;
        }
        else
        {
            return false;
        }
    }
}

?>