<!DOCTYPE html>
<html>
<head>
<title>Sirportly Frame</title>
<link href="{$systemurl}/modules/addons/sirportly/stylesheets/frame.css" media="screen" rel="stylesheet" type="text/css" />
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
      text-align: center;
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
        background-color: #EEEEEE;
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
  <tr><td width="33%" valign="top">

<div class="clientssummarybox">
<div class="title">Client Information</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="110">Full Name</td><td>{$client.firstname} {$client.lastname}</td></tr>
<tr><td>Company Name</td><td>{$client.companyname}</td></tr>
<tr class="altrow"><td>Email Address</td><td>{$client.email}</td></tr>
<tr><td>Address 1</td><td>{$client.address1}</td></tr>
<tr class="altrow"><td>Address 2</td><td>{$client.address2}</td></tr>
<tr><td>City</td><td>{$client.city}</td></tr>
<tr class="altrow"><td>State/Region</td><td>{$client.state}</td></tr>
<tr><td>Postcode</td><td>{$client.postcode}</td></tr>
<tr class="altrow"><td>Country</td><td>{$client.country}</td></tr>
<tr><td>Phone Number</td><td>{$client.phonenumber}</td></tr>
</table>
<ul>
</div>

</td><td width="33%" valign="top">

<div class="clientssummarybox">
<div class="title">Invoices/Billing</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="110">Paid</td><td>{$stats.numpaidinvoices} ({$stats.paidinvoicesamount})</td></tr>
<tr class="altrow"><td>Unpaid/Due</td><td>{$stats.numdueinvoices} ({$stats.dueinvoicesbalance})</td></tr>
<tr><td>Cancelled</td><td>{$stats.numcancelledinvoices} ({$stats.cancelledinvoicesamount})</td></tr>
<tr class="altrow"><td>Refunded</td><td>{$stats.numrefundedinvoices} ({$stats.refundedinvoicesamount})</td></tr>
<tr><td>Collections</td><td>{$stats.numcollectionsinvoices} ({$stats.collectionsinvoicesamount})</td></tr>
<tr class="altrow"><td><strong>Income</strong></td><td><strong>{$stats.income}</strong></td></tr>
<tr><td>Credit Balance</td><td>{$stats.creditbalance}</td></tr>
</table>
</div>

</td><td width="33%" valign="top">

<div class="clientssummarybox">
<div class="title">Products/Services</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="140">Shared Hosting</td><td>{$stats.productsnumactivehosting} ({$stats.productsnumhosting} Total)</td></tr>
<tr class="altrow"><td>Reseller Hosting</td><td>{$stats.productsnumactivereseller} ({$stats.productsnumreseller} Total)</td></tr>
<tr><td>VPS/Server</td><td>{$stats.productsnumactiveservers} ({$stats.productsnumservers} Total)</td></tr>
<tr class="altrow"><td>Product/Service</td><td>{$stats.productsnumactiveother} ({$stats.productsnumother} Total)</td></tr>
<tr><td>Domains</td><td>{$stats.numactivedomains} ({$stats.numdomains} Total)</td></tr>
<tr class="altrow"><td>Accepted Quotes</td><td>{$stats.numacceptedquotes} ({$stats.numquotes} Total)</td></tr>
<tr><td>Affiliate Signups</td><td>{$stats.numaffiliatesignups}</td></tr>
</table>
</div>

</td></table>


<h2>Services</h2>

<table width="100%" class="datatable">
  <tr>
    <th>Order #</th>
    <th>Product Name</th>
    <th>Billing Cycle</th>
    <th>Amount</th>
    <th>Status</th>

{foreach from=$products item=product}
  <tr>
    <td>{$product.orderid}</td>
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


</div>
</body>
</html>