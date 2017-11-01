@echo off 

set VSVARS="%VS110COMNTOOLS%vsvars32.bat"
set VC_VER=110

call %VSVARS%
devenv mm_lizi.sln /build debug
devenv mm_lizi.sln /build release
pause