<?php

class akismetInfo
{
    function info()
    {
        return array(
            'Name' => "Akismet extension for eZ Publish",
            'Version' => "1.1.0",
            'Copyright' => "Copyright (C) 2007 eZ systems AS; 2009 Contactivity BV; 1999-2012 Brookins Consulting",
            'Author' => "Contactivity BV, based on the Akismet extension by Kristof Coomans for eZ publish 3.x",
            'License' => "GNU General Public License v2.0",
            'info_url' => "http://projects.ez.no/akismet",
            'Includes the following third-party software' =>
                array(
                    'Name' => "Akismet PHP5 class",
                    'Version' => "0.4",
                    'Patches' => "Make submitSpam and submitHam methods return the response",
                    'Website' => 'http://www.miphp.net/blog/view/php4_akismet_class/',
                    'License' => "http://www.opensource.org/licenses/mit-license.php MIT License",
                    'Author' => "Bret Kuhns"
                )
        );
    }
}

?>