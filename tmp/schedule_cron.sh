#!/bin/bash
rm -fr schedule
rm schedule-full.json
mkdir schedule
mkdir schedule/day
mkdir schedule/event
mkdir schedule/fahrplan
python3 schedule.py

# ... and then copy the schedule folder to the webroot ...
rm -fr ../public/schedule
mv schedule ../public