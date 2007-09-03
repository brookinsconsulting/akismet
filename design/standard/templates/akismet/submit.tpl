<form action={'akismet/submit'|ezurl} method="post">
<table class="list" cellspacing="0">
<tr>
    <th></th>
    <th>Name</th>
    <th>Published</th>
</tr>
{foreach $nodes as $node sequence array('bglight','bgdark') as $sequence}
<tr class="{$sequence}">
    <td><input type="checkbox" name="ObjectIDList[]" value="{$node.contentobject_id}" /></td>
    <td><a href={$node.url_alias|ezurl}>{$node.name|wash}</a></td>
    <td>{$node.object.published|l10n( shortdatetime )}</td>
</tr>
{/foreach}
</table>

<div class="button-block">
    <input type="submit" value="Report as spam" name="SpamSubmitButton" />
</div>

</form>

<div class="context-toolbar">
{include name=navigator
         uri='design:navigator/google.tpl'
         page_uri='akismet/submit'
         item_count=$nodes_count
         view_parameters=$view_parameters
         item_limit=$limit}
</div>
