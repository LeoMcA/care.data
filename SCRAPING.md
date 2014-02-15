Plan
====

It's relatively straightforward to grab emails for an MP, given a postcode. Here are the steps necessary to do so:

1. Send a GET request to `http://findyourmp.parliament.uk/postcodes/` and append the full postcode to the end of the URL. Example URL: `http://findyourmp.parliament.uk/postcodes/IP286PZ`.
2. You'll get a 302 Found response similar to the below:

````text
HTTP/1.1 302 Found
Date: Sat, 15 Feb 2014 14:25:09 GMT
Server: Apache
Cache-Control: no-cache
Location: http://findyourmp.parliament.uk/constituencies/west-suffolk
Content-Length: 125
Status: 302 Found
Cache-Control: max-age=2592000
Expires: Mon, 17 Mar 2014 14:25:09 GMT
Vary: Accept-Encoding
Content-Type: text/html; charset=utf-8
````

3. Follow the location header and grab the given page.
4. Apply the `div.text > .row_item:nth-child(1) > .row_value > a` selector to get the name of the MP and a link to their page. Example element:

````html
<a href="http://www.parliament.uk/biographies/commons/Matthew-Hancock/4070" class="">Matthew Hancock</a>
````

5. Follow the link to the biography page and apply the selector `a[href^="mailto:"]`. You'll get a couple of emails and occassionally an invalid link to a site (e.g. "mailto:http://www.google.com"). It's advised you check to ensure these addresses are in fact valid.

6. There are usually a couple of addresses given on the page. We can either default to the first or allow the user to pick one from the list. There are usually relevant headings on the page that we can use to display alongside the emails. I find the `h3[id^="ctl00_ctl00_FormContent_SiteSpecificPlaceholder_PageContent_ctlContactDetails_rptPhysicalAddresses_"]` selector works pretty well.
