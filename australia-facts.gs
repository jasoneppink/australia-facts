
function doGet(e){
  var data = getRandomTweet(e.parameter['id']);
  return ContentService.createTextOutput(data);
}

function doPost(e){
  var data = getRandomTweet(e.parameter['id']);
  return ContentService.createTextOutput(data);
}


function getRandomTweet(id) {
  //get number of rows
  var sheets = SpreadsheetApp.openById("ADD-YOUR-SPREADSHEET-ID-HERE");
  var sheet = sheets.getSheets()[0];
  var lastRow = sheet.getLastRow();
  
  //remove all potential tweets that have already been used
  var allRows = sheet.getRange(2, 1, lastRow, 3).getValues();
  var allEligibleRows = new Array()
  for (i=0, len = allRows.length-1; i < len; i++){
    if (allRows[i][2] == 0){
      allEligibleRows.push(allRows[i]);
    }
  }
  
  var numRows = allEligibleRows.length;
  
  //Alert! We're low on facts!
  if(numRows == 15){
    MailApp.sendEmail("YOUR.EMAIL@ADDRESS.COM", "YOUR SUBJECT LINE", "YOUR WARNING MESSAGE");
  }
  
  if(numRows > 0){
    //generate random number between 1 (inclusive) and lastRow (inclusive)
    var randomRow = Math.floor(Math.random() * (numRows - 1 + 1));
    Logger.log(randomRow);
    
    //get tweet from array
    var tweet = allEligibleRows[randomRow][0];

    //find index of random row in the spreadsheet and set "used" to 1
    for (i=0, len = allRows.length; i < len; i++){
      if (allRows[i][0] == tweet){
        sheet.getRange(i+2, 3).setValue("1");
      }
    }
    
    //return tweet to PHP script
    return tweet;
    
  } else {
    //Alert! We're out of facts!
    MailApp.sendEmail("YOUR.EMAIL@ADDRESS.COM", "YOUR SUBJECT LINE", "YOUR WARNING MESSAGE");
  }

}
