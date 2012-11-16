{include file="$template/pageheader.tpl" title=$LANG.navopenticket}

{if $sirportly_error}
<div class="alert-message error">
    <p>An error occurred, please try again later.</p>
</div>
{/if}

<script language="javascript">
var currentcheckcontent,lastcheckcontent;
{if $kbsuggestions}
{literal}
function getticketsuggestions() {
    currentcheckcontent = jQuery("#message").val();
    if (currentcheckcontent!=lastcheckcontent && currentcheckcontent!="") {
        jQuery.post("submitticket.php", { action: "getkbarticles", text: currentcheckcontent },
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
<div class="alert-message error">
    <p class="bold">{$LANG.clientareaerrors}</p>
    <ul>
        {$errormessage}
    </ul>
</div>
{/if}

<form name="submitticket" method="post" accept-charset="UTF-8" action="{$smarty.server.PHP_SELF}?step=2" enctype="multipart/form-data" class="form-stacked">
  <input name="utf8" type="hidden" value="&#x2713;" />

    <fieldset>


        <div class="row">
    	    <div class="clearfix">
    		    <label for="subject">{$LANG.supportticketsticketsubject}</label>
    			<div class="input">
    			    <input class="xlarge" type="text" name="subject" id="subject" value="{$subject}" style="width:95%;" />
    			</div>
    		</div>
		</div>
        <div class="row">
            <div class="multicol">
                <div class="clearfix">
        		    <label for="name">{$LANG.supportticketsdepartment}</label>
        			<div class="input">
        			    <select name="deptid">
                        {foreach from=$departments item=department}
                            <option value="{$department.id}"{if $department.id eq $deptid} selected="selected"{/if}>{$department.name}</option>
                        {/foreach}
                        </select>
        			</div>
        		</div>
    		</div>

            <div class="multicol">
        	    <div class="clearfix">
        		    <label for="priority">{$LANG.supportticketspriority}</label>
        			<div class="input">
        			    <select name="urgency" id="priority">
        			      {foreach from=$priorities item=priority}
                        <option value="{$priority.id}"{if $priority.id eq $priorityid} selected="selected"{/if}>{$priority.name}</option>
                    {/foreach}
                        </select>
        			</div>
        		</div>
    		</div>
        </div>

	    <div class="clearfix">
		    <label for="message">{$LANG.contactmessage}</label>
			<div class="input">
			    <textarea name="message" id="message" rows="12" class="fullwidth">{$message}</textarea>
			</div>
		</div>
{foreach key=num item=customfield from=$customfields}
	    <div class="clearfix">
		    <label for="customfield{$customfield.id}">{$customfield.name}</label>
			<div class="input">
			    {$customfield.input} {$customfield.description}
			</div>
		</div>
{/foreach}
	   

    </fieldset>

<div id="searchresults" class="contentbox" style="display:none;"></div>


<div class="actions">
    <input class="btn primary" type="submit" name="save" value="{$LANG.supportticketsticketsubmit}" />
    <input class="btn" type="reset" value="{$LANG.cancel}" onclick="window.location='supporttickets.php'" />
</div>

</form>