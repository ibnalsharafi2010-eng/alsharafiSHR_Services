@echo off
cd /d %~dp0
git init
git add .
git commit -m "auto upload"
git branch -M main
git remote remove origin 2>nul
git remote add origin https://github.com/ibnalsharafi2010-eng/alsharafiSHR_Services.git
git push -u origin main
pause
