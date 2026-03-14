@echo off
echo ==========================================
echo SAMAWA RUN - PLAYWRIGHT TEST SUITE
echo ==========================================
echo.

REM Test 1: Homepage
echo [TEST 1] Testing Homepage...
playwright-cli open http://localhost:8000
playwright-cli snapshot --filename=01-homepage.yaml
playwright-cli screenshot --filename=01-homepage.png

REM Test 2: Events
echo [TEST 2] Testing Events Page...
playwright-cli goto http://localhost:8000/events
playwright-cli snapshot --filename=02-events.yaml
playwright-cli screenshot --filename=02-events.png

REM Test 3: Event Detail
echo [TEST 3] Testing Event Detail...
playwright-cli goto http://localhost:8000/events/2
playwright-cli snapshot --filename=03-event-detail.yaml
playwright-cli screenshot --filename=03-event-detail.png

REM Test 4: Registration Form
echo [TEST 4] Testing Registration Form...
playwright-cli goto http://localhost:8000/events/2/register
playwright-cli snapshot --filename=04-registration.yaml
playwright-cli screenshot --filename=04-registration.png

REM Test 5: Admin Login
echo [TEST 5] Testing Admin Login...
playwright-cli goto http://localhost:8000/admin/login
playwright-cli fill "input[name='email']" "azrulrifai6@gmail.com"
playwright-cli fill "input[name='password']" "Samawarun2026"
playwright-cli click "button[type='submit']"
playwright-cli snapshot --filename=05-admin-dashboard.yaml
playwright-cli screenshot --filename=05-admin-dashboard.png

REM Test 6: Participants
echo [TEST 6] Testing Participants Page...
playwright-cli goto http://localhost:8000/admin/participants
playwright-cli snapshot --filename=06-participants.yaml
playwright-cli screenshot --filename=06-participants.png

echo.
echo ==========================================
echo TESTING COMPLETE!
echo Check .playwright-cli/ folder for snapshots
echo Check screenshots in project root
echo ==========================================

playwright-cli close
