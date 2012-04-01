<?php /*

#This only works if you have an api, get it at: http://akismet.com/personal/
[AccountSettings]
APIKey=aaaabbb000111

[SiteSettings]
BlogURL=http://www.yoursite.com

[InformationExtractorSettings]
# Lists all classes that should make use of the Akismet service
ExtractableClasses[]=trackback
ExtractableClasses[]=comment

# Consists of the class identifier name with _AkismetSettings appended
# Maps the author, email, website and comment to eZ publish content class attributes identifiers
# Empty fields are allowed, currently supports the following datatypes:
# author, url, email, text line, text block, xml block, user 

[trackback_AkismetSettings]
AuthorAttribute=name
EmailAttribute=
WebsiteAttribute=url
BodyAttribute=excerpt

[comment_AkismetSettings]
AuthorAttribute=author2
EmailAttribute=author2
WebsiteAttribute=url2
BodyAttribute=message


*/ ?>