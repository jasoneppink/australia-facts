# Australia Facts

Australia Facts tweets two facts about Australia every day, upside down, to [@AustraliaFacts_](https://twitter.com/australiafacts_). Sometimes the fact is rendered as an image, in which case it is posted to Tumblr at [australiafacts.tumblr.com](https://australiafacts.tumblr.com), too. ([Twitteroauth](https://github.com/abraham/twitteroauth) is required to post to Twitter; Tumblr posting is built into the script.)

Australia Facts runs from a Google Spreadsheet using [Google AppScript](https://developers.google.com/apps-script/), which makes it easy to compile facts collaboratively. When a fact is randomly selected by the script, it is marked as "used" so it is not selected again. When only a few facts remain, an email is sent to alert the project owner.

The script expects a spreadsheet constructed as below:
![spreadsheet example](https://github.com/jasoneppink/australia-facts/blob/master/spreadsheet-sample.png)

Image are created with ImageMagick using [pre-selected background images](https://github.com/jasoneppink/australia-facts/tree/master/images). A fact has a 1-in-6 chance of becoming an image, unless it contains a 2 or a 7, in which case it always becomes an image. (We were unsatisfied with the options for upside down 2s and 7s.)

A cron job randomly publish facts inside a given timeframe.
```
#between 11am and 12pm
0 11 * * * perl -le 'sleep rand 3600' && php /DIRECTORY/TO/australiafacts.php
#between 5pm and 7pm
0 17 * * * perl -le 'sleep rand 7200' && php /DIRECTORY/TO/australiafacts.php
```

Australia Facts was made by [Jason Eppink](http://jasoneppink.com) and [Larissa Hayden](http://www.larissahayden.com/).
