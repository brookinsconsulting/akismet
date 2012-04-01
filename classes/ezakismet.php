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

class eZAkismet extends Akismet
{
        
    function __construct($comment) 
    {
        $ini = eZINI::instance( 'akismet.ini' );
        $blogURL = $ini->variable( 'SiteSettings', 'BlogURL' );
        $apiKey = $ini->variable( 'AccountSettings', 'APIKey' );
 	parent::__construct( $blogURL, $apiKey );
 	
 	
 	if ( isset( $comment['permalink'] ) )
	{
	 	parent::setPermalink( $comment['permalink'] );	
	}
	
	if ( $comment['type'] )
	{
		parent::setCommentType( $comment['type'] );
	}
 	
 	if ( isset( $comment['author'] ) )
 	{
 		parent::setCommentAuthor( $comment['author'] );
	}
	else
	{
		parent::setCommentAuthor('' );
	}
	
	if ( isset( $comment['email'] ) )
	{
		parent::setCommentAuthorEmail( $comment['email'] );
	}
	
	if ( $comment['website'] )
	{
		parent::setCommentAuthorURL( $comment['website'] );
	}
	
	if ( $comment['body'] )
	{
		parent::setCommentContent( $comment['body']  );
	}
	
    }
}

?>