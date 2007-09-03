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

    /*!
     \static
     Returns an array of class identifiers for which a content object akismet
     information extractor is available.
    */
    function getExtractableClassList()
    {
        include_once( 'lib/ezutils/classes/ezini.php' );
        $ini = eZINI::instance( 'akismet.ini' );
        $infoExtractors = $ini->variable( 'InformationExtractorSettings', 'ClassMap' );

        return array_keys( $infoExtractors );
    }

    /*
     \static
     Returns an array of nodes for which a content object akismet
     information extractor is available.
    */
    function getExtractableNodes( $limit = false, $offset = false )
    {
        $classList = eZContentObjectAkismet::getExtractableClassList();

        include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
        $params = array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => $classList,
            'SortBy' => array( array( 'published', false ) ),
            'Limit' => $limit,
            'Offset' => $offset
        );

        $nodes = eZContentObjectTreeNode::subTree( $params, 1 );
        return $nodes;
    }

    /*
     \static
     Returns the number of nodes for which a content object akismet
     information extractor is available.
    */
    function getExtractableNodesCount()
    {
        $classList = eZContentObjectAkismet::getExtractableClassList();

        include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
        $params = array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => $classList
        );

        $nodeCount = eZContentObjectTreeNode::subTreeCount( $params, 1 );
        return $nodeCount;
    }
}

?>