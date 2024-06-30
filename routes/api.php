<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\SkillController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\Api\UserAwardController;
use App\Http\Controllers\API\UserSkillController;
use App\Http\Controllers\API\UploadFileController;
use App\Http\Controllers\Api\UserProjectController;
use App\Http\Controllers\API\EmailValidateController;
use App\Http\Controllers\Api\UserEducationController;
use App\Http\Controllers\Api\UserExperienceController;

Route::post('/login', [AuthController::class, 'login'])
    ->name('login');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Profile routes
    Route::resource('profile', ProfileController::class)
        ->name('index', 'profile.index')
        ->name('update', 'profile.update')
        ->name('delete', 'profile.destroy');

    Route::put('/change-password', [ProfileController::class, 'changePassword'])
        ->name('change-password');

    // Email validation routes
    Route::post('/email/validate', [EmailValidateController::class, 'validateEmail'])
        ->name('email.validate');

    Route::post('/email/generate-token', [EmailValidateController::class, 'generateToken'])
        ->name('email.generate-token');

    // Temporary file upload routes
    Route::post('/image-upload', [UploadFileController::class, 'imageUpload'])
    ->name('file.image-upload');

    Route::post('/file-upload', [UploadFileController::class, 'fileUpload'])
        ->name('file.file-upload');

    // Users routes
    Route::resource('users', UserController::class)
        ->name('index', 'users.index')
        ->name('show', 'users.show');

    // Skill routes
    Route::resource('skills', SkillController::class)
        ->name('index', 'skills.index');

    // User skills routes
    Route::get('/user-skills', UserSkillController::class)
        ->name('user.skills');

    // User experience routes
    Route::resource('user-experiences', UserExperienceController::class)
        ->name('index', 'user-experiences.index')
        ->name('store', 'user-experiences.store')
        ->name('delete', 'user-experiences.destroy');

    // User education routes
    Route::resource('user-educations', UserEducationController::class)
        ->name('index', 'user-educations.index')
        ->name('store', 'user-educations.store')
        ->name('delete', 'user-educations.destroy');

    // User project routes
    Route::resource('user-projects', UserProjectController::class)
        ->name('index', 'user-projects.index')
        ->name('store', 'user-projects.store')
        ->name('delete', 'user-projects.destroy');

    // User award routes
    Route::resource('user-awards', UserAwardController::class)
        ->name('index', 'user-awards.index')
        ->name('store', 'user-awards.store')
        ->name('delete', 'user-awards.destroy');

    // Job routes
    Route::resource('jobs', JobController::class)
        ->name('index', 'jobs.index')
        ->name('store', 'jobs.store')
        ->name('show', 'jobs.show')
        ->name('update', 'jobs.update')
        ->name('delete', 'jobs.destroy');
});
