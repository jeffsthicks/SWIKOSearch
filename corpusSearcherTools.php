<?php
#This function will parse a search term and return the corresponding regex string
function termParser($term){
    $parsedTerm="";
    $tierArray=explode("&",$term);
    sort($tierArray);
    foreach($tierArray as $tier){
    if(str_contains($tier,"\$g")){
            preg_match("~g=([A-Za-z:\.\*_]*)~",$tier,$matches);
            $parsedTerm = $parsedTerm."<event class=G[^>]*>".$matches[1]."</event>.*";
        }
    elseif(str_contains($tier,"\$o")){
        preg_match("~o=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm."<event class=O[^>]*".$matches[1]."</event>.*";
    }    
    elseif(str_contains($tier,"\$cpos")){
        preg_match("~cpos=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm."<event class=commonPOS>".$matches[1]."</event>.*";
    }   
    elseif(str_contains($tier,"\$ctok")){
        preg_match("~ctok=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm."<event class=ctok>".$matches[1]."</event>.*";
    }
    elseif(str_contains($tier,"\$lg")){
        preg_match("~lg=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm. "<event class=lg-specific POS>".$matches[1]."</event>.*";
    }
    elseif(str_contains($tier,"\$lem")){
        preg_match("~lem=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm. "<event class=.?lemma>".$matches[1]."</event>.*";
    }
    elseif(str_contains($tier,"\$tok")){
        preg_match("~tok=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm. "<event class=tok\*>".$matches[1]."</event>.*";
    }
    elseif(str_contains($tier,"\$th1")){
        preg_match("~th1=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm. "<event class=TH1>".$matches[1]."</event>.*";
    }
    }
    return $parsedTerm;
}

#This function allows the printing of concordances from the file. 
function prettyPrint($fileName,$inline,$outline, $concordanceLength=10){
    $fileLines=file($fileName);
    $start=max(0, $inline-$concordanceLength);
    $end  =min(count($fileLines)-1,$outline+$concordanceLength);
    $output="<td class='pre'>";
    for ($index=$start; $index <= $end; $index++){
        if($index == $inline){
        $output=$output."</td><td class='mid'>";
        }
        $output=$output.str_replace("tok*","tok",$fileLines[$index]);
        if($index+1 == $outline){
            $output=$output."</td><td class='post'>";
            }
    }
    $output=$output."</td>";
    return $output;
}

function generateRegex($searchQuery='Default', $capChoice = 'i', $defaultTier = '$th1='){
    
$searchTerms=array();
$index=0;
$tempTerms=explode(" ",$searchQuery);
$thisTerm="";
$with=TRUE;
foreach($tempTerms as $term){ 
    if($term=="WITH")
    {
        $with=TRUE;
        $thisTerm=$thisTerm."&";
    }
    else{
        if(!str_contains($term,"\$")){
            $term=$defaultTier.$term;
        }
        if($with==FALSE){
            $searchTerms[$index]=$thisTerm;
            $thisTerm=$term;
            $index++;
        }
        else{
            $thisTerm=$thisTerm.$term;
        }
        $with=FALSE;
    }
}

$searchTerms[$index]=$thisTerm;

$regex="~<timePoint index=([0-9]*)>";
foreach ($searchTerms as $term){
#    echo($term);
    $regex=$regex.".*".termParser($term)."[^\*]*";
}
$regex=$regex."<timePoint index=([0-9]*)>~".$capChoice;
return $regex;
}
?>
