Akismet extension INSTALL
------------------------

The akismet extension is meant to provide spam protection in cases where content objects (comments, trackback, etc) can be added by visitors.


Steps to install and configure akismet extension
-------------------------

1. Unzip the files, copy them to the "extension/" directory of eZ Publish in the 'akismet' folder (you create);

2. Activate the extension in the admin interface or via direct ini changes

[ExtensionSettings]
ActiveExtensions[]=akismet

3. Create a settings overide of the akismet.ini.append.php settings file:

    1. Get api key at: http://akismet.com/personal/
    2. Add it to the APIKey setting
    2. Define your BlogURL
    3. Define all classes that should make use of the Akismet service in the InformationExtractorSettings setting
    4. Map 'author','email', 'website' and 'body' to the content class attribute identifiers of the classes above

       The format is:

       [classIdentifier_AkismetSettings]
       AuthorAttribute=classAttributeIdentifier

       for example:

       [comment_AkismetSettings]
       AuthorAttribute=author

4. Create a workflow with the " Event / Akismet Spam filter" event, called by the 'publish/before' trigger.


Administration Back end
-----------------------

The back-end is available at the uri: /akismet/reportspam

You can use this from the admin or user siteaccesses.
