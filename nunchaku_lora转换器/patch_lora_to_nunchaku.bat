@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

REM =============================
REM ✅ RELATIVE PATH to python.exe (adjust only if folder structure changes)
set "python_exe=E:\ComfyUI\python\python.exe"
REM =============================

REM ✅ Check if python exists
if not exist "%python_exe%" (
    echo [ERROR] Could not find python.exe at: %python_exe%
    pause
    exit /b
)

REM ✅ Get dragged .safetensors file
set "input_lora=%~1"

if "%input_lora%"=="" (
    echo ⚠️  No LoRA file provided.
    echo 👉 Drag a .safetensors file onto this .bat file.
    pause
    exit /b
)

if not exist "%input_lora%" (
    echo [ERROR] File not found: %input_lora%
    pause
    exit /b
)

REM ✅ Extract name and build output path
for %%F in ("%input_lora%") do (
    set "filename=%%~nF"
    set "folder=%%~dpF"
)

set "output_lora=%folder%!filename!_nunchaku.safetensors"

REM ✅ Save the simulated input to a temp file with UTF-8 encoding
echo %input_lora% > temp_input.txt
echo %output_lora% >> temp_input.txt

REM ✅ Run the patcher script, piping the fake input
set "patch_script=%~dp0patch_comfyui_nunchaku_lora.py"

if not exist "%patch_script%" (
    echo [ERROR] patch_comfyui_nunchaku_lora.py not found in this folder.
    pause
    exit /b
)

echo.
echo 🔧 Patching: %input_lora%
echo 💾 Output:   %output_lora%
echo 🐍 Python:   %python_exe%
echo.

REM ✅ Use Python with UTF-8 encoding and proper environment
set PYTHONIOENCODING=utf-8
"%python_exe%" "%patch_script%" < temp_input.txt

REM ✅ Clean up temp file
del temp_input.txt

echo.
echo ✅ Done!
pause
