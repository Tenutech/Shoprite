<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['verify' => true]);

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth', 'verified', 'role:1,2', 'user.activity'])->group(function () {

    //Home
    Route::get('/home', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.home');

    //Vacancy Approvals
    Route::get('/approvals', [App\Http\Controllers\ApprovalController::class, 'index'])->name('approvals.index');

    Route::put('/vacancy-approve', [App\Http\Controllers\ApprovalController::class, 'approve'])->name('vacancy.approve');

    Route::put('/vacancy-amend', [App\Http\Controllers\ApprovalController::class, 'amend'])->name('vacancy.amend');

    Route::put('/vacancy-decline', [App\Http\Controllers\ApprovalController::class, 'decline'])->name('vacancy.decline');

    //Applicant Approvals
    Route::get('/applicant/approvals', [App\Http\Controllers\ApplicantApprovalController::class, 'index'])->name('applicant-approvals.index');

    Route::put('/applicant-approve', [App\Http\Controllers\ApplicantApprovalController::class, 'approve'])->name('applicant.approve');

    Route::put('/applicant-amend', [App\Http\Controllers\ApplicantApprovalController::class, 'amend'])->name('applicant.amend');

    Route::put('/applicant-decline', [App\Http\Controllers\ApplicantApprovalController::class, 'decline'])->name('applicant.decline');

    //Users

    Route::get('/users', [App\Http\Controllers\UsersController::class, 'index'])->name('users.index');

    Route::get('user', [App\Http\Controllers\UsersController::class, 'user'])->name('users.user');

    Route::post('/users/add', [App\Http\Controllers\UsersController::class, 'store'])->name('users.store');

    Route::post('/users/update', [App\Http\Controllers\UsersController::class, 'update'])->name('users.update');

    Route::get('/users/details/{id}', [App\Http\Controllers\UsersController::class, 'details'])->name('users.details');

    Route::delete('/users/destroy/{id}', [App\Http\Controllers\UsersController::class, 'destroy'])->name('users.destroy');

    Route::post('/users/destroy-multiple', [App\Http\Controllers\UsersController::class, 'destroyMultiple'])->name('users.destroyMultiple');

    //Literacy

    Route::get('/literacy', [App\Http\Controllers\LiteracyController::class, 'index'])->name('literacy.index');

    Route::post('/literacy/add', [App\Http\Controllers\LiteracyController::class, 'store'])->name('literacy.store');

    Route::post('/literacy/update', [App\Http\Controllers\LiteracyController::class, 'update'])->name('literacy.update');

    Route::get('/literacy/details/{id}', [App\Http\Controllers\LiteracyController::class, 'details'])->name('literacy.details');

    Route::delete('/literacy/destroy/{id}', [App\Http\Controllers\LiteracyController::class, 'destroy'])->name('literacy.destroy');

    //Numeracy

    Route::get('/numeracy', [App\Http\Controllers\NumeracyController::class, 'index'])->name('numeracy.index');

    Route::post('/numeracy/add', [App\Http\Controllers\NumeracyController::class, 'store'])->name('numeracy.store');

    Route::post('/numeracy/update', [App\Http\Controllers\NumeracyController::class, 'update'])->name('numeracy.update');

    Route::get('/numeracy/details/{id}', [App\Http\Controllers\NumeracyController::class, 'details'])->name('numeracy.details');

    Route::delete('/numeracy/destroy/{id}', [App\Http\Controllers\NumeracyController::class, 'destroy'])->name('numeracy.destroy');
});

/*
|--------------------------------------------------------------------------
| Manager Routes
|--------------------------------------------------------------------------
*/

Route::prefix('manager')->middleware(['auth', 'verified', 'role:1,2,3', 'user.activity'])->group(function () {
    //Home
    Route::get('/home', [App\Http\Controllers\ManagerController::class, 'index'])->name('manager.home');

    //vacancies
    Route::get('/vacancies', [App\Http\Controllers\ManagerController::class, 'vacancies'])->name('manager.vacancies');

    //Vacancy
    Route::get('/vacancy', [App\Http\Controllers\VacancyController::class, 'index'])->name('vacancy.index');

    Route::post('/vacancy/store', [App\Http\Controllers\VacancyController::class, 'store'])->name('vacancy.store');

    Route::post('/vacancy/update', [App\Http\Controllers\VacancyController::class, 'update'])->name('vacancy.update');

    Route::delete('/vacancy/destroy/{id}', [App\Http\Controllers\VacancyController::class, 'destroy'])->name('vacancy.destroy');

    Route::post('/vacancy/destroy-multiple', [App\Http\Controllers\VacancyController::class, 'destroyMultiple'])->name('vacancy.destroyMultiple');

    Route::post('/vacancy/fill', [App\Http\Controllers\VacancyController::class, 'vacancyFill'])->name('vacancy.fill');

    //User Profile
    Route::get('/user-profile', [App\Http\Controllers\UserProfileController::class, 'index'])->name('user-profile.index');

    //Applicants
    Route::get('/applicants', [App\Http\Controllers\ApplicantsController::class, 'index'])->name('applicants.index');

    Route::get('/applicants-data', [App\Http\Controllers\ApplicantsController::class, 'applicants'])->name('applicants.data');

    //Shortlist
    Route::get('/shortlist', [App\Http\Controllers\ShortlistController::class, 'index'])->name('shortlist.index');

    Route::get('/shortlist-data', [App\Http\Controllers\ShortlistController::class, 'applicants']);

    Route::get('/shortlist-applicants', [App\Http\Controllers\ShortlistController::class, 'shortlistApplicants']);

    Route::post('/shortlist-update', [App\Http\Controllers\ShortlistController::class, 'shortlistUpdate']);

    //Interviews
    Route::post('/interview-store', [App\Http\Controllers\InterviewController::class, 'store'])->name('interview.store');

    Route::post('/interview-score', [App\Http\Controllers\InterviewController::class, 'score'])->name('interview.score');

    Route::post('/contract-send', [App\Http\Controllers\InterviewController::class, 'contract'])->name('contract.send');

    //Applicant Profile
    Route::get('/applicant-profile', [App\Http\Controllers\ApplicantProfileController::class, 'index'])->name('applicant-profile.index');

    Route::get('/applicant-messages/{id}', [App\Http\Controllers\ApplicantProfileController::class, 'messages'])->name('applicant-profile.messages');

    Route::get('/applicant-profile/files/{filename}', [App\Http\Controllers\ApplicantProfileController::class, 'checkFile'])->name('check.file');

    //Chats

    Route::get('/chats', [App\Http\Controllers\ChatsController::class, 'index'])->name('chats.index');

    Route::post('/chats/add', [App\Http\Controllers\ChatsController::class, 'store'])->name('chats.store');

    Route::post('/chats/update', [App\Http\Controllers\ChatsController::class, 'update'])->name('chats.update');

    Route::get('/chats/details/{id}', [App\Http\Controllers\ChatsController::class, 'details'])->name('chats.details');

    Route::delete('/chats/destroy/{id}', [App\Http\Controllers\ChatsController::class, 'destroy'])->name('chats.destroy');

    Route::post('/chats/destroy-multiple', [App\Http\Controllers\ChatsController::class, 'destroyMultiple'])->name('chats.destroyMultiple');

});

/*
|--------------------------------------------------------------------------
| Default Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'user.activity'])->group(function () {
    //Home
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'home'])->name('home');

    Route::get('/home/vacancies', [App\Http\Controllers\HomeController::class, 'vacancies'])->name('home.vacancies');

    //Language Translation
    Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

    //Application
    Route::get('/application', [App\Http\Controllers\ApplicationController::class, 'index'])->name('application.index');

    Route::post('/application/store', [App\Http\Controllers\ApplicationController::class, 'store'])->name('application.store');

    Route::post('/application/update', [App\Http\Controllers\ApplicationController::class, 'update'])->name('application.update');

    //Job Overview
    Route::get('/job-overview/{id}', [App\Http\Controllers\JobOverviewController::class, 'index'])->name('job-overview.index');

    Route::get('/files/view/{id}', [App\Http\Controllers\JobOverviewController::class, 'viewFile'])->name('file.view');

    Route::get('/files/download/{id}', [App\Http\Controllers\JobOverviewController::class, 'downloadFile'])->name('file.download');

    Route::post('/files/add', [App\Http\Controllers\JobOverviewController::class, 'store'])->name('file.store');

    Route::delete('/files/delete/{id}', [App\Http\Controllers\JobOverviewController::class, 'destroy'])->name('file.destroy');    

    //Messages
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');

    Route::get('/user-messages', [App\Http\Controllers\MessageController::class, 'messages']);

    Route::post('/messages/store', [App\Http\Controllers\MessageController::class, 'store'])->name('message.store');

    Route::post('/messages-read', [App\Http\Controllers\MessageController::class, 'read'])->name('messages.read');

    Route::delete('/message-delete/{id}', [App\Http\Controllers\MessageController::class, 'destroy'])->name('message.destroy');

    //Vacancies
    Route::get('/vacancies', [App\Http\Controllers\VacanciesController::class, 'index'])->name('vacancies.index');

    Route::get('/vacancy/jobs', [App\Http\Controllers\VacanciesController::class, 'vacancies']);

    //My Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');

    Route::get('/documents/view/{id}', [App\Http\Controllers\ProfileController::class, 'viewFile'])->name('document.view');

    Route::get('/documents/download/{id}', [App\Http\Controllers\ProfileController::class, 'downloadFile'])->name('document.download');

    Route::post('/documents/add', [App\Http\Controllers\ProfileController::class, 'store'])->name('document.store');

    Route::delete('/documents/delete/{id}', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('document.destroy');

    Route::post('/delete-profile', [App\Http\Controllers\ProfileController::class, 'deleteProfile'])->name('profile.delete');

    //My Profile Settings
    Route::get('/profile-settings', [App\Http\Controllers\ProfileSettingsController::class, 'index'])->name('profile-settings.index');
    
    Route::post('/update-profile', [App\Http\Controllers\ProfileSettingsController::class, 'update'])->name('profile-settings.update');

    Route::post('/update-password', [App\Http\Controllers\ProfileSettingsController::class, 'updatePassword'])->name('profile-settings.updatePassword');

    Route::post('/update-notifications', [App\Http\Controllers\ProfileSettingsController::class, 'notificationSettings'])->name('profile-settings.notifications');

    //Save
    Route::put('/applicant-save/{id}', [App\Http\Controllers\SaveController::class, 'applicantSave'])->name('applicant.save');

    Route::put('/vacancy-save/{id}', [App\Http\Controllers\SaveController::class, 'vacancySave'])->name('vacancy.save');

    //Applications
    Route::put('/apply/{id}', [App\Http\Controllers\ApplyController::class, 'vacancyApply'])->name('vacancy.apply');

    Route::put('/apply-approve', [App\Http\Controllers\ApplyController::class, 'approve'])->name('application.approve');

    Route::put('/apply-decline', [App\Http\Controllers\ApplyController::class, 'decline'])->name('application.decline');

    //Interviews
    Route::put('/interview-confirm', [App\Http\Controllers\InterviewController::class, 'confirm'])->name('interview.approve');

    Route::put('/interview-decline', [App\Http\Controllers\InterviewController::class, 'decline'])->name('interview.decline');

    //Notifications
    Route::put('/notification-read', [App\Http\Controllers\NotificationController::class, 'notificationRead'])->name('notification.read');

    Route::put('/notification-remove', [App\Http\Controllers\NotificationController::class, 'notificationRemove'])->name('notification.remove');
});

/*
|--------------------------------------------------------------------------
| Welcome Routes
|--------------------------------------------------------------------------
*/

//Welcome
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

//Subscribe
Route::post('/subscribe', [App\Http\Controllers\HomeController::class, 'subscribe']);

//Privacy Policy
Route::get('/privacy-policy', [App\Http\Controllers\HomeController::class, 'policy'])->name('policy');

//Terms of Service
Route::get('/terms-of-service', [App\Http\Controllers\HomeController::class, 'terms'])->name('terms');

//Security
Route::get('/security', [App\Http\Controllers\HomeController::class, 'security'])->name('security');

Route::post('/shoops', [App\Http\Controllers\ShoopsController::class, 'shoops'])->name('shoops');

//Pages
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');