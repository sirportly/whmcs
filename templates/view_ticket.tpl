{if $sirportly_error}
<div class="alert-message error">
    <p>An error occurred, please try again later.</p>
</div>
{/if}

{if $error}

<p>{$LANG.supportticketinvalid}</p>

{else}

{include file="$template/pageheader.tpl" title=$LANG.supportticketsviewticket|cat:' #'|cat:$tid}

{if $errormessage}
<div class="alert-message error">
    <p class="bold">{$LANG.clientareaerrors}</p>
    <ul>
        {$errormessage}
    </ul>
</div>
{/if}

<h2>{$subject}</h2>

<div class="ticketdetailscontainer">
    <div class="col4">
        <div class="internalpadding">
            {$LANG.supportticketsubmitted}
            <div class="detail">{$date}</div>
        </div>
    </div>
    <div class="col4">
        <div class="internalpadding">
            {$LANG.supportticketsdepartment}
            <div class="detail">{$department}</div>
        </div>
    </div>
    <div class="col4">
        <div class="internalpadding">
            {$LANG.supportticketspriority}
            <div class="detail">{$urgency}</div>
        </div>
    </div>
    <div class="col4">
        <div class="internalpadding">
            Status
            <div class="detail">{$status}</div>
        </div>
    </div>
    <div class="clear"></div>
</div>


<p><input type="button" value="{$LANG.clientareabacklink}" class="btn" onclick="window.location='supporttickets.php'" /> <input type="button" value="Reply" class="btn primary" onclick="jQuery('#replycont').slideToggle()" />{if $showclosebutton} <input type="button" value="Close Ticket" class="btn error" onclick="window.location='{$smarty.server.PHP_SELF}?tid={$tid}&amp;c={$c}&amp;closeticket=true'" />{/if}</p>

<div id="replycont" class="ticketreplybox{if !$smarty.get.postreply} hidden{/if}">
<form method="post" action="{$smarty.server.PHP_SELF}?tid={$tid}&amp;c={$c}" enctype="multipart/form-data" class="form-stacked">

    <fieldset>



	    <div class="clearfix">
		    <label for="message">{$LANG.contactmessage}</label>
			<div class="input">
			    <textarea name="replymessage" id="message" rows="12" class="fullwidth">{$replymessage}</textarea>
			</div>
		</div>

	    

    </fieldset>

    <p align="center"><input type="submit" value="{$LANG.supportticketsticketsubmit}" class="btn primary" /></p>

</form>
</div>

<div class="ticketmsgs">
{foreach from=$descreplies key=num item=reply}
    <div class="{if $reply.admin}admin{else}client{/if}header">
        <div style="float:right;">{$reply.date}</div>
        {if $reply.admin}
            {$reply.name} || {$LANG.supportticketsstaff}
        {elseif $reply.contactid}
            {$reply.name} || {$LANG.supportticketscontact}
        {elseif $reply.userid}
            {$reply.name} || {$LANG.supportticketsclient}
        {else}
            {$reply.name} || {$LANG.supportticketsclient}
        {/if}
    </div>
    <div class="{if $reply.admin}admin{else}client{/if}msg">

        {$reply.message}




    </div>
{/foreach}
</div>



<div id="replycont2" class="ticketreplybox hidden">
<form method="post" action="{$smarty.server.PHP_SELF}?tid={$tid}&amp;c={$c}&amp;postreply=true" enctype="multipart/form-data" class="form-stacked">

    <fieldset>

	    

	    <div class="clearfix">
		    <label for="message">{$LANG.contactmessage}</label>
			<div class="input">
			    <textarea name="replymessage" id="message" rows="12" class="fullwidth">{$replymessage}</textarea>
			</div>
		</div>

	   
    </fieldset>

    <p align="center"><input type="submit" value="{$LANG.supportticketsticketsubmit}" class="btn primary" /></p>

</form>
</div>

{/if}