<head>

<style>
body {background-color: powderblue;}
event   {display: none;}
    .tok {display: inline;}
timepoint {}
    timepoint:hover .ctok { visibility: visible;}
    
table {} .concordances {font-family:monospace;}
div {} .results {max-height:400px; overflow-y:auto; max-width:800px;}


</style>
<!-- This portion of the styling is required to make the webpage display results correctly -->
<style>
td {}
.pre {text-align:right; max-width: 30ch;white-space: nowrap;overflow: hidden;direction: rtl;}
.mid {text-align:center; font-weight:bold;}
.post {text-align:left; max-width: 30ch;white-space: nowrap;overflow: hidden; }

</style>



</head>
<h1> Welcome to JankySearch! </h1>
    <p>
    </p>

    <p id="output"></p>

<form action="index.php" method="get">
Your Search Query: <input type="text" name="searchQuery">
<label for="searchTag">Search within:</label>
<select name="searchTag" id="searchTag">
  <option value="$tok=">tok (default)</option>
  <option value="$ctok=">ctok</option>
  <option value="$lem=">lemma</option>
  <option value="$th1=">TH1</option>
</select>
<input type="checkbox" id="capSensitive" name="capSensitive" value="T" checked>
<label for="capSensitive"> Search is capitalization sensitive?</label><br>


<input type="submit">
</form>
This page can search through a corpus for different phrases with specific annotation tags. For instance, if you were interested in looking at 
<ul>
    <li> All instance of the word "die", you would search for "die" </li>
    <li> All instance of words beginning with "Lieblings", you would search for "Leiblings.*" </li>
    <li> All instances of "ist (any word) Hund" you would search for "ist .* Hund" </li>
    <li> You can add several criteria to a single lemma by joining them with "WITH". For instances, all instances of "mein Vater" where the speaker made a grammatical error in the word mein, you would search for "mein WITH $g=G_PPOSAT_cha Vater" </li>
</ul>
<p> Here are the tags that you can currently search.</p>
<ul>
    
    <li> $g is grammar error </li>
    <li> $cpos is common part of speech </li>
    <li> $o is orthography </li>
    <li> $lg is language specific POS </li>
    <li> $tok, $ctok, $lem, $th1 </li>
</ul>
<p> Additionally note that all searches are case and accent sensitive --- even at the start of a sentence! </p>
<p> Please select how you would like your search results displayed </p>
<div id="btn">
<div >
        <input type="radio" name="size" value="TH1" id="TH1Selector">
        <label for="TH1Selector">TH1</label>
    </div>
    <div>
        <input type="radio" name="size" value="ctok" id="ctokSelector" checked="checked">
        <label for="ctokSelector">ctok</label>
    </div>
    <div>
        <input type="radio" name="size" value="tok" id="tokSelector" >
        <label for="tokSelector">tok</label>
    </div>
    <div>
        <input type="radio" name="size" value="lemma" id="lemmaSelector">
        <label for="lemmaSelector">lemma</label>
    </div>
</div>

<?php
include 'corpusSearcherTools.php';


if(empty($_GET["searchQuery"])){
    $searchQuery="Default";
}
else{
    $searchQuery=$_GET["searchQuery"];
}
if(empty($_GET["searchTag"])){
    $defaultTier="$th1=";
}
else{
    $defaultTier=$_GET["searchTag"];
}
if(empty($_GET["capSensitive"])){
    $capChoice="i";
}
else{
    $capChoice="";
}

echo("<p>You searched for:<tt>'".$searchQuery."'</tt>. Let me get your search results for you... here they are!.");


#This regular expression will capture the starting timepoint index and ending timepoint index of the phrase you searched for.
$regex=generateRegex($searchQuery,$capChoice,$defaultTier);


$directory="searchableFiles";
$fileList= array_diff(scandir($directory), array('..', '.'));
echo("<div class='results'><table class='concordances'>\n");
$lineCount=0;
$fileCount=0;
$fileContainsCount=0;
$finish=time()+5;
$zipList=array();


foreach($fileList as $file){
    $fileCount+=1;
    $fileName="searchableFiles\\".$file;
    $fileContents= file_get_contents($fileName);
    preg_match_all($regex,$fileContents,$matches,PREG_SET_ORDER);
    #every match should contain the start/end time index for the desired search pattern
    if($matches!= null ){
        echo("<tr>\n");
        array_push($zipList,$fileName);
        $fileContainsCount++;}
    foreach($matches as $match){
            $lineCount++;
            $inline=$match[1];
            $outline=$match[2];
            echo("<td>...</td>".prettyPrint($fileName,$inline,$outline,10))."<td>...</td>\n";
            #prettyPrint will produce a concordance from inline to outline
            echo("<td> In:".$file."</td>\n");
            echo("</tr>\n");
        }
    if(time()>=$finish){
        echo("</table></div>\n");
        echo("Ran out of time...");
        break;
    }
}

echo("</table></div>\n");

### This portion is to produce a download of all of the search results ###
$zipname = 'searchResults.zip';
$zip = new ZipArchive;
$zip->open($zipname, ZipArchive::CREATE);
foreach ($zipList as $file) {
  $zip->addFile($file);
}
$zip->close();

echo("<p>I'm finished! I read through ".$fileCount." files and found ".$lineCount."  occurrences of your search pattern in ".$fileContainsCount." texts.</p>");


echo("<p> You can download the relevant files <a href='searchResults.zip' download='searchResults'> here </a> </p>");
?>


<!-- In order to display different results, we use a bit of JavaScript to modify the styles of the HTML appearing in the results table so just the desired tier appears -->
<script>
        const btn = document.querySelector('#btn');      
        const radioButtons = document.querySelectorAll('input[name="size"]');
        var visibility = "none"
        btn.addEventListener("click", () => {
            for (const radioButton of radioButtons) {
                if (radioButton.checked) {
                      visibility= "inline";
                }
                else {  visibility = "none";
                      selected = tier;}
                tier = radioButton.value;
                var ele = document.getElementsByClassName(tier);
                for (var i = 0; i < ele.length; i++ ) {
                    ele[i].style.display = visibility;
                    }
                }
            output.innerText = selected ? `You selected ${selected}` : `You haven't selected any style`;
        });
    </script>

