@echo off
title Laravel Auto Run

cd /d %~dp0

echo Running migrate:fresh...
php artisan migrate:fresh

echo Creating admin account...
php artisan tinker --execute="App\Models\User::create(['name'=>'admin','email'=>'admin@mail.com','password'=>bcrypt('123456'),'role'=>'admin']);"

echo Clearing cache...
php artisan optimize:clear

echo Starting server...
php artisan serve

pause
