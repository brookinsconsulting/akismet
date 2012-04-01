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

function akismet_ContentActionHandler( $module, $http, $objectID )
{
    $object = eZContentObject::fetch( $objectID );
    $version = $object->attribute( 'current' );
    if ( $http->hasPostVariable( 'AkismetSubmitSpam' ) )
    {
        $user = eZUser::currentUser();
        $accessResult = $user->hasAccessTo( 'akismet', 'submit' );

        if ( $accessResult['accessWord'] === 'yes' )
        {
            $mainNode = $object->attribute( 'main_node' );
            $module->redirectTo( $mainNode->attribute( 'url_alias' ) );

            
            $akismetObject = new eZContentObjectAkismet();
            $comment = $akismetObject->akismetInformationExtractor( $version );
            
            if ( $comment)
            {
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

        $mainNode = $object->attribute( 'main_node' );
        $module->redirectTo( $mainNode->attribute( 'url_alias' ) );

        return true;
    }
}

?>