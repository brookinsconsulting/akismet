<?php

include_once( 'kernel/classes/collaborationhandlers/ezapprove/ezapprovecollaborationhandler.php' );
include_once( 'kernel/classes/workflowtypes/event/ezapprove/ezapprovetype.php' );
include_once( 'kernel/classes/ezaudit.php' );

define( "EZ_WORKFLOW_TYPE_AKISMET_ID", "ezakismet" );

/*!
 The eZAkismetType workflow event type will check wether a content object version is recognized as spam by using
 the Akismet web service. If the test is affirmitive, the object will have to be approved before it is published.
 The default approve collaboration handler is used. If the approver approves the content, the content object will be
 reported as a false positive to the Akismet web service.
*/
class eZAkismetType extends eZApproveType
{
    function eZAkismetType()
    {
        $this->eZWorkflowEventType( EZ_WORKFLOW_TYPE_AKISMET_ID, ezi18n( 'kernel/workflow/event', "Akismet spam test" ) );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'before' ) ) ) );
    }

    function execute( &$process, &$event )
    {
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $process, 'eZAkismetType::execute' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $event, 'eZAkismetType::execute' );
        $parameters = $process->attribute( 'parameter_list' );
        $versionID =& $parameters['version'];
        $object = eZContentObject::fetch( $parameters['object_id'] );

        if ( !$object )
        {
            eZDebugSetting::writeError( 'kernel-workflow-approve', $parameters['object_id'], 'eZAkismetType::execute' );
            return EZ_WORKFLOW_TYPE_STATUS_WORKFLOW_CANCELLED;
        }

        // version option checking
        $version_option = $event->attribute( 'version_option' );
        if ( ( $version_option == EZ_APPROVE_VERSION_OPTION_FIRST_ONLY and $parameters['version'] > 1 ) or
             ( $version_option == EZ_APPROVE_VERSION_OPTION_EXCEPT_FIRST and $parameters['version'] == 1 ) )
        {
            return EZ_WORKFLOW_TYPE_STATUS_ACCEPTED;
        }

        /*
          If we run event first time ( when we click publish in admin ) we do not have user_id set in workflow process,
          so we take current user and store it in workflow process, so next time when we run event from cronjob we fetch
          user_id from there.
         */
        if ( $process->attribute( 'user_id' ) == 0 )
        {
            $user = eZUser::currentUser();
            $process->setAttribute( 'user_id', $user->id() );
        }
        else
        {
            $user = eZUser::instance( $process->attribute( 'user_id' ) );
        }

        $userGroups = array_merge( $user->attribute( 'groups' ), array( $user->attribute( 'contentobject_id' ) ) );
        $workflowSections = explode( ',', $event->attribute( 'data_text1' ) );
        $workflowGroups = $event->attribute( 'data_text2' ) == '' ? array() : explode( ',', $event->attribute( 'data_text2' ) );
        $editors =        $event->attribute( 'data_text3' ) == '' ? array() : explode( ',', $event->attribute( 'data_text3' ) );
        $approveGroups =  $event->attribute( 'data_text4' ) == '' ? array() : explode( ',', $event->attribute( 'data_text4' ) );
        $languageMask = $event->attribute( 'data_int2' );

        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $user, 'eZAkismetType::execute::user' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $userGroups, 'eZAkismetType::execute::userGroups' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $editors, 'eZAkismetType::execute::editor' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $workflowSections, 'eZAkismetType::execute::workflowSections' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $workflowGroups, 'eZAkismetType::execute::workflowGroups' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $languageMask, 'eZAkismetType::execute::languageMask' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $object->attribute( 'section_id'), 'eZAkismetType::execute::section_id' );

        $section = $object->attribute( 'section_id' );
        $correctSection = false;

        if ( !in_array( $section, $workflowSections ) && !in_array( -1, $workflowSections ) )
        {
            $assignedNodes = $object->attribute( 'assigned_nodes' );
            if ( $assignedNodes )
            {
                foreach( $assignedNodes as $assignedNode )
                {
                    $parent = $assignedNode->attribute( 'parent' );
                    $parentObject = $parent->object();
                    $section = $parentObject->attribute( 'section_id');

                    if ( in_array( $section, $workflowSections ) )
                    {
                        $correctSection = true;
                        break;
                    }
                }
            }
        }
        else
            $correctSection = true;

        $inExcludeGroups = count( array_intersect( $userGroups, $workflowGroups ) ) != 0;

        $userIsEditor = ( in_array( $user->id(), $editors ) ||
                          count( array_intersect( $userGroups, $approveGroups ) ) != 0 );

        // All languages match by default
        $hasLanguageMatch = true;
        if ( $languageMask != 0 )
        {
            // Examine if the published version contains one of the languages we
            // match for.
            $version = $object->version( $versionID );
            // If the language ID is part of the mask the result is non-zero.
            $languageID = (int)$version->attribute( 'initial_language_id' );
            $hasLanguageMatch = (bool)( $languageMask & $languageID );
        }

        if ( $hasLanguageMatch and
             !$userIsEditor and
             !$inExcludeGroups and
             $correctSection )
        {
            $collaborationID = false;
            $db = & eZDB::instance();
            $taskResult = $db->arrayQuery( 'select workflow_process_id, collaboration_id from ezapprove_items where workflow_process_id = ' . $process->attribute( 'id' )  );
            if ( count( $taskResult ) > 0 )
                $collaborationID = $taskResult[0]['collaboration_id'];

            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $collaborationID, 'approve collaborationID' );
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $process->attribute( 'event_state'), 'approve $process->attribute( \'event_state\')' );
            if ( $collaborationID === false )
            {
                /*
                  only create approve event when Akismet does recognize the published content as spam
                */
                if ( !eZAkismetType::isSpam( $object, $versionID ) )
                {
                    eZAudit::writeAudit( 'akismet', array( 'Action' => 'not recognized as spam',
                                                           'Object' => $object->attribute( 'id' ),
                                                           'Version' => $versionID ) );

                    return EZ_WORKFLOW_TYPE_STATUS_ACCEPTED;
                }

                eZAudit::writeAudit( 'akismet', array( 'Action' => 'recognized as spam',
                                                       'Object' => $object->attribute( 'id' ),
                                                       'Version' => $versionID ) );

                /* Get user IDs from approve user groups */
                $ini = eZINI::instance();
                $userClassIDArray = array( $ini->variable( 'UserSettings', 'UserClassID' ) );
                $approveUserIDArray = array();
                foreach( $approveGroups as $approveUserGroupID )
                {
                    if (  $approveUserGroupID != false )
                    {
                        $approveUserGroup = eZContentObject::fetch( $approveUserGroupID );
                        if ( isset( $approveUserGroup ) )
                        {
                            foreach( $approveUserGroup->attribute( 'assigned_nodes' ) as $assignedNode )
                            {
                                $userNodeArray =& $assignedNode->subTree( array( 'ClassFilterType' => 'include',
                                                                                     'ClassFilterArray' => $userClassIDArray,
                                                                                     'Limitation' => array() ) );
                                foreach( $userNodeArray as $userNode )
                                {
                                   $approveUserIDArray[] = $userNode->attribute( 'contentobject_id' );
                                }
                            }
                        }
                    }
                }
                $approveUserIDArray = array_merge( $approveUserIDArray, $editors );
                $approveUserIDArray = array_unique( $approveUserIDArray );

                $this->createApproveCollaboration( $process, $event, $user->id(), $object->attribute( 'id' ), $versionID, $approveUserIDArray );
                $this->setInformation( "We are going to create approval" );
                $process->setAttribute( 'event_state', EZ_APPROVE_COLLABORATION_CREATED );
                $process->store();
                eZDebugSetting::writeDebug( 'kernel-workflow-approve', $this, 'approve execute' );
                return EZ_WORKFLOW_TYPE_STATUS_DEFERRED_TO_CRON_REPEAT;
            }
            else if ( $process->attribute( 'event_state') == EZ_APPROVE_COLLABORATION_NOT_CREATED )
            {
                eZApproveCollaborationHandler::activateApproval( $collaborationID );
                $process->setAttribute( 'event_state', EZ_APPROVE_COLLABORATION_CREATED );
                $process->store();
                eZDebugSetting::writeDebug( 'kernel-workflow-approve', $this, 'approve re-execute' );
                return EZ_WORKFLOW_TYPE_STATUS_DEFERRED_TO_CRON_REPEAT;
            }
            else //EZ_APPROVE_COLLABORATION_CREATED
            {
                $this->setInformation( "we are checking approval now" );
                eZDebugSetting::writeDebug( 'kernel-workflow-approve', $event, 'check approval' );
                $workflowStatus = $this->checkApproveCollaboration(  $process, $event );
                if ( $workflowStatus == EZ_WORKFLOW_TYPE_STATUS_ACCEPTED )
                {
                    eZAudit::writeAudit( 'akismet', array( 'Action' => 'submitting ham',
                                         'Object' => $object->attribute( 'id' ),
                                         'Version' => $versionID ) );
                    $this->submitHam( $object, $versionID );
                }

                return $workflowStatus;
            }
        }
        else
        {
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $workflowSections , "we are not going to create approval " . $object->attribute( 'section_id') );
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $userGroups, "we are not going to create approval" );
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $workflowGroups,  "we are not going to create approval" );
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $user->id(), "we are not going to create approval "  );
            return EZ_WORKFLOW_TYPE_STATUS_ACCEPTED;
        }
    }

    function isSpam ( $object, $versionID )
    {
        $version =& $object->version( $versionID );

        if ( !is_object( $version ) )
        {
            return false;
        }

        include_once( 'extension/akismet/classes/ezcontentobjectakismet.php' );
        $infoExtractor = eZContentObjectAkismet::getInfoExtractor( $version );
        if ( !$infoExtractor )
        {
            return false;
        }

        $debug = eZDebug::instance();

        $comment = $infoExtractor->getCommentArray();
        $debug->writeDebug( $comment, 'eZAkismetType::isSpam' );

        include_once( 'extension/akismet/classes/ezakismet.php' );
        $akismet = new eZAkismet( $comment );

        if ( $akismet->errorsExist() )
        {
            return false;
        }

        $isSpam = $akismet->isSpam();
        $debug->writeDebug( $isSpam ? 'Recognized as spam.' : 'Not recognized as spam', 'eZAkismetType::isSpam' );

        return $isSpam;
    }

    function submitHam( $object, $versionID )
    {
        $version =& $object->version( $versionID );

        if ( !is_object( $version ) )
        {
            return false;
        }

        include_once( 'extension/akismet/classes/ezcontentobjectakismet.php' );
        $infoExtractor = eZContentObjectAkismet::getInfoExtractor( $version );
        if ( !$infoExtractor )
        {
            return false;
        }

        $comment = $infoExtractor->getCommentArray();

        include_once( 'extension/akismet/classes/ezakismet.php' );
        $akismet = new eZAkismet( $comment );

        if ( $akismet->errorsExist() )
        {
            return false;
        }

        $response = $akismet->submitHam();
        $debug = eZDebug::instance();
        $debug->writeNotice( $response, 'Akismet web service response' );

        return true;
    }
}

eZWorkflowEventType::registerType( EZ_WORKFLOW_TYPE_AKISMET_ID, "ezakismettype" );

?>
