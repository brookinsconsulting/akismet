<?php

class akismetInfo
{
    function info()
    {
        return array(
            'Name' => "Akismet eZ publish extension",
            'Version' => "0.x",
            'Copyright' => "Copyright (C) 2007 eZ systems AS",
            'Author' => "Kristof Coomans",
            'License' => "GNU General Public License v2.0",
            'Includes the following third-party software' =>
                array(
                    'Name' => "Akismet PHP4 class",
                    'Version' => "0.3.3",
                    'Patches' => "Make submitSpam and submitHam methods return the response",
                    'Website' => 'http://www.miphp.net/blog/view/php4_akismet_class/',
                    'License' => "http://www.opensource.org/licenses/mit-license.php MIT License",
                    'Author' => "Bret Kuhns"
                )
        );
    }
}

?>