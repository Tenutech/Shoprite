<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

Route::prefix('admin')->middleware(['auth', 'verified', 'role:1,2,3,4,5', 'user.activity'])->group(function () {
    //Home

    Route::get('/home', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.home');

    //Update Data

    Route::get('/updateData', [App\Http\Controllers\AdminController::class, 'updateData'])->name('admin.updateData');

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

    //Managers

    Route::get('/managers', [App\Http\Controllers\ManagersController::class, 'index'])->name('managers.index');

    //Admins

    Route::get('/admins', [App\Http\Controllers\AdminsController::class, 'index'])->name('admins.index');

    //Super Admins

    Route::get('/super-admins', [App\Http\Controllers\SuperAdminsController::class, 'index'])->name('super-admins.index');

    //Email

    Route::get('/email', [App\Http\Controllers\EmailController::class, 'index'])->name('email.index');

    Route::post('/email/add', [App\Http\Controllers\EmailController::class, 'store'])->name('email.store');

    Route::post('/email/update', [App\Http\Controllers\EmailController::class, 'update'])->name('email.update');

    Route::get('/email/details/{id}', [App\Http\Controllers\EmailController::class, 'details'])->name('email.details');

    Route::delete('/email/destroy/{id}', [App\Http\Controllers\EmailController::class, 'destroy'])->name('email.destroy');

    Route::post('/email/destroy-multiple', [App\Http\Controllers\EmailController::class, 'destroyMultiple'])->name('email.destroyMultiple');

    Route::get('email/export', [App\Http\Controllers\EmailController::class, 'export'])->name('email.export');

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

    //Situational Awareness

    Route::get('/situational-awareness', [App\Http\Controllers\SituationalAwarenessController::class, 'index'])->name('situational-awareness.index');

    Route::post('/situational-awareness/add', [App\Http\Controllers\SituationalAwarenessController::class, 'store'])->name('situational-awareness.store');

    Route::post('/situational-awareness/update', [App\Http\Controllers\SituationalAwarenessController::class, 'update'])->name('situational-awareness.update');

    Route::get('/situational-awareness/details/{id}', [App\Http\Controllers\SituationalAwarenessController::class, 'details'])->name('situational-awareness.details');

    Route::delete('/situational-awareness/destroy/{id}', [App\Http\Controllers\SituationalAwarenessController::class, 'destroy'])->name('situational-awareness.destroy');

    //Weighting

    Route::get('/weighting', [App\Http\Controllers\WeightingController::class, 'index'])->name('weighting.index');

    Route::post('/weighting/add', [App\Http\Controllers\WeightingController::class, 'store'])->name('weighting.store');

    Route::post('/weighting/update', [App\Http\Controllers\WeightingController::class, 'update'])->name('weighting.update');

    Route::get('/weighting/details/{id}', [App\Http\Controllers\WeightingController::class, 'details'])->name('weighting.details');

    Route::delete('/weighting/destroy/{id}', [App\Http\Controllers\WeightingController::class, 'destroy'])->name('weighting.destroy');

    Route::post('/weighting/destroy-multiple', [App\Http\Controllers\WeightingController::class, 'destroyMultiple'])->name('weighting.destroyMultiple');

    //Settings

    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');

    Route::post('/settings/update', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

    Route::post('/settings/reminders', [App\Http\Controllers\SettingsController::class, 'reminderSettings'])->name('settings.reminder-settings');

    //Roles

    Route::get('/roles', [App\Http\Controllers\RolesController::class, 'index'])->name('roles.index');

    Route::post('/role/add', [App\Http\Controllers\RolesController::class, 'store'])->name('role.store');

    Route::post('/role/update', [App\Http\Controllers\RolesController::class, 'update'])->name('role.update');

    Route::get('/role/details/{id}', [App\Http\Controllers\RolesController::class, 'details'])->name('role.details');

    Route::delete('/role/destroy/{id}', [App\Http\Controllers\RolesController::class, 'destroy'])->name('role.destroy');

    Route::post('/role/destroy-multiple', [App\Http\Controllers\RolesController::class, 'destroyMultiple'])->name('role.destroyMultiple');

    //Positions

    Route::get('/positions', [App\Http\Controllers\PositionsController::class, 'index'])->name('positions.index');

    Route::post('/position/add', [App\Http\Controllers\PositionsController::class, 'store'])->name('position.store');

    Route::post('/position/update', [App\Http\Controllers\PositionsController::class, 'update'])->name('position.update');

    Route::get('/position/details/{id}', [App\Http\Controllers\PositionsController::class, 'details'])->name('position.details');

    Route::delete('/position/destroy/{id}', [App\Http\Controllers\PositionsController::class, 'destroy'])->name('position.destroy');

    Route::post('/position/destroy-multiple', [App\Http\Controllers\PositionsController::class, 'destroyMultiple'])->name('position.destroyMultiple');

    //Brands

    Route::get('/brands', [App\Http\Controllers\BrandsController::class, 'index'])->name('brands.index');

    Route::post('/brand/add', [App\Http\Controllers\BrandsController::class, 'store'])->name('brand.store');

    Route::post('/brand/update', [App\Http\Controllers\BrandsController::class, 'update'])->name('brand.update');

    Route::get('/brand/details/{id}', [App\Http\Controllers\BrandsController::class, 'details'])->name('brand.details');

    Route::delete('/brand/destroy/{id}', [App\Http\Controllers\BrandsController::class, 'destroy'])->name('brand.destroy');

    Route::post('/brand/destroy-multiple', [App\Http\Controllers\BrandsController::class, 'destroyMultiple'])->name('brand.destroyMultiple');

    //Towns

    Route::get('/towns', [App\Http\Controllers\TownsController::class, 'index'])->name('towns.index');

    Route::post('/town/add', [App\Http\Controllers\TownsController::class, 'store'])->name('town.store');

    Route::post('/town/update', [App\Http\Controllers\TownsController::class, 'update'])->name('town.update');

    Route::get('/town/details/{id}', [App\Http\Controllers\TownsController::class, 'details'])->name('town.details');

    Route::delete('/town/destroy/{id}', [App\Http\Controllers\TownsController::class, 'destroy'])->name('town.destroy');

    Route::post('/town/destroy-multiple', [App\Http\Controllers\TownsController::class, 'destroyMultiple'])->name('town.destroyMultiple');

    //Stores

    Route::get('/stores', [App\Http\Controllers\StoresController::class, 'index'])->name('stores.index');

    Route::post('/store/add', [App\Http\Controllers\StoresController::class, 'store'])->name('store.store');

    Route::post('/store/update', [App\Http\Controllers\StoresController::class, 'update'])->name('store.update');

    Route::get('/store/details/{id}', [App\Http\Controllers\StoresController::class, 'details'])->name('store.details');

    Route::delete('/store/destroy/{id}', [App\Http\Controllers\StoresController::class, 'destroy'])->name('store.destroy');

    Route::post('/store/destroy-multiple', [App\Http\Controllers\StoresController::class, 'destroyMultiple'])->name('store.destroyMultiple');

    //Provinces

    Route::get('/provinces', [App\Http\Controllers\ProvincesController::class, 'index'])->name('provinces.index');

    Route::post('/province/add', [App\Http\Controllers\ProvincesController::class, 'store'])->name('province.store');

    Route::post('/province/update', [App\Http\Controllers\ProvincesController::class, 'update'])->name('province.update');

    Route::get('/province/details/{id}', [App\Http\Controllers\ProvincesController::class, 'details'])->name('province.details');

    Route::delete('/province/destroy/{id}', [App\Http\Controllers\ProvincesController::class, 'destroy'])->name('province.destroy');

    Route::post('/province/destroy-multiple', [App\Http\Controllers\ProvincesController::class, 'destroyMultiple'])->name('province.destroyMultiple');

    //Banks

    Route::get('/banks', [App\Http\Controllers\BanksController::class, 'index'])->name('banks.index');

    Route::post('/bank/add', [App\Http\Controllers\BanksController::class, 'store'])->name('bank.store');

    Route::post('/bank/update', [App\Http\Controllers\BanksController::class, 'update'])->name('bank.update');

    Route::get('/bank/details/{id}', [App\Http\Controllers\BanksController::class, 'details'])->name('bank.details');

    Route::delete('/bank/destroy/{id}', [App\Http\Controllers\BanksController::class, 'destroy'])->name('bank.destroy');

    Route::post('/bank/destroy-multiple', [App\Http\Controllers\BanksController::class, 'destroyMultiple'])->name('bank.destroyMultiple');

    //Disabilities

    Route::get('/disabilities', [App\Http\Controllers\DisabilitiesController::class, 'index'])->name('disabilities.index');

    Route::post('/disability/add', [App\Http\Controllers\DisabilitiesController::class, 'store'])->name('disability.store');

    Route::post('/disability/update', [App\Http\Controllers\DisabilitiesController::class, 'update'])->name('disability.update');

    Route::get('/disability/details/{id}', [App\Http\Controllers\DisabilitiesController::class, 'details'])->name('disability.details');

    Route::delete('/disability/destroy/{id}', [App\Http\Controllers\DisabilitiesController::class, 'destroy'])->name('disability.destroy');

    Route::post('/disability/destroy-multiple', [App\Http\Controllers\DisabilitiesController::class, 'destroyMultiple'])->name('disability.destroyMultiple');

    //Genders

    Route::get('/genders', [App\Http\Controllers\GendersController::class, 'index'])->name('genders.index');

    Route::post('/gender/add', [App\Http\Controllers\GendersController::class, 'store'])->name('gender.store');

    Route::post('/gender/update', [App\Http\Controllers\GendersController::class, 'update'])->name('gender.update');

    Route::get('/gender/details/{id}', [App\Http\Controllers\GendersController::class, 'details'])->name('gender.details');

    Route::delete('/gender/destroy/{id}', [App\Http\Controllers\GendersController::class, 'destroy'])->name('gender.destroy');

    Route::post('/gender/destroy-multiple', [App\Http\Controllers\GendersController::class, 'destroyMultiple'])->name('gender.destroyMultiple');

    //Races

    Route::get('/races', [App\Http\Controllers\RacesController::class, 'index'])->name('races.index');

    Route::post('/race/add', [App\Http\Controllers\RacesController::class, 'store'])->name('race.store');

    Route::post('/race/update', [App\Http\Controllers\RacesController::class, 'update'])->name('race.update');

    Route::get('/race/details/{id}', [App\Http\Controllers\RacesController::class, 'details'])->name('race.details');

    Route::delete('/race/destroy/{id}', [App\Http\Controllers\RacesController::class, 'destroy'])->name('race.destroy');

    Route::post('/race/destroy-multiple', [App\Http\Controllers\RacesController::class, 'destroyMultiple'])->name('race.destroyMultiple');

    //Durations

    Route::get('/durations', [App\Http\Controllers\DurationsController::class, 'index'])->name('durations.index');

    Route::post('/duration/add', [App\Http\Controllers\DurationsController::class, 'store'])->name('duration.store');

    Route::post('/duration/update', [App\Http\Controllers\DurationsController::class, 'update'])->name('duration.update');

    Route::get('/duration/details/{id}', [App\Http\Controllers\DurationsController::class, 'details'])->name('duration.details');

    Route::delete('/duration/destroy/{id}', [App\Http\Controllers\DurationsController::class, 'destroy'])->name('duration.destroy');

    Route::post('/duration/destroy-multiple', [App\Http\Controllers\DurationsController::class, 'destroyMultiple'])->name('duration.destroyMultiple');

    //Educations

    Route::get('/educations', [App\Http\Controllers\EducationsController::class, 'index'])->name('educations.index');

    Route::post('/education/add', [App\Http\Controllers\RacesController::class, 'store'])->name('education.store');

    Route::post('/education/update', [App\Http\Controllers\RacesController::class, 'update'])->name('education.update');

    Route::get('/education/details/{id}', [App\Http\Controllers\RacesController::class, 'details'])->name('education.details');

    Route::delete('/education/destroy/{id}', [App\Http\Controllers\RacesController::class, 'destroy'])->name('education.destroy');

    Route::post('/education/destroy-multiple', [App\Http\Controllers\RacesController::class, 'destroyMultiple'])->name('education.destroyMultiple');

    //Languages

    Route::get('/languages', [App\Http\Controllers\LanguagesController::class, 'index'])->name('languages.index');

    Route::post('/language/add', [App\Http\Controllers\LanguagesController::class, 'store'])->name('language.store');

    Route::post('/language/update', [App\Http\Controllers\LanguagesController::class, 'update'])->name('language.update');

    Route::get('/language/details/{id}', [App\Http\Controllers\LanguagesController::class, 'details'])->name('language.details');

    Route::delete('/language/destroy/{id}', [App\Http\Controllers\LanguagesController::class, 'destroy'])->name('language.destroy');

    Route::post('/language/destroy-multiple', [App\Http\Controllers\LanguagesController::class, 'destroyMultiple'])->name('language.destroyMultiple');

    //Reasons

    Route::get('/reasons', [App\Http\Controllers\ReasonsController::class, 'index'])->name('reasons.index');

    Route::post('/reason/add', [App\Http\Controllers\ReasonsController::class, 'store'])->name('reason.store');

    Route::post('/reason/update', [App\Http\Controllers\ReasonsController::class, 'update'])->name('reason.update');

    Route::get('/reason/details/{id}', [App\Http\Controllers\ReasonsController::class, 'details'])->name('reason.details');

    Route::delete('/reason/destroy/{id}', [App\Http\Controllers\ReasonsController::class, 'destroy'])->name('reason.destroy');

    Route::post('/reason/destroy-multiple', [App\Http\Controllers\ReasonsController::class, 'destroyMultiple'])->name('reason.destroyMultiple');

    //Transports

    Route::get('/transports', [App\Http\Controllers\TransportsController::class, 'index'])->name('transports.index');

    Route::post('/transport/add', [App\Http\Controllers\TransportsController::class, 'store'])->name('transport.store');

    Route::post('/transport/update', [App\Http\Controllers\TransportsController::class, 'update'])->name('transport.update');

    Route::get('/transport/details/{id}', [App\Http\Controllers\TransportsController::class, 'details'])->name('transport.details');

    Route::delete('/transport/destroy/{id}', [App\Http\Controllers\TransportsController::class, 'destroy'])->name('transport.destroy');

    Route::post('/transport/destroy-multiple', [App\Http\Controllers\TransportsController::class, 'destroyMultiple'])->name('transport.destroyMultiple');

    //Experience

    Route::get('/experience', [App\Http\Controllers\ExperienceController::class, 'index'])->name('experience.index');

    Route::post('/experience/add', [App\Http\Controllers\ExperienceController::class, 'store'])->name('experience.store');

    Route::post('/experience/update', [App\Http\Controllers\ExperienceController::class, 'update'])->name('experience.update');

    Route::get('/experience/details/{id}', [App\Http\Controllers\ExperienceController::class, 'details'])->name('experience.details');

    Route::delete('/experience/destroy/{id}', [App\Http\Controllers\ExperienceController::class, 'destroy'])->name('experience.destroy');

    Route::post('/experience/destroy-multiple', [App\Http\Controllers\ExperienceController::class, 'destroyMultiple'])->name('experience.destroyMultiple');

    //Physical

    Route::get('/physical', [App\Http\Controllers\PhysicalController::class, 'index'])->name('physical.index');

    Route::post('/physical/add', [App\Http\Controllers\PhysicalController::class, 'store'])->name('physical.store');

    Route::post('/physical/update', [App\Http\Controllers\PhysicalController::class, 'update'])->name('physical.update');

    Route::get('/physical/details/{id}', [App\Http\Controllers\PhysicalController::class, 'details'])->name('physical.details');

    Route::delete('/physical/destroy/{id}', [App\Http\Controllers\PhysicalController::class, 'destroy'])->name('physical.destroy');

    Route::post('/physical/destroy-multiple', [App\Http\Controllers\PhysicalController::class, 'destroyMultiple'])->name('physical.destroyMultiple');

    //Qualifications

    Route::get('/qualifications', [App\Http\Controllers\QualificationsController::class, 'index'])->name('qualifications.index');

    Route::post('/qualification/add', [App\Http\Controllers\QualificationsController::class, 'store'])->name('qualification.store');

    Route::post('/qualification/update', [App\Http\Controllers\QualificationsController::class, 'update'])->name('qualification.update');

    Route::get('/qualification/details/{id}', [App\Http\Controllers\QualificationsController::class, 'details'])->name('qualification.details');

    Route::delete('/qualification/destroy/{id}', [App\Http\Controllers\QualificationsController::class, 'destroy'])->name('qualification.destroy');

    Route::post('/qualification/destroy-multiple', [App\Http\Controllers\QualificationsController::class, 'destroyMultiple'])->name('qualification.destroyMultiple');

    //Responsibilities

    Route::get('/responsibilities', [App\Http\Controllers\ResponsibilitiesController::class, 'index'])->name('responsibilities.index');

    Route::post('/responsibility/add', [App\Http\Controllers\ResponsibilitiesController::class, 'store'])->name('responsibility.store');

    Route::post('/responsibility/update', [App\Http\Controllers\ResponsibilitiesController::class, 'update'])->name('responsibility.update');

    Route::get('/responsibility/details/{id}', [App\Http\Controllers\ResponsibilitiesController::class, 'details'])->name('responsibility.details');

    Route::delete('/responsibility/destroy/{id}', [App\Http\Controllers\ResponsibilitiesController::class, 'destroy'])->name('responsibility.destroy');

    Route::post('/responsibility/destroy-multiple', [App\Http\Controllers\ResponsibilitiesController::class, 'destroyMultiple'])->name('responsibility.destroyMultiple');

    //Salaries

    Route::get('/salaries', [App\Http\Controllers\SalariesController::class, 'index'])->name('salaries.index');

    Route::post('/salary/add', [App\Http\Controllers\SalariesController::class, 'store'])->name('salary.store');

    Route::post('/salary/update', [App\Http\Controllers\SalariesController::class, 'update'])->name('salary.update');

    Route::get('/salary/details/{id}', [App\Http\Controllers\SalariesController::class, 'details'])->name('salary.details');

    Route::delete('/salary/destroy/{id}', [App\Http\Controllers\SalariesController::class, 'destroy'])->name('salary.destroy');

    Route::post('/salary/destroy-multiple', [App\Http\Controllers\SalariesController::class, 'destroyMultiple'])->name('salary.destroyMultiple');

    //Skills

    Route::get('/skills', [App\Http\Controllers\SkillsController::class, 'index'])->name('skills.index');

    Route::post('/skill/add', [App\Http\Controllers\SkillsController::class, 'store'])->name('skill.store');

    Route::post('/skill/update', [App\Http\Controllers\SkillsController::class, 'update'])->name('skill.update');

    Route::get('/skill/details/{id}', [App\Http\Controllers\SkillsController::class, 'details'])->name('skill.details');

    Route::delete('/skill/destroy/{id}', [App\Http\Controllers\SkillsController::class, 'destroy'])->name('skill.destroy');

    Route::post('/skill/destroy-multiple', [App\Http\Controllers\SkillsController::class, 'destroyMultiple'])->name('skill.destroyMultiple');

    //Success Factors

    Route::get('/success-factors', [App\Http\Controllers\SuccessFactorsController::class, 'index'])->name('success-factors.index');

    Route::post('/success-factor/add', [App\Http\Controllers\SuccessFactorsController::class, 'store'])->name('success-factor.store');

    Route::post('/success-factor/update', [App\Http\Controllers\SuccessFactorsController::class, 'update'])->name('success-factor.update');

    Route::get('/success-factor/details/{id}', [App\Http\Controllers\SuccessFactorsController::class, 'details'])->name('success-factor.details');

    Route::delete('/success-factor/destroy/{id}', [App\Http\Controllers\SuccessFactorsController::class, 'destroy'])->name('success-factor.destroy');

    Route::post('/success-factor/destroy-multiple', [App\Http\Controllers\SuccessFactorsController::class, 'destroyMultiple'])->name('success-factor.destroyMultiple');

    //Working Hours

    Route::get('/hours', [App\Http\Controllers\HoursController::class, 'index'])->name('hours.index');

    Route::post('/hour/add', [App\Http\Controllers\HoursController::class, 'store'])->name('hour.store');

    Route::post('/hour/update', [App\Http\Controllers\HoursController::class, 'update'])->name('hour.update');

    Route::get('/hour/details/{id}', [App\Http\Controllers\HoursController::class, 'details'])->name('hour.details');

    Route::delete('/hour/destroy/{id}', [App\Http\Controllers\HoursController::class, 'destroy'])->name('hour.destroy');

    Route::post('/hour/destroy-multiple', [App\Http\Controllers\HoursController::class, 'destroyMultiple'])->name('hour.destroyMultiple');

    //Interview Guide

    Route::get('/guide', [App\Http\Controllers\InterviewGuideController::class, 'index'])->name('guide.index');

    Route::post('/guide/update', [App\Http\Controllers\InterviewGuideController::class, 'update'])->name('guide.update');

    Route::get('/guide/details/{id}', [App\Http\Controllers\InterviewGuideController::class, 'details'])->name('guide.details');

    //Interview Template

    Route::get('/template', [App\Http\Controllers\InterviewTemplateController::class, 'index'])->name('template.index');

    Route::post('/template/question/add', [App\Http\Controllers\InterviewTemplateController::class, 'questionStore'])->name('template.question.store');

    Route::post('/template/question/update', [App\Http\Controllers\InterviewTemplateController::class, 'questionUpdate'])->name('template.question.update');

    Route::get('/template/question/details/{id}', [App\Http\Controllers\InterviewTemplateController::class, 'questionDetails'])->name('template.question.details');

    Route::delete('/template/question/destroy/{id}', [App\Http\Controllers\InterviewTemplateController::class, 'questionDestroy'])->name('template.question.destroy');

    Route::post('/template/add', [App\Http\Controllers\InterviewTemplateController::class, 'store'])->name('template.store');

    Route::delete('/template/destroy/{id}', [App\Http\Controllers\InterviewTemplateController::class, 'destroy'])->name('template.destroy');
});

/*
|--------------------------------------------------------------------------
| Regional People Partner (RPP) Routes
|--------------------------------------------------------------------------
*/

Route::prefix('rpp')->middleware(['auth', 'verified', 'role:3', 'user.activity'])->group(function () {
    //Home

    Route::get('/home', [App\Http\Controllers\RPPController::class, 'index'])->name('rpp.home');

    //Update Data

    Route::get('/updateData', [App\Http\Controllers\RPPController::class, 'updateData'])->name('rpp.updateData');
});

/*
|--------------------------------------------------------------------------
| Divisional Talent Development Partner (DTDP) Routes
|--------------------------------------------------------------------------
*/

Route::prefix('dtdp')->middleware(['auth', 'verified', 'role:4', 'user.activity'])->group(function () {
    //Home

    Route::get('/home', [App\Http\Controllers\DTDPController::class, 'index'])->name('dtdp.home');

    //Update Data

    Route::get('/updateData', [App\Http\Controllers\DTDPController::class, 'updateData'])->name('dtdp.updateData');
});

/*
|--------------------------------------------------------------------------
| Divisional People Partner (DPP) Routes
|--------------------------------------------------------------------------
*/

Route::prefix('dpp')->middleware(['auth', 'verified', 'role:5', 'user.activity'])->group(function () {
    //Home

    Route::get('/home', [App\Http\Controllers\DPPController::class, 'index'])->name('dpp.home');

    //Update Data

    Route::get('/updateData', [App\Http\Controllers\DPPController::class, 'updateData'])->name('dpp.updateData');
});

/*
|--------------------------------------------------------------------------
| Manager Routes
|--------------------------------------------------------------------------
*/

Route::prefix('manager')->middleware(['auth', 'verified', 'role:1,2,3,4,5,6', 'user.activity'])->group(function () {
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

    Route::post('/applicant-interview-store', [App\Http\Controllers\ApplicantProfileController::class, 'interview'])->name('applicant-interview.store');

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

    //Route::get('/application', [App\Http\Controllers\ApplicationController::class, 'index'])->name('application.index');

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

    //Queries
    Route::get('/help', [App\Http\Controllers\QueryController::class, 'index'])->name('help.index');

    Route::post('/queries/store', [App\Http\Controllers\QueryController::class, 'store'])->name('query.store');

    //Vacancies

    Route::get('/vacancies', [App\Http\Controllers\VacanciesController::class, 'index'])->middleware('check.user.applicant')->name('vacancies.index');

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

    Route::get('/interviews', [App\Http\Controllers\InterviewController::class, 'index'])->middleware('check.user.applicant')->name('interviews.index');

    Route::post('/interview-confirm', [App\Http\Controllers\InterviewController::class, 'confirm'])->name('interview.approve');

    Route::post('/interview-decline', [App\Http\Controllers\InterviewController::class, 'decline'])->name('interview.decline');

    Route::post('/interview-reschedule', [App\Http\Controllers\InterviewController::class, 'reschedule'])->name('interview.reschedule');

    Route::post('/interview-complete', [App\Http\Controllers\InterviewController::class, 'complete'])->name('interview.complete');

    Route::post('/interview-cancel', [App\Http\Controllers\InterviewController::class, 'cancel'])->name('interview.cancel');

    Route::post('/interview-noShow', [App\Http\Controllers\InterviewController::class, 'noShow'])->name('interview.noShow');


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

//Shoops
Route::post('/shoops', [App\Http\Controllers\ShoopsController::class, 'shoops'])->name('shoops');

Route::get('/shoops', function(Request $request) {
    $hubVerifyToken = "RECRUITMENT";
    $challenge = $request->query('hub_challenge');
    $tokenSent = $request->query('hub_verify_token');

    if ($tokenSent === $hubVerifyToken) {
        return response($challenge);
    } else {
        return response()->json(['message' => 'Forbidden'], 403);
    }
});

//Jira
Route::post('/jira', [App\Http\Controllers\JiraController::class, 'handle'])->middleware('verify.jira')->name('jira');

//Pages

Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');