<?php

/*!
 Implementations of this interface retrieve information from a content object version.
 This information will be used for spam tests, submitting new spam or submitting
 false positives (ham)
*/

class eZContentObjectAkismetInformationExtractor
{
    var $ContentObjectVersion;

    function eZContentObjectAkismetInformationExtractor( &$contentObjectVersion )
    {
        $this->ContentObjectVersion =& $contentObjectVersion;
    }

    /*!
      \return the name of the author
    */
    function getAuthor()
    {
    }

    /*
     \return the body
    */
    function getBody()
    {
    }

    /*i
      \return the e-mail address of the author
    */
    function getEmail()
    {
    }

    /*!
     \return the website of the author
    */
    function getWebsite()
    {
    }

    /*
     \return a comment array directly usable for spam testing or reporting
     spam or ham
    */
    function getCommentArray()
    {
        return array(
            'author' => $this->getAuthor(),
            'email' => $this->getEmail(),
            'body' => $this->getBody(),
            'website' => $this->getWebsite()
        );
    }
}

?>