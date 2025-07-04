@echo off
setlocal

set "SOURCE=E:\repositories\php\TemplateViewer\src"
set "DEST=C:\xampp\htdocs\vendor\danieltm\template_viewer\src"

echo Copiando arquivos de:
echo %SOURCE%
echo para:
echo %DEST%

xcopy "%SOURCE%\*" "%DEST%\" /E /H /Y /C /Q

echo.
echo Concluido!
pause
