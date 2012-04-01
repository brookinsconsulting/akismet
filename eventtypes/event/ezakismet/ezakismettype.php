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
 
class eZAkismetType extends eZWorkflowEventType
{
    	const WORKFLOW_TYPE_STRING = 'ezakismet';
       
	function eZAkismetType()
	{
	    $this->eZWorkflowEventType( eZAkismetType::WORKFLOW_TYPE_STRING, ezi18n( 'extension/contactivity/akismet/workflow/event', "Akismet Spam filter" ) );
	    $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'before','after' ) ) ) );
	}

	function execute( $process, $event )
	{
		$parameters = $process->attribute( 'parameter_list' );
		$object = eZContentObject::fetch( $parameters['object_id'] );
		$versionID = $parameters['version'];

		if ( !$object )
		{
		    return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
		}

		$version = $object->version( $versionID );

		if ( !$version )
		{
		    return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
		}

		$akismetObject = new eZContentObjectAkismet();
		$comment = $akismetObject->akismetInformationExtractor( $version );

		if ( $comment )
		{
			$akismet = new eZAkismet( $comment ); 
			
			if ( $akismet )
			{
			    $isSpam = $akismet->isCommentSpam();
			    eZDebug::writeDebug($comment );
			    eZDebug::writeDebug( "this is spam: ". $isSpam );
			}
			else
			{
			   return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
			}


			if ( !$isSpam )
			{
				$response = $akismet->submitHam();
				return eZWorkflowType::STATUS_ACCEPTED;
			}
			return eZWorkflowType::STATUS_REJECTED;

		}
	}        
}

eZWorkflowEventType::registerEventType( eZAkismetType::WORKFLOW_TYPE_STRING, 'eZAkismetType' );
?>
