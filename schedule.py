# MIT (c) 2017 Renze Nikolai

from dateutil.parser import parse
import requests
import json
from datetime import datetime

print("Requesting data...")
data = requests.get("https://program.sha2017.org/schedule.json")

print("Decoding...")
program = data.json()

output_schedule = {}
version = program['schedule']['version']

print("Schedule version: '"+version+"'")
output_schedule['version'] = version
conference = program['schedule']['conference']
title = conference['title']
output_schedule['title'] = title
print("Conference title: "+title)
days = program['schedule']['conference']['days']

output = []
output_schedule['days'] = {}

for daynr in range(0,len(days)):
    print("Parsing day "+str(daynr)+"...")
    day = days[daynr]
    rooms = day['rooms']
    output_schedule['days'][day['index']] = day['date']
    output_day = []
    output_day_simple = []
    output_day_fahrplan = {}
    output_day_fahrplan['version'] = version
    output_day_fahrplan['date'] = day['date']
    output_day_fahrplan['rooms'] = {}
    for room_name in rooms:
      room = rooms[room_name]
      output_day_fahrplan['rooms'][room_name] = []
      for event in room:
        output_event = {}
        output_event['timestamp'] = round(parse(event['date']).timestamp())
        output_event['duration'] = event['duration']
        output_event['room'] = event['room']
        output_event['title'] = event['title']
        output_event['subtitle'] = event['subtitle']
        output_event['type'] = event['type']
        output_event['language'] = event['language']
        output_event['abstract'] = event['abstract']
        output_event['description'] = event['description']
        output_event['recording_license'] = event['recording_license']
        output_event['do_not_record'] = event['do_not_record']
        output_event['guid'] = event['guid']
        output_event['when'] = datetime.fromtimestamp(int(output_event['timestamp'])).strftime('%Y-%m-%d %H:%M')
        sev = {}
        sev['guid'] = output_event['guid']
        sev['title'] = output_event['title']
        output_day_simple.append(sev)
        fp_ev = {}
        fp_ev['start'] = datetime.fromtimestamp(int(output_event['timestamp'])).strftime('%H:%M')
        fp_ev['duration'] = event['duration']
        fp_ev['title'] = event['title']
        output_day_fahrplan['rooms'][room_name].append(fp_ev)
        output_event['persons'] = []
        for person in event['persons']:
            output_event['persons'].append(person['public_name'])
        output_day.append(output_event)
        with open('schedule/event/'+str(output_event['guid'])+'.json', 'w') as file:
            json.dump(output_event, file)
    output.append(output_day)
    with open('schedule/day/'+str(daynr)+'.json', 'w') as file:
        json.dump(output_day_simple, file)
    with open('schedule/fahrplan/'+str(daynr)+'.json', 'w') as file:
        json.dump(output_day_fahrplan, file)

with open('schedule/schedule.json', 'w') as file:
    json.dump(output_schedule, file)

with open('schedule-full.json', 'w') as file:
    json.dump(output, file)

