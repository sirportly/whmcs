<div class="page-header">
    <div class="styled_title"><h1>Open Ticket</h1></div>
</div>

{if $sirportly_error}
<div class="alert-message error">
    <p>An error occurred, please try again later.</p>
</div>
{/if}
<p>{$LANG.supportticketsheader}</p>

<br />

<div class="row">
    <div class="center80">
    {foreach from=$departments item=department}
        <div class="col2half">
            <div class="contentpadded">
                <p><div class="fontsize2"><img src="images/emails.gif" /> &nbsp;<strong><a href="{$smarty.server.PHP_SELF}?step=2&amp;deptid={$department.id}">{$department.name}</a></strong></div></p>
    			{if $department.description}<p>{$department.description}</p>{/if}
            </div>
        </div>
    {/foreach}
    </div>
</div>

<br />
<br />
<br />