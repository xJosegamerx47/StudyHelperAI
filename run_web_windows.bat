@echo off
cd /d "%~dp0"
echo Starting StudyHelperAI web app...
echo Open http://localhost:8000 in your browser.
php -S localhost:8000 -t web
pause
