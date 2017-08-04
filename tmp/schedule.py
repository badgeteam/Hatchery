from dateutil.parser import parse
import requests
import json
from datetime import datetime
from datetime import timedelta

start_time = datetime.now()
end_time = start_time + timedelta(hours=1,minutes=30)
#print("Now: "+str(start_time))
#print("End: "+str(end_time))

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
output_upcoming = []

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
        #fp_ev['guid'] = event['guid']
        event_time = datetime.fromtimestamp(int(output_event['timestamp']))
        #printf("Event: "+str(event_time))
        if start_time <= event_time and event_time <= end_time:
          upcoming_ev = {}
          upcoming_ev['start'] = fp_ev['start']
          upcoming_ev['duration'] = event['duration']
          upcoming_ev['room'] = event['room']
          upcoming_ev['title'] = event['title']
          #upcoming_ev['subtitle'] = event['subtitle']
          upcoming_ev['type'] = event['type']
          #upcoming_ev['language'] = event['language']
          #upcoming_ev['abstract'] = event['abstract']
          #upcoming_ev['description'] = event['description']
          #upcoming_ev['recording_license'] = event['recording_license']
          #upcoming_ev['do_not_record'] = event['do_not_record']
          upcoming_ev['guid'] = event['guid']
          output_upcoming.append(upcoming_ev)
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

with open('schedule/upcoming.json', 'w') as file:
	json.dump(output_upcoming, file)

