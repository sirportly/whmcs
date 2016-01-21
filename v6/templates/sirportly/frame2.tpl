<!DOCTYPE html>
<html>
<head>
<title>Sirportly Frame</title>

<style type='text/css'>
{literal}


    /* --------------------------------------------------------------
       GLOBAL
    -------------------------------------------------------------- */


    body, td, th, div, input, select, textarea {
      font-family: Tahoma, Arial, Helvetica, sans-serif;
      font-size: 12px;
    }

    /* --------------------------------------------------------------
       TABLES
    -------------------------------------------------------------- */

    table.form {
      background-color: #fff;
      padding: 0px;
      border: 3px solid #E2E7E9;
        -moz-border-radius: 4px;
        -webkit-border-radius: 4px;
        -o-border-radius: 4px;
        border-radius: 4px;
    }
    table.form td {
      font-size: 12px;
    }
    table.form td.fieldlabel {
      background-color: #fff;
        text-align: right;
    }
    table.form td.fieldarea {
      background-color: #efefef;
        text-align: left;
    }
    table.datatable {
      padding: 0;
      margin: 0;
    }
    table.datatable th {
      background-color: #1A4D80;
      font-weight: bold;
      text-align: left;
        -moz-border-radius: 3px;
        -webkit-border-radius: 3px;
        -o-border-radius: 3px;
        border-radius: 3px;
    }
    table.datatable td {
      background-color: #FFF;
      border-bottom: 1px solid #EBEBEB;
      font-size: 12px;
    }
    table.datatable tr.rowhighlight td {
      background-color: #E0E8F3;
    }
    table.datatable tr:hover td {
      background-color: #EFF2F9;
    }
    table.datatable th, table.datatable th a, table.datatable th a:visited {
      color: #FFF;
      text-decoration: none;
    }
    table.datatable th a:hover {
      color: #FFF;
      text-decoration: underline;
    }
    table.datatable tr:hover td {
      background-color: #F3F3F3;
    }

    /* --------------------------------------------------------------
       CLIENT SUMMARY PAGE
    -------------------------------------------------------------- */

    .clientssummarybox {
        background-color: #1A4D80;
        padding: 10px;
        margin: 10px 2px 5px 3px;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        -o-border-radius: 5px;
        border-radius: 5px;
    }
    .clientssummarybox .title {
        text-align: center;
        padding: 0 0 10px 0;
        font-family: Arial;
        font-size: 16px;
        font-weight: bold;
        color: #1A4D80;
    }
    .clientssummarybox ul {
        list-style-type: none;
        margin: 10px 0 0 0;
    }
    .clientssummarybox ul li {
        margin: 0 0 2px -20px;
    }
    table.clientssummarystats {
        width: 100%;
        border: 4px solid #fff;
    }
    table.clientssummarystats td {
        background-color: #fff;
        font-size: 11px;
        padding-left: 4px;
        border-bottom: 1px dashed #ccc;
    }
    table.clientssummarystats tr.altrow td {
        background-color: #fff;
        padding-left: 4px;
        border-bottom: 1px dashed #ccc;
    }
    .clientsummaryactions {
        float:right;
        margin: 0;
        padding:4px 15px;
        border:1px dashed #BEDCF3;
        background-color:#EDF5FC;
        font-size:14px;
        -moz-border-radius: 4px;
        -webkit-border-radius: 4px;
        -o-border-radius: 4px;
        border-radius: 4px;
    }
    {/literal}
</style>
</head>
<body>
<div class='frame'>
  <table width="100%">
  <tr><td width="300" valign="top">

<img src="{$systemurl}assets/img/logo.png" align="center" height="45">

<div class="clientssummarybox">

<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="110">Customer Name</td><td>{$client.firstname} {$client.lastname}</td></tr>
<tr><td>Company Name</td><td>{$client.companyname}</td></tr>
<tr><td>Phone Number</td><td>{$client.phonenumber}</td></tr>

<tr><td colspan=2>&nbsp;</td></tr>

<tr><td><strong>Unpaid Invoices</td><td><strong>{$stats.numdueinvoices}</td></tr>
<tr><td><strong>Unpaid Total</td><td><strong>{$stats.dueinvoicesbalance}</td></tr>

</table>
<br>

<center><a href="{$systemurl}admin/clientssummary.php?userid={$client.id}" target="new-window"><img src="{$systemurl}assets/img/whmcs.png" border="0"></a>

</div>

</td><td valign="top">

<h2>Services</h2>

<table width="100%" class="datatable">
  <tr>
    <th>Domain</th>
    <th>Product Name</th>
    <th>Billing Cycle</th>
    <th>Amount</th>
    <th>Status</th>

{foreach from=$products item=product}
  <tr>
    <td><a href="{$systemurl}admin/clientsservices.php?id={$product.id}" target="new-window">{$product.domain}</a></td>
    <td>{$product.groupname} - {$product.name}</td>
    <td>{$product.billingcycle}</td>
    <td>{$product.recurringamount} {$client.currency_code}</td>
    <td>{$product.status}</td>
  </tr>

{foreachelse}
  <tr>
    <td colspan="5">This client doesn't have any services</td>
  </tr>
{/foreach}
</table>

<h2>Domains</h2>

<table width="100%" class="datatable">
  <tr>
    <th>Domain</th>
    <th>Registrar</th>
    <th>Expiry Date</th>
    <th>Billing Cycle</th>
    <th>Amount</th>
    <th>Status</th>

{foreach from=$domains item=domain}
  <tr>
    <td>{$domain.domainname}</td>
    <td>{$domain.registrar}</td>
    <td>{$domain.expirydate}</td>
    <td>{$product.billingcycle}</td>
    <td>{$domain.recurringamount} {$client.currency_code}</td>
    <td>{$domain.status}</td>
  </tr>

{foreachelse}
  <tr>
    <td colspan="5">This client doesn't have any domains</td>
  </tr>
{/foreach}
</table>

<br><br>

</div>
</body>
</html>