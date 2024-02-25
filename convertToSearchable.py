__author__ = 'hicks'
import sys
import os
sys.path.append('exmaralda-converter\\exmaralda_converter')
import exmaralda



inputDirectory="input"
outputDirectory="searchableFiles"


def convert(fileIn):  # This function will output a string with markup formatting which is easier to search by regular expression
    ###Load in the exmaralda Transcript ###
    transcript = exmaralda.ExmaraldaTranscript.load(fileIn)
    
    #Generate a list of tiers which appear in this Exmaralda Transcript
    tierDict={}
    for tid in transcript.tiers:
        tier = transcript.tiers[tid]
        if tier.category=="lgPOS": tier.category="lg-specific POS"
        tierDict[tier.category]=tier
    output=""
    tierDict = dict(sorted(tierDict.items()))

    #We will now write the output ordered by timeindex (as opposed to ordered first by tier).
    for index, timePoint in enumerate(transcript.timeline.list):
        line=f"<timePoint index={index}>"
        time_id=timePoint.time_id
        for tier in tierDict.keys():
            if time_id in tierDict[tier].event_dict:
                event=tierDict[tier].event_dict[time_id]
                line+=f"<event class={tier}>{event.content.strip().replace('NA','')}</event>"
        line+="</timePoint>\n"
        output+=line
    return(output)


fileList=os.listdir(inputDirectory)

for file in fileList:
    searchableText=convert(os.path.join(inputDirectory,file))
    searchableText=searchableText.replace("=tok","=tok*")
    fileOut=os.path.join(outputDirectory,file[:-4]+".out")
    with open(fileOut,"w", encoding="utf-8") as outputfile:
        outputfile.write(searchableText)
quit()
