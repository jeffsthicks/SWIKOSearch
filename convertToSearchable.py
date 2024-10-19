__author__ = 'hicks'
import sys
import os
import re

sys.path.append('exmaralda-converter/exmaralda_converter')
import exmaralda





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


replacementRules=[[" .","."],[" ,",","],["//",""],[" '","'"]]
def readTier(fileIn,category):
    transcript = exmaralda.ExmaraldaTranscript.load(fileIn)

    for tid in transcript.tiers:
        if category == transcript.tiers[tid].category:tier=transcript.tiers[tid]
    tierContent=""
    try:
        for index, timePoint in enumerate(transcript.timeline.list):
            time_id=timePoint.time_id
            if time_id in tier.event_dict:
                event=tier.event_dict[time_id]
                tierContent+=event.content.strip().replace('NA','')+" "
        
        for rule in replacementRules:
            tierContent=tierContent.replace(rule[0],rule[1])
        tierContent=tierContent.replace(" .",".")
        tierContent=tierContent.replace(" .",".")
        tierContent=tierContent.replace("//","")
    except: ()#print(f"Couldn't read {category} in {fileIn}")
    return tierContent 



import mysql.connector

mydb = mysql.connector.connect(
  host="localhost",
  user="db_user",
  password="YES",
  database="swiko"
)




exchangeDirectory="C:/Users/jhicks2/Documents/SWIKO/SWIKOweb/SWIKO-Exchange/"


mycursor = mydb.cursor()
cursor2 = mydb.cursor()
mycursor.execute("SELECT id, exmaralda FROM analysis")


myresult = mycursor.fetchall()

badFileList=[]
for x in myresult:
    if (x[1])!=None:
        try:
            thisFilename  = exchangeDirectory+ re.findall("written.*exb", x[1])[0]
        except:
            print(f"Ran into a problem: {x[0]}!")
        try:
            ctokText=readTier(thisFilename,"ctok")
            th1Text=readTier(thisFilename,"TH1")
            lemmaText=readTier(thisFilename,"lemma")
            searchableText=convert(thisFilename)
            sql = "UPDATE analysis set TokenSearchable = %s ,ctokText= %s , th1Text = %s ,lemmaText=%s where id = %s"
            cursor2.execute(sql,(searchableText,ctokText,th1Text,lemmaText,x[0]))
            #sql = f"UPDATE analysis set ctok = '{ctokText}' where id = {x[0]}"
            #cursor2.execute(sql)
        except: 
           print(f"error on {x[0]}: {thisFilename}... Sorry!")
        


quit()
