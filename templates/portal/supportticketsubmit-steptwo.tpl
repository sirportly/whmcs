<script language="JavaScript" type="text/javascript">
{literal}
var currentcheckcontent,lastcheckcontent;
jQuery(document).ready(function(){
    jQuery("#addfileupload").click(function () {
        jQuery("#fileuploads").append("<input type=\"file\" name=\"attachments[]\" size=\"50\"><br />");
        return false;
    });
});
{/literal}
{if $kbsuggestions}
{literal}
function getticketsuggestions() {
    currentcheckcontent = jQuery("#message").val();
    if (currentcheckcontent!=lastcheckcontent && currentcheckcontent!="") {
        $.post("submitticket.php", { action: "getkbarticles", text: currentcheckcontent },
        function(data){
            if (data) {
                jQuery("#searchresults").html(data);
                jQuery("#searchresults").slideDown();
            }
        });
        lastcheckcontent = currentcheckcontent;
	}
    setTimeout('getticketsuggestions();', 3000);
}
getticketsuggestions();
{/literal}
{/if}
</script>
{if $errormessage}
<div class="errorbox">{$errormessage}</div>
<br />
{/if}
<form action="{$smarty.server.PHP_SELF}?step=2" method="post" enctype="multipart/form-data" name="submitticket" id="submitticket">
  <input type="hidden" name="deptid" value="{$deptid}" />
  <table width="100%" cellspacing="1" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td width="120" class="fieldarea">{$LANG.supportticketsclientname}</td>
            <td>{if $loggedin}{$clientname}{else}
              <input type="text" name="name" size="30" value="{$name}" />
              {/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.supportticketsclientemail}</td>
            <td>{if $loggedin}{$email}{else}
              <input type="text" name="email" size="50" value="{$email}" />
              {/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.supportticketsdepartment}</td>
            <td>{$department}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.supportticketsticketsubject}</td>
            <td><input type="text" name="subject" size="60" value="{$subject}" /></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.supportticketsticketurgency}</td>
            <td><select name="priorityid">
  			      {foreach from=$priorities item=priority}
                  <option value="{$priority.id}"{if $priority.id eq $priorityid} selected="selected"{/if}>{$priority.name}</option>
              {/foreach}
                  </select></td>
          </tr>
          {if $relatedservices}
          <tr>
            <td class="fieldarea">{$LANG.relatedservice}</td>
            <td><select name="relatedservice">
                <option value="">{$LANG.none}</option>
                
{foreach from=$relatedservices item=relatedservice}

                <option value="{$relatedservice.id}">{$relatedservice.name} ({$relatedservice.status})</option>
                
{/foreach}

              </select></td>
          </tr>
          {/if}
          <tr>
            <td colspan="2" class="fieldarea"><textarea name="message" id="message" rows="12" cols="60" style="width:100%">{$message}</textarea></td>
          </tr>
          {foreach key=num item=customfield from=$customfields}
          <tr>
            <td class="fieldarea">{$customfield.name}</td>
            <td>{$customfield.input} {$customfield.description}</td>
          </tr>
          {/foreach}
          <tr>
            <td class="fieldarea">{$LANG.supportticketsticketattachments}</td>
            <td><input type="file" name="attachments[]" size="50" />
              <a href="#" id="addfileupload"><img src="images/add.gif" class="absmiddle" border="0" alt="" /> {$LANG.addmore}</a><br />
              <div id="fileuploads"></div>
              ({$LANG.supportticketsallowedextensions}: {$allowedfiletypes})</td>
          </tr>
      </table></td>
    </tr>
  </table>
  <br />
  <div id="searchresults" class="contentbox" style="display:none;"></div>
  {if $capatacha}
  <h2>{$LANG.captchatitle}</h2>
  <p>{$LANG.captchaverify}</p>
  {if $capatacha eq "recaptcha"}
  <div align="center">{$recapatchahtml}</div>
  {else}
  <p align="center"><img src="includes/verifyimage.php" align="middle" /> <input type="text" name="code" size="10" maxlength="5" /></p>
  {/if}
  {/if}
  <p align="center">
    <input type="submit" value="{$LANG.supportticketsticketsubmit}" />
  </p>
</form><br />