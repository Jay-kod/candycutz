@echo off
setlocal

cd /d "%~dp0"

git rev-parse --is-inside-work-tree >nul 2>&1
if errorlevel 1 (
    echo This folder is not a Git repository.
    exit /b 1
)

for /f "delims=" %%B in ('git branch --show-current') do set "BRANCH=%%B"
if not defined BRANCH (
    echo Detached HEAD detected. Checkout a branch before uploading.
    exit /b 1
)

git config --get "branch.%BRANCH%.remote" >nul 2>&1
if errorlevel 1 (
    echo Branch "%BRANCH%" has no configured remote.
    echo Set an upstream with: git push --set-upstream origin %BRANCH%
    exit /b 1
)

git add -A

git diff --cached --quiet
if not errorlevel 1 (
    echo No changes to upload.
    exit /b 0
)

git diff --cached --check
if errorlevel 1 (
    echo WARNING: staged patch contains whitespace errors. Continuing with upload.
)

set "MESSAGE=%~1"
if not defined MESSAGE set "MESSAGE=chore: upload latest changes"

git commit -m "%MESSAGE%"
if errorlevel 1 exit /b 1

git push
if errorlevel 1 exit /b 1

echo Uploaded to GitHub on branch "%BRANCH%".
endlocal
