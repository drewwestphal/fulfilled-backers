# "Fulfilled Backers"

"Fulfilled Backers" is a hopefully not so facetious title for a "suite" of tools I developed for fulfillment and general backer management. Write me if you'd like to contribute to it.

The kickstarter specific code here originated with the [Code Monkey Save World kickstarter](http://www.kickstarter.com/projects/gregpak/code-monkey-save-world). Email templating, some utility functions, etc were pasted and ingloriously merged here from the [JoCo Cruise Crazy](https://jococruisecrazy.com/) booking engine and other projects of mine from the past few years. I was on the [Jonathan Coulton](http://www.jonathancoulton.com/) payroll while this was written, so credit goes to him for not a) taking ownership of my code and b) wanting our work to benefit others (I hope it does!). The idea to open source this project arose as part of our [SXSW panel](http://schedule.sxsw.com/2014/events/event_IAP23793) in 2014.

Generally, the code and DB schema here allow you to project, track and display the rewards owed to your backers and to send email updates about said rewards to those backers. On our kickstarter it was operated by me and by Anna B. Together we operate under the pseudonym "Scarface", for a variety of reasons, which a Jonathan Coulton fan should readily understand.

This project took place in the real world at least as much as online. We owe thanks to our fulfillment house and manufacturers. Referrals available on request.

Below the FAQs section I've written a  more detailed description of what you'll find in the files, from which you can infer in more detail the "features" this "software" "has". Please: use this software as a jumping off point only. All code is provided as is (though I think you can drop me a line through this site and I'd be interested to hear what you are working on). Caveat haxor.

Also, please, forgive my excessive use of quotes and general cynicism. This code has been published on a wave of margaritas from SXSW. 



## FAQs
**Doesn't kickstarter provide tools for backer communication and management *already*?**

Well, yeah... But there are a number of pretty significant limitations. I wouldn't recommend using the built-in tools for any project with more than a few hundred backers. Limitations include:

1. You can either send a message to EVERYONE or to ONE TIER ONLY. You can't send a message ot everyone to whom any other set of criteria applies (people getting a mug whose order hasn't shipped out yet, for example).
2. It's granular only to the backer tier level. You can see who has pledged for which tier, but you can't add in the products due to them by virtue of being a member of that tier or give them "add on" products.
3. You can't merge in variables from data you have (tracking numbers, submitted addresses, etc) to your email messages
4. You can't pull stats or reports or a combined file of everyone or update the files with your own data.
5. And more

The list goes on. Really, kickstarter let's you message your backers one by one, tier by tier, or all-in. That's it. You can keep track of some backer specific notes if you click on that backer (is there search on this? I don't even know.) and there's a checkbox for whether their order has been fulfilled entirely. That's the extent of the kicktstarter tool. It's one page with a bunch of javascript overlays.

Generally, I'd say this: Kickstarter has built an amazing platform for funding but backer support is not part of what they provide. You have to do that part on your own.

I built this thing to do that part for a specific and large project, any general application of it that might be dreamed up is a happy accident. It started as SQL tables to do stats so we could see what we'd ended up promising. Once we had those it made more sense to hook those tables up to a site than to build out or adopt some other solution.


**Why did you build this instead of using something like [BackerKit](https://backerkit.com/) or [Pledge Manager](http://www.pledgemanager.com/)?**

Pledge Manager still doesn't exist for the general public and BackerKit only *may* have when I started building this. But it started organically, as a bunch of tables I was using for projections. Then I hooked up more and more pieces to it and it was too late to switch to a paid tool by the time I found any.

**Why aren't you selling this or otherwise trying to monetize it?**

The aforementioned tools exist. If you want to pay someone, I'd check out their offerings. They seem pretty good and I kicked myself when I found out about BackerKit after building so much of this. Pretty much the answer is: 

1. I don't want to. 
2. My work on JoCo Cruise Crazy and at Jonathan Coulton Heavy Industries is pretty much full time.
3. Over the years I've used a ton of open source tools so, maybe, this is a small way I can "pay it forward", "give back", or other platitudes.
4. If you require some sort of kickstarter consultation and you'd like to work with me. Get in touch with me via github. I'd at least be interested in talking, time permitting.


## Features & File Structure

Basically backer_home.php serves the Backer Home page, which shows people the downloads they have requested and lets them confirm the constitution of their order. Backer home pages are identitied by a long random string and don't require a user account to access.

The "int" directory contains the tools we used on the project. General stats in stats.php. Note that the DB schema I chose was for easy querying using NATURAL JOIN and not performance. The queries related to the backer home page were performant enough due to caching, but the stats page by the time we finished required almost 17 seconds to load fresh.

Check out schema.sql. Contains the schema I used. Between that and the queries in stats should give you an idea of how most of the database can be used. Again this whole thing was created to serve our business purpose and not to be some grand commentary on proper DB design. I have no such commentaries to make, but even I know that this guy is pretty hodge podge.

Note that a lot of files still contain our URLs and I've gone through trying to relink things without mention of our specific server folder structure. Probably did not do a 100% perfect job at it.

Belows's the output of tree with comments and elisions.

	
	├── schema.sql -- the db schema
	├── cmsw.sql -- the products and tiers table filled out with example data... you'll need to provide your backer data... Please let me know if there's other sample data you are looking for
	├── backer_home.php -- the home page for backers, one big file, does what it should, does not do so elegantly. written in a fugue, probably.
	├── address.php -- for accepting address change submissions from backer_home
	
	├── docs -- this directory contains a few bits of useful business information we used for other parts of our project
	
	├── dl -- for handling downloads via Amazon S3
	│   ├── hashes.txt -- informational to backers
	│   ├── index.php -- serves the download
	
	├── int -- the backend		
	│   ├── backerimport.php -- script to pull in information from a standard kickstarter formatted zip file. Note that the file format may change and is pretty non-standard as is... so this works as of Feb 2014. This guy is pretty useful. 

	│   ├── kickstarterzips -- uploaded zips from kickstarter
	│   ├── search.php -- search for a backer, usable by non-technologists
	│   ├── stats.php -- pull stats and put in table form
	│   ├── submitquery.php -- this didn't work on this project and i just pulled reports from PHPMyAdmin
	│   └── summarizetiers.php -- list tiers in readable format

	│   ├── eml -- for the quasi "WYSIWYG" email templating tool. Check out the files for more info how to use, neessary GET variables, etc.
	│   │   ├── et_edit.php -- edit a template
	│   │   ├── et_preview.php -- preview and send a template... see Email prefixed tables. 
	│   │   ├── et_preview_new.php -- for sending emails using sendgrid in large batches--quite useful and I used this one a lot.
	│   │   └── tiny_mce -- TINY MCE, one of the versions
	│   ├── func.php -- functions used in the backend... ugly
	
	├── css -- standard bootstrap files
	├── js -- standard javascript libs from bootstrap etc
	├── lib
	│   ├── common.php -- general files, some pasted over from other projects
	│   ├── composer.json -- dependencies -- AWS and sendgrid
	│   ├── dl_links.php -- download links, requires some modifications here
	│   ├── et_code_v2.php -- send emails serially, doing merge token substitutions on the client side
	│   └── et_code_v3_chunking.php -- send emails in batches using the sendgrid api, doing merges on the sendgrid side
	└── themes -- bootstrap themes. this page was originally designed on divshot and this dir and others trace their lineage to the export from that site.
