__author__ = 'hicks'
import sys
import os
import re

sys.path.append('exmaralda-converter/exmaralda_converter')
import exmaralda



inputDirectory="C:/Users/jhicks2/Documents/Nina/SwikoAlex/SWIKOweb/SWIKO-Exchange/written/5_Exmaralda/SWIKO22"
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
        line=""
        time_id=timePoint.time_id
        for tier in tierDict.keys():
            if time_id in tierDict[tier].event_dict:
                event=tierDict[tier].event_dict[time_id]
                line+=f"<{tier}>{event.content.strip().replace('NA','')}<>"
        line+="<~>"
        output+=line
    return(output)



import mysql.connector

mydb = mysql.connector.connect(
  host="localhost",
  user="db_user",
  password="YES",
  database="swiko"
)




exchangeDirectory="C:/Users/jhicks2/Documents/Nina/SwikoAlex/SWIKOweb/SWIKO-Exchange/"


mycursor = mydb.cursor()
cursor2 = mydb.cursor()
mycursor.execute("SELECT id, exmaralda FROM analysis")


myresult = mycursor.fetchall()

badFileList=[]
for x in myresult:
    if (x[1])!=None:    
        thisFilename  = exchangeDirectory+ re.findall("written.*exb", x[1])[0]
        #print(thisFilename)
        try: 
            searchableText=convert(thisFilename)
    #       searchableText=searchableText.replace("=tok","=tok*")
            searchableText=searchableText.replace("'","\\'")
            #searchableText=searchableText.replace("@","\\@")
            #print(searchableText)
            sql = f"UPDATE analysis set TokenSearchable = '{searchableText}' where id = {x[0]} "
            cursor2.execute(sql)
        except:
            print(f"error on {x[0]}: {thisFilename}... Sorry!")


quit()

fileList=os.listdir(inputDirectory)
badFileList=[]
for file in fileList:
    #print(file)
    try:
        searchableText=convert(os.path.join(inputDirectory,file))
        searchableText=searchableText.replace("=tok","=tok*")
        fileOut=os.path.join(outputDirectory,file[:-4]+".out")
        with open(fileOut,"w", encoding="utf-8") as outputfile:
            outputfile.write(searchableText)
    except:
        print(f"Could not parse {file} ")
        badFileList.append(file)
for file in badFileList: print(file)
quit()
