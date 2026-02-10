@echo off
REM Kill stuck git processes
taskkill /F /IM vim.exe 2>nul
taskkill /F /IM git.exe 2>nul
timeout /t 2

REM Navigate and push
cd /d C:\xampp\htdocs\eya
git merge --abort 2>nul
git remote remove medtime 2>nul
git remote add medtime https://github.com/eyaarg/MedTime.git
git push medtime main --force

echo.
echo Push complete! Check: https://github.com/eyaarg/MedTime/commits/main
echo.
pause
