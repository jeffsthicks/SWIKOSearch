<?php
#This function will parse a search term and return the corresponding regex string
function termParser($term){
    $parsedTerm="";
    $tierArray=explode("&",$term);
    sort($tierArray);
    foreach($tierArray as $tier){
    error_log($tier);
    if(str_contains($tier,"\$cpos")){
        preg_match("~cpos=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm."<commonPOS>".$matches[1]."<[^~]*";
    }   
    elseif(str_contains($tier,"\$ctok")){
        preg_match("~ctok=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm."<ctok>".$matches[1]."<[^~]*";
    }
    elseif(str_contains($tier,"\$g")){
        preg_match("~g=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm."<G[^>]*>".$matches[1]."<[^~]*";
    }
    elseif(str_contains($tier,"\$lgPOS")){
        preg_match("~lgPOS=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm. "<lg-specific POS>".$matches[1]."<[^~]*";
    }
    elseif(str_contains($tier,"\$lem")){
        preg_match("~lem=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm. "<.?lemma>".$matches[1]."<[^~]*";
    }
    elseif(str_contains($tier,"\$o")){
        preg_match("~o=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm."<O[^>]*".$matches[1]."<[^~]*";
    }
    elseif(str_contains($tier,"\$tag")){
        preg_match("~tag=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm."<tag>".$matches[1]."<[^~]*";
    }    
    elseif(str_contains($tier,"\$tok")){
        preg_match("~tok=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm. "<tok>".$matches[1]."<[^~]*";
    }
    elseif(str_contains($tier,"\$th1")){
        preg_match("~th1=([A-Za-z:\.\*_]*)~",$tier,$matches);
        $parsedTerm = $parsedTerm. "<TH1>".$matches[1]."<[^~]*";
    }
    }
    $parsedTerm=str_replace(".", "[^~]", $parsedTerm);   
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
function detokenize($text,$tier){
    $output="";
    preg_match_all("/<$tier>([^<]*)/",$text,$matches);
    foreach($matches[1] as $match){
        $output=$output.$match." ";
    }
    $output=preg_replace("/ ([,.')])/","$1",$output);
    #trim(preg_replace('/[:space:]+/', ' ', $output));
    return $output;
}
function prettierPrint($text,$regex,$exm=TRUE,$tier='ctok',$caseTerm=''){
    $output="";
    if(($caseTerm=='')){
        $caseFlag="i";
    }  
    else{
        $caseFlag="";
    }
    $regex.=$caseFlag;
#    error_log($regex);
    $text=str_replace("\\","",$text);
    #echo($regex);
    preg_match_all($regex,$text,$matches, PREG_OFFSET_CAPTURE);
    #echo(count($matches[0]));
    foreach($matches[0] as $match){
        $matchText= $match[0];
        $matchLength = strlen($matchText);
        $offset = $match[1];
        #echo($matchText."<p>".$matchLength."<p>".$offset);
        if($exm){
        $centerString=trim(detokenize($matchText,$tier));
        $centerString=preg_replace('/[\s]+/', ' ', $centerString);
        $leftString=mb_substr(detokenize(substr($text,0,$offset),$tier),-30);
        $leftString=preg_replace('/[\s]+/', ' ', $leftString);
        $leftString=mb_str_pad(trim($leftString), 30, " " , STR_PAD_LEFT);
        $rightString=mb_substr(detokenize(substr($text,$offset+$matchLength),$tier),0,40-mb_strlen($centerString));
        }
        else{
        $centerString=trim(($matchText));
        $centerString=preg_replace('/[\s]+/', ' ', $centerString);
        $leftString=mb_substr((substr($text,0,$offset)),-30);
        $leftString=preg_replace('/[\s]+/', ' ', $leftString);
        $leftString=mb_str_pad(trim($leftString), 30, " " , STR_PAD_LEFT);
        $rightString=mb_substr(trim(preg_replace('/[\s]+/', ' ',substr($text,$offset+$matchLength))),0,40-mb_strlen($centerString));
        }
        $output=$output.('<span>'.$leftString.' </span>'.'<span style="font-weight: bold; color: #3b8695;">'.$centerString.' </span><span>'.$rightString.'</span><br>');   
    }
    if($output==""){$output='<span></span>';}
    echo $output;
}
    

function generateRegex($searchQuery='Default', $capChoice = '', $defaultTier = '$th1=', $sql=false){
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
if($sql==true){$regex="a.TokenSearchable REGEXP $capChoice '";}
else{$regex="/";}
foreach ($searchTerms as $term){
#    echo($term);
    $regex=$regex."[^~]*".termParser($term)."~";
}
if($sql==true){$regex=$regex."'";}
else{$regex=$regex."/";}
#clean regex:
while(str_contains($regex,"[^~]*[^~]*"))
    {$regex=str_replace("[^~]*[^~]*", "[^~]*", $regex);}
return $regex;
}
?>
