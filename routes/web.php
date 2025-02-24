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

Route::prefix('admin')->middleware(['auth', 'verified', 'role:1,2', 'user.activity'])->group(function () {
    //Home

    Route::get('/home', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.home');

    //Update Data

    Route::get('/update-dashboard', [App\Http\Controllers\AdminController::class, 'updateDashboard'])->name('admin.updateDashboard');

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

    //Applicants Table

    Route::get('/applicants-table', [App\Http\Controllers\ApplicantsTableController::class, 'index'])->name('applicants-table.index');

    Route::post('/applicants-table/update', [App\Http\Controllers\ApplicantsTableController::class, 'update'])->name('applicants-table.update');

    Route::get('/applicants-table/details/{id}', [App\Http\Controllers\ApplicantsTableController::class, 'details'])->name('applicants-table.details');

    Route::delete('/applicants-table/destroy/{id}', [App\Http\Controllers\ApplicantsTableController::class, 'destroy'])->name('applicants-table.destroy');

    Route::post('/applicants-table/destroy-multiple', [App\Http\Controllers\ApplicantsTableController::class, 'destroyMultiple'])->name('applicants-table.destroyMultiple');

    Route::get('/api/applicants/fetch', [App\Http\Controllers\ApplicantsTableController::class, 'fetchApplicants'])->name('applicants-table.fetchApplicants');

    //Users

    Route::get('/users', [App\Http\Controllers\UsersController::class, 'index'])->name('users.index');

    Route::get('user', [App\Http\Controllers\UsersController::class, 'user'])->name('users.user');

    Route::post('/users/add', [App\Http\Controllers\UsersController::class, 'store'])->name('users.store');

    Route::post('/users/update', [App\Http\Controllers\UsersController::class, 'update'])->name('users.update');

    Route::get('/users/details/{id}', [App\Http\Controllers\UsersController::class, 'details'])->name('users.details');

    Route::delete('/users/destroy/{id}', [App\Http\Controllers\UsersController::class, 'destroy'])->name('users.destroy');

    Route::post('/users/destroy-multiple', [App\Http\Controllers\UsersController::class, 'destroyMultiple'])->name('users.destroyMultiple');

    Route::post('/users/password-reset', [App\Http\Controllers\UsersController::class, 'passwordReset'])->name('users.password');

    //Managers

    Route::get('/managers', [App\Http\Controllers\ManagersController::class, 'index'])->name('managers.index');

    Route::get('manager', [App\Http\Controllers\ManagersController::class, 'user'])->name('managers.user');

    Route::post('/managers/store', [App\Http\Controllers\ManagersController::class, 'store'])->name('managers.store');

    Route::post('/managers/update', [App\Http\Controllers\ManagersController::class, 'update'])->name('managers.update');

    Route::get('/managers/details/{id}', [App\Http\Controllers\ManagersController::class, 'details'])->name('managers.details');

    Route::delete('/managers/destroy/{id}', [App\Http\Controllers\ManagersController::class, 'destroy'])->name('managers.destroy');

    Route::post('/managers/destroy-multiple', [App\Http\Controllers\ManagersController::class, 'destroyMultiple'])->name('managers.destroyMultiple');

    //DPPs

    Route::get('/dpps', [App\Http\Controllers\DPPsController::class, 'index'])->name('dpps.index');

    Route::post('/dpps/store', [App\Http\Controllers\DPPsController::class, 'store'])->name('dpps.store');

    Route::post('/dpps/update', [App\Http\Controllers\DPPsController::class, 'update'])->name('dpps.update');

    Route::get('/dpps/details/{id}', [App\Http\Controllers\DPPsController::class, 'details'])->name('dpps.details');

    Route::delete('/dpps/destroy/{id}', [App\Http\Controllers\DPPsController::class, 'destroy'])->name('dpps.destroy');

    Route::post('/dpps/destroy-multiple', [App\Http\Controllers\DPPsController::class, 'destroyMultiple'])->name('dpps.destroyMultiple');

    //DTDPs

    Route::get('/dtdps', [App\Http\Controllers\DTDPsController::class, 'index'])->name('dtdps.index');

    Route::post('/dtdps/store', [App\Http\Controllers\DTDPsController::class, 'store'])->name('dtdps.store');

    Route::post('/dtdps/update', [App\Http\Controllers\DTDPsController::class, 'update'])->name('dtdps.update');

    Route::get('/dtdps/details/{id}', [App\Http\Controllers\DTDPsController::class, 'details'])->name('dtdps.details');

    Route::delete('/dtdps/destroy/{id}', [App\Http\Controllers\DTDPsController::class, 'destroy'])->name('dtdps.destroy');

    Route::post('/dtdps/destroy-multiple', [App\Http\Controllers\DTDPsController::class, 'destroyMultiple'])->name('dtdps.destroyMultiple');

    //RPPs

    Route::get('/rpps', [App\Http\Controllers\RPPsController::class, 'index'])->name('rpps.index');

    Route::post('/rpps/store', [App\Http\Controllers\RPPsController::class, 'store'])->name('rpps.store');

    Route::post('/rpps/update', [App\Http\Controllers\RPPsController::class, 'update'])->name('rpps.update');

    Route::get('/rpps/details/{id}', [App\Http\Controllers\RPPsController::class, 'details'])->name('rpps.details');

    Route::delete('/rpps/destroy/{id}', [App\Http\Controllers\RPPsController::class, 'destroy'])->name('rpps.destroy');

    Route::post('/rpps/destroy-multiple', [App\Http\Controllers\RPPsController::class, 'destroyMultiple'])->name('rpps.destroyMultiple');

    //Admins

    Route::get('/admins', [App\Http\Controllers\AdminsController::class, 'index'])->name('admins.index');

    Route::post('/admins/store', [App\Http\Controllers\AdminsController::class, 'store'])->name('admins.store');

    Route::post('/admins/update', [App\Http\Controllers\AdminsController::class, 'update'])->name('admins.update');

    Route::get('/admins/details/{id}', [App\Http\Controllers\AdminsController::class, 'details'])->name('admins.details');

    Route::delete('/admins/destroy/{id}', [App\Http\Controllers\AdminsController::class, 'destroy'])->name('admins.destroy');

    Route::post('/admins/destroy-multiple', [App\Http\Controllers\AdminsController::class, 'destroyMultiple'])->name('admins.destroyMultiple');

    //Super Admins

    Route::get('/super-admins', [App\Http\Controllers\SuperAdminsController::class, 'index'])->name('super-admins.index');

    Route::post('/super-admins/store', [App\Http\Controllers\SuperAdminsController::class, 'store'])->name('super-admins.store');

    Route::post('/super-admins/update', [App\Http\Controllers\SuperAdminsController::class, 'update'])->name('super-admins.update');

    Route::get('/super-admins/details/{id}', [App\Http\Controllers\SuperAdminsController::class, 'details'])->name('super-admins.details');

    Route::delete('/super-admins/destroy/{id}', [App\Http\Controllers\SuperAdminsController::class, 'destroy'])->name('super-admins.destroy');

    Route::post('/super-admins/destroy-multiple', [App\Http\Controllers\SuperAdminsController::class, 'destroyMultiple'])->name('super-admins.destroyMultiple');

    //Impersonate

    Route::get('/impersonate/{id}', [App\Http\Controllers\ImpersonateController::class, 'impersonate'])->name('impersonate');

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

    Route::get('/situational', [App\Http\Controllers\SituationalController::class, 'index'])->name('situational.index');

    Route::post('/situational/add', [App\Http\Controllers\SituationalController::class, 'store'])->name('situational.store');

    Route::post('/situational/update', [App\Http\Controllers\SituationalController::class, 'update'])->name('situational.update');

    Route::get('/situational/details/{id}', [App\Http\Controllers\SituationalController::class, 'details'])->name('situational.details');

    Route::delete('/situational/destroy/{id}', [App\Http\Controllers\SituationalController::class, 'destroy'])->name('situational.destroy');

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

    //Regions
    Route::get('/regions', [App\Http\Controllers\RegionsController::class, 'index'])->name('regions.index');

    Route::post('/regions/add', [App\Http\Controllers\RegionsController::class, 'store'])->name('regions.store');

    Route::post('/regions/update', [App\Http\Controllers\RegionsController::class, 'update'])->name('regions.update');

    Route::get('/regions/details/{id}', [App\Http\Controllers\RegionsController::class, 'details'])->name('regions.details');

    Route::delete('/regions/destroy/{id}', [App\Http\Controllers\RegionsController::class, 'destroy'])->name('regions.destroy');

    Route::post('/regions/destroy-multiple', [App\Http\Controllers\RegionsController::class, 'destroyMultiple'])->name('regions.destroyMultiple');

    //Divisions
    Route::get('/divisions', [App\Http\Controllers\DivisionsController::class, 'index'])->name('divisions.index');

    Route::post('/divisions/add', [App\Http\Controllers\DivisionsController::class, 'store'])->name('divisions.store');

    Route::post('/divisions/update', [App\Http\Controllers\DivisionsController::class, 'update'])->name('divisions.update');

    Route::get('/divisions/details/{id}', [App\Http\Controllers\DivisionsController::class, 'details'])->name('divisions.details');

    Route::delete('/divisions/destroy/{id}', [App\Http\Controllers\DivisionsController::class, 'destroy'])->name('divisions.destroy');

    Route::post('/divisions/destroy-multiple', [App\Http\Controllers\DivisionsController::class, 'destroyMultiple'])->name('divisions.destroyMultiple');


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

    //FAQs

    Route::get('/faqs', [App\Http\Controllers\FAQsController::class, 'index'])->name('faqs.index');

    Route::post('/faqs/add', [App\Http\Controllers\FAQsController::class, 'store'])->name('faqs.store');

    Route::post('/faqs/update', [App\Http\Controllers\FAQsController::class, 'update'])->name('faqs.update');

    Route::get('/faqs/details/{id}', [App\Http\Controllers\FAQsController::class, 'details'])->name('faqs.details');

    Route::delete('/faqs/destroy/{id}', [App\Http\Controllers\FAQsController::class, 'destroy'])->name('faqs.destroy');

    Route::post('/faqs/destroy-multiple', [App\Http\Controllers\FAQsController::class, 'destroyMultiple'])->name('faqs.destroyMultiple');

    // Applicants Reports

    Route::get('/reports/applicants', [App\Http\Controllers\Reports\ApplicantsReportController::class, 'index'])->name('applicants.reports.index');

    Route::post('/reports/applicants/update', [App\Http\Controllers\Reports\ApplicantsReportController::class, 'update'])->name('applicants.reports.update');

    Route::post('/reports/applicants/export', [App\Http\Controllers\Reports\ApplicantsReportController::class, 'export'])->name('applicants.reports.export');

    Route::get('/api/applicants-metrics', [App\Http\Controllers\Reports\ApplicantsReportController::class, 'getApplicantsMetrics'])->name('applicants.reports.metrics');

    Route::get('/api/graph-metrics', [App\Http\Controllers\Reports\ApplicantsReportController::class, 'getApplicantsGraphMetrics'])->name('graph.reports.metrics');
});

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/

// Reports for Stores and Vacancies (Restricted to roles 3, 4, 5)
Route::prefix('admin/reports')->middleware(['auth', 'verified', 'role:1,2,3,4,5', 'user.activity'])->group(function () {
    // Stores Reports

    Route::get('/stores', [App\Http\Controllers\Reports\StoresReportController::class, 'index'])->name('stores.reports.index');

    Route::post('/stores/update', [App\Http\Controllers\Reports\StoresReportController::class, 'update'])->name('stores.reports.update');

    Route::post('/stores/export', [App\Http\Controllers\Reports\StoresReportController::class, 'export'])->name('stores.reports.export');

    // Vacancies Reports

    Route::get('/vacancies', [App\Http\Controllers\Reports\VacanciesReportController::class, 'index'])->name('vacancies.reports.index');

    Route::post('/vacancies/update', [App\Http\Controllers\Reports\VacanciesReportController::class, 'update'])->name('vacancies.reports.update');

    Route::post('/vacancies/export', [App\Http\Controllers\Reports\VacanciesReportController::class, 'export'])->name('vacancies.reports.export');
});

/*
|--------------------------------------------------------------------------
| Regional People Partner (RPP) Routes
|--------------------------------------------------------------------------
*/

Route::prefix('rpp')->middleware(['auth', 'verified', 'role:1,2,3', 'user.activity'])->group(function () {
    //Home

    Route::get('/home', [App\Http\Controllers\RPPController::class, 'index'])->name('rpp.home');

    Route::get('/update-dashboard', [App\Http\Controllers\RPPController::class, 'updateDashboard'])->name('rpp.updateDashboard');

    //Update Data

    Route::get('/updateData', [App\Http\Controllers\RPPController::class, 'updateData'])->name('rpp.updateData');
});

/*
|--------------------------------------------------------------------------
| Divisional Talent Development Partner (DTDP) Routes
|--------------------------------------------------------------------------
*/

Route::prefix('dtdp')->middleware(['auth', 'verified', 'role:1,2,4', 'user.activity'])->group(function () {
    //Home

    Route::get('/home', [App\Http\Controllers\DTDPController::class, 'index'])->name('dtdp.home');

    Route::get('/update-dashboard', [App\Http\Controllers\DTDPController::class, 'updateDashboard'])->name('dtdp.updateDashboard');

    //Update Data

    Route::get('/updateData', [App\Http\Controllers\DTDPController::class, 'updateData'])->name('dtdp.updateData');
});

/*
|--------------------------------------------------------------------------
| Divisional People Partner (DPP) Routes
|--------------------------------------------------------------------------
*/

Route::prefix('dpp')->middleware(['auth', 'verified', 'role:1,2,5', 'user.activity'])->group(function () {
    //Home

    Route::get('/home', [App\Http\Controllers\DPPController::class, 'index'])->name('dpp.home');

    Route::get('/update-dashboard', [App\Http\Controllers\DTDPController::class, 'updateDashboard'])->name('dpp.updateDashboard');

    //Fetch Data
    Route::get('/api/time-metrics', [App\Http\Controllers\DataController::class, 'getTimeMetrics'])->name('dpp.time.metrics');

    Route::get('/api/proximity-metrics', [App\Http\Controllers\DataController::class, 'getProximityMetrics'])->name('dpp.proximity.metrics');

    Route::get('/api/proximity-metrics/talentpool', [App\Http\Controllers\DataController::class, 'getTalentPoolProximityMetrics'])->name('dpp.proximity.metrics.talentpool');

    Route::get('/api/proximity-metrics/appointed', [App\Http\Controllers\DataController::class, 'getApplicantsAppointedProximityMetrics'])->name('dpp.proximity.metrics.appointed');

    Route::get('/api/average-score-metrics', [App\Http\Controllers\DataController::class, 'getAverageScoreMetrics'])->name('dpp.average-score.metrics');

    Route::get('/api/assessment-scores-metrics', [App\Http\Controllers\DataController::class, 'getAssessmentScores'])->name('dpp.assessment-scores.metrics');

    Route::get('/api/vacancies-metrics', [App\Http\Controllers\DataController::class, 'getVacanciesMetrics'])->name('dpp.vacancies.metrics');

    Route::get('/api/interviews-metrics', [App\Http\Controllers\DataController::class, 'getInterviewsMetrics'])->name('dpp.interviews.metrics');

    Route::get('/api/applicants-metrics', [App\Http\Controllers\DataController::class, 'getApplicantsMetrics'])->name('dpp.applicants.metrics');

    Route::get('/api/talent-pool-metrics', [App\Http\Controllers\DataController::class, 'getTalentPoolMetrics'])->name('dpp.talent-pool.metrics');

    Route::get('/api/demographic-metrics', [App\Http\Controllers\DataController::class, 'getDemographicMetrics'])->name('dpp.demographic.metrics');

    //Update Data

    Route::get('/updateData', [App\Http\Controllers\DPPController::class, 'updateData'])->name('dpp.updateData');
});

/*
|--------------------------------------------------------------------------
| Manager Routes
|--------------------------------------------------------------------------
*/

Route::prefix('manager')->middleware(['auth', 'verified', 'role:1,2,3,4,6', 'user.activity'])->group(function () {
    //Home

    Route::get('/home', [App\Http\Controllers\ManagerController::class, 'index'])->name('manager.home');

    Route::get('/update-dashboard', [App\Http\Controllers\ManagerController::class, 'updateDashboard'])->name('manager.updateDashboard');

    //Fetch Data
    Route::get('/api/time-metrics', [App\Http\Controllers\DataController::class, 'getTimeMetrics'])->name('time.metrics');

    Route::get('/api/proximity-metrics', [App\Http\Controllers\DataController::class, 'getProximityMetrics'])->name('proximity.metrics');

    Route::get('/api/proximity-metrics/talentpool', [App\Http\Controllers\DataController::class, 'getTalentPoolProximityMetrics'])->name('proximity.metrics.talentpool');

    Route::get('/api/proximity-metrics/appointed', [App\Http\Controllers\DataController::class, 'getApplicantsAppointedProximityMetrics'])->name('proximity.metrics.appointed');

    Route::get('/api/average-score-metrics', [App\Http\Controllers\DataController::class, 'getAverageScoreMetrics'])->name('average-score.metrics');

    Route::get('/api/assessment-scores-metrics', [App\Http\Controllers\DataController::class, 'getAssessmentScores'])->name('assessment-scores.metrics');

    Route::get('/api/vacancies-metrics', [App\Http\Controllers\DataController::class, 'getVacanciesMetrics'])->name('vacancies.metrics');

    Route::get('/api/interviews-metrics', [App\Http\Controllers\DataController::class, 'getInterviewsMetrics'])->name('interviews.metrics');

    Route::get('/api/applicants-metrics', [App\Http\Controllers\DataController::class, 'getApplicantsMetrics'])->name('applicants.metrics');

    Route::get('/api/talent-pool-metrics', [App\Http\Controllers\DataController::class, 'getTalentPoolMetrics'])->name('talent-pool.metrics');

    Route::get('/api/application-channels-metrics', [App\Http\Controllers\DataController::class, 'getApplicationChannelsMetrics'])->name('application-channels.metrics');

    Route::get('/api/application-completion-metrics', [App\Http\Controllers\DataController::class, 'getApplicationCompletionMetrics'])->name('application-completion.metrics');

    Route::get('/api/stores-metrics', [App\Http\Controllers\DataController::class, 'getStoresMetrics'])->name('stores.metrics');

    Route::get('/api/demographic-metrics', [App\Http\Controllers\DataController::class, 'getDemographicMetrics'])->name('demographic.metrics');

    Route::get('/api/gender-metrics', [App\Http\Controllers\DataController::class, 'getGenderMetrics'])->name('gender.metrics');

    Route::get('/api/province-metrics', [App\Http\Controllers\DataController::class, 'getProvinceMetrics'])->name('province.metrics');

    //User Profile

    Route::get('/user-profile', [App\Http\Controllers\UserProfileController::class, 'index'])->name('user-profile.index');

    //Applicants

    Route::get('/applicants', [App\Http\Controllers\ApplicantsController::class, 'index'])->name('applicants.index');

    Route::get('/applicants-data', [App\Http\Controllers\ApplicantsController::class, 'applicants'])->name('applicants.data');

    //Vacancies

    Route::get('/vacancies', [App\Http\Controllers\ManagerController::class, 'vacancies'])->name('manager.vacancies');

    //Vacancy

    Route::get('/vacancy', [App\Http\Controllers\VacancyController::class, 'index'])->name('vacancy.index');

    Route::post('/vacancy/store', [App\Http\Controllers\VacancyController::class, 'store'])->name('vacancy.store');

    Route::post('/vacancy/update', [App\Http\Controllers\VacancyController::class, 'update'])->name('vacancy.update');

    Route::delete('/vacancy/destroy/{id}', [App\Http\Controllers\VacancyController::class, 'destroy'])->name('vacancy.destroy');

    Route::post('/vacancy/destroy-multiple', [App\Http\Controllers\VacancyController::class, 'destroyMultiple'])->name('vacancy.destroyMultiple');

    Route::post('/vacancy/fill', [App\Http\Controllers\VacancyController::class, 'vacancyFill'])->name('vacancy.fill');

    //Vacancies

    Route::get('/my-vacancies', [App\Http\Controllers\VacanciesController::class, 'index'])->name('vacancies.index');

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

    //Queries
    Route::get('/help', [App\Http\Controllers\QueryController::class, 'index'])->name('help.index');

    Route::post('/queries/store', [App\Http\Controllers\QueryController::class, 'store'])->name('query.store');

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

    Route::get('/verify-email-update', [App\Http\Controllers\ProfileSettingsController::class, 'verifyEmailUpdate'])->name('verify.email.update');

    //Save

    Route::put('/applicant-save/{id}', [App\Http\Controllers\SaveController::class, 'applicantSave'])->name('applicant.save');

    Route::put('/vacancy-save/{id}', [App\Http\Controllers\SaveController::class, 'vacancySave'])->name('vacancy.save');

    //Applications

    Route::put('/apply/{id}', [App\Http\Controllers\ApplyController::class, 'vacancyApply'])->name('vacancy.apply');

    Route::put('/apply-approve', [App\Http\Controllers\ApplyController::class, 'approve'])->name('application.approve');

    Route::put('/apply-decline', [App\Http\Controllers\ApplyController::class, 'decline'])->name('application.decline');

    //Interviews

    Route::get('/interviews', [App\Http\Controllers\InterviewController::class, 'index'])->middleware('check.user.applicant')->name('interviews.index');

    Route::post('/interview-confirm', [App\Http\Controllers\InterviewController::class, 'confirm'])->name('interview.confirm');

    Route::post('/interview-decline', [App\Http\Controllers\InterviewController::class, 'decline'])->name('interview.decline');

    Route::post('/interview-reschedule', [App\Http\Controllers\InterviewController::class, 'reschedule'])->name('interview.reschedule');

    Route::post('/interview-complete', [App\Http\Controllers\InterviewController::class, 'complete'])->name('interview.complete');

    Route::post('/interview-cancel', [App\Http\Controllers\InterviewController::class, 'cancel'])->name('interview.cancel');

    Route::post('/interview-noShow', [App\Http\Controllers\InterviewController::class, 'noShow'])->name('interview.noShow');


    //Notifications

    Route::put('/notification-read', [App\Http\Controllers\NotificationController::class, 'notificationRead'])->name('notification.read');

    Route::put('/notification-remove', [App\Http\Controllers\NotificationController::class, 'notificationRemove'])->name('notification.remove');

    //Stop Impersonating

    Route::get('/impersonate_leave/{role_id?}', [App\Http\Controllers\ImpersonateController::class, 'stopImpersonating'])->name('impersonate.leave');
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