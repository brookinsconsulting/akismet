<?php

// SOFTWARE NAME: Akismet extension
// Based on the Akismet extension by Kristof Coomans for eZ publish 3.x 
// SOFTWARE RELEASE: 1.0-0
// COPYRIGHT NOTICE: Copyright (C) 2009-2010 Contactivity
// SOFTWARE LICENSE: GNU General Public License v1.0
// NOTICE: >

//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

include_once( 'autoload.php' );

class eZContentObjectAkismet
{
    /*!
     \static
     Returns an instance of a content object akismet information extractor
     suitable for the content object version $contentObjectVersion.
    */
    function akismetInformationExtractor( $version )
    {
	$ini = eZINI::instance( 'akismet.ini' );
	$infoExtractors = $ini->variable( 'InformationExtractorSettings', 'ExtractableClasses' );
	$debug = eZDebug::instance();
	$comment = array();
	
	$contentObject = $version->attribute( 'contentobject' );
	$classIdentifier = $contentObject->attribute( 'class_identifier' );
	
	//Do not uncomment the line below - it could generate false spam warnings
	//$comment['type'] = $classIdentifier;
	
	if ( !in_array( $classIdentifier, $infoExtractors ) )
	{
	    $debug->writeWarning( 'No Akismet content object information extractor configured for content class with identifier: ' . $classIdentifier );
	    return false;
	}
	
	$iniGroup = $classIdentifier.'_AkismetSettings';
	
	if ( !$ini->hasGroup( $iniGroup ) )
	{
	    $debug->writeWarning( 'No configuration group in upload.ini for class identifier %class_identifier: ' . $classIdentifier );
	    return false;
        }
        
	
	$authorIdentifier = $ini->variable( $classIdentifier . '_AkismetSettings', 'AuthorAttribute' );
	$emailIdentifier = $ini->variable( $classIdentifier . '_AkismetSettings', 'EmailAttribute' );
	$websiteIdentifier = $ini->variable( $classIdentifier . '_AkismetSettings', 'WebsiteAttribute' );
	$bodyIdentifier = $ini->variable( $classIdentifier . '_AkismetSettings', 'BodyAttribute' );
	$attributeIdentifiers = array( 'author' => $authorIdentifier, 'email' => $emailIdentifier, 'website' => $websiteIdentifier, 'body' => $bodyIdentifier );
	$contentObjectAttributes = $contentObject->contentObjectAttributes();
	$loopLenght = count( $contentObjectAttributes );
	for( $i = 0; $i < $loopLenght; $i++ )
	{
		if ( in_array($contentObjectAttributes[$i]->attribute( 'contentclass_attribute_identifier' ), array_values( $attributeIdentifiers ) ) )
		{
			$key = array_search($contentObjectAttributes[$i]->attribute( 'contentclass_attribute_identifier' ), $attributeIdentifiers); 
			if ( $contentObjectAttributes[$i]->hasContent() )
			{
				$value = $contentObjectAttributes[$i]->attribute( 'content' );
				
				switch ( $datatypeString = $contentObjectAttributes[$i]->attribute( 'data_type_string' ) )
				{
				
					case 'ezuser':
					{
						if ( $authorIdentifier == $emailIdentifier )
						{
							$comment['author'] = $value->attribute( 'login' );
							$comment['email'] = $value->attribute( 'email' );
							break;
						
						}
						else
						{
							if ( $key == "author" )
							{
								$comment[$key] = $value->attribute( 'login' );
								break;
							}
							else
							{
								$comment[$key] = $value->attribute( 'email' );
								break;
							}
						}
					}
					break;
					
					case 'ezauthor':
					{
						if ( $authorIdentifier == $emailIdentifier )
						{
							foreach ($value as $author)
							{
								$comment['author'] = $author[0]['name'];
								$comment['email'] = $author[0]['email'];
								break;
							}
						}
						else
						{
						
							foreach ($value as $author)
							{	
								if ( $key == "author" )
								{
									$comment[$key] = $author[0]['name'];
									break;
								}
								else
								{
									$comment[$key] = $author[0]['email'];	
									break;
								}
							}
						}
					}
					break;
					
					case 'ezxml':
					{						
						if ( $value instanceof eZXMLText )
						{
							$outputHandler =  $value->attribute( 'output' );
							$itemDescriptionText = $outputHandler->attribute( 'output_text' );
							$value = substr(strip_tags($itemDescriptionText),0,1000);
						}
						$comment[$key] = $value;
					}
					break;
					
					default:
					{
						$comment[$key] = $value;
					}
					break;
				}
				
				unset( $attributeIdentifiers[$key] );
				
			}
			else
			{
				$comment[$key] = false;
			}
			
		}		
	}
	
	return $comment;
    }
    
 
    function getExtractableClassList()
    {
        $ini = eZINI::instance( 'akismet.ini' );
        $extractableClasses = $ini->variable( 'InformationExtractorSettings', 'ExtractableClasses' );
        return $extractableClasses;
    }

    function getExtractableNodes( $limit = false, $offset = false )
    {
        $classList = eZContentObjectAkismet::getExtractableClassList();
        $params = array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => $classList,
            'SortBy' => array( array( 'published', false ) ),
            'Limit' => $limit,
            'Offset' => $offset
        );

        $nodes = eZContentObjectTreeNode::subTreeByNodeID( $params, 1 );
        return $nodes;
    }

    function getExtractableNodesCount()
    {
        $classList = eZContentObjectAkismet::getExtractableClassList();

        $params = array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => $classList
        );

	$node = eZContentObjectTreeNode::fetchNode( 1, 1);
        $nodeCount = $node->subTreeCount( $params, 1 );
        return $nodeCount;
    }
}

?>