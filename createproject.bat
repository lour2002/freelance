@Echo On
set /p project="NewProject: "
git checkout --orphan %project% 
mkdir %project%
copy NUL > .\%project%\init
git reset
git add .\%project%\init
git commit -m "CREATE %project%"
git add .
git rm -f --ignore-unmatch -- README.md
git rm -f --ignore-unmatch -- .gitignore
git rm -f --ignore-unmatch -- createproject.bat
pause