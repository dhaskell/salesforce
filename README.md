# Salesforce PHP class

**IGNORE - UNFINISHED REPO**

This class was created to get around a specific problem that few/no other devs will probably face involving
the Salesforce CMS platform. 

My dev team was never really canonized at my workplace at the time this was written so we had no special 
access to any systems. However it was requested that we perform reporting based on information from our 
partition of Salesforce. I wrote this class to take advantage of the Salesforce URL structure and PHP's 
native cURL browser to extract data from their reporting platform programatically with no special access.

I do not maintain this code or plan to improve it (There is a lot of room for it) but I'm open to help 
anyone who wishes to fork it.


## How to use it

### Instantiating the object

Before you start pulling reports out of Salesforce with this class you'll want to verify/update the partition 
it will attempt to access. This probably shoul have been an argument in the constructor but our company only
had the one partition at the time. This can be changed in the constructor on line 25.

Once that's done you should be good to go. Instantiating a new instance of the class will establish a session
within salesforce.

```
<?php
	$salesforce = new salesforce($myUsername, $myPassword);
	
	if(count($salesforce->errors) == 0)
	{
		// Success!
	}
<?
```

### Retrieving a basic report

Now that your connected to Salesforce you can start running reports. Each report has a unique ID assigned 
to it once it's created. These can be tricky to find but trust me they're there. Once you have the id you 
can use the report method to retrieve the data.

```
<?php
	
	$report_id = "00OU0000001QQQQ"
	$salesforce = new salesforce($myUsername, $myPassword);
	
	if(count($salesforce->errors) == 0)
	{
		$reportResult = $salesforce->report($report_id);
		
		if(is_array($reportResult))
		{
			// Success!
		}
	}
<?
```

You'll notice the structure pretty quickly. It will come back with a multidimensional array who's first 
element is the report's columns. Each subsiquent element will be the reports contents.


### Filtering/searching reports

//TODO: This. I'll write up a tutorial later.