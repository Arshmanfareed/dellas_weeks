<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DasboardController;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RolespermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\MaindashboardController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignElementController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CsvController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\ScheduleCampaign;
use App\Http\Controllers\UnipileController;
use App\Http\Controllers\LinkedInController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\TestController;

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

/* This below is only for testing */

Route::get('/test_route', [TestController::class, 'base']);

/* These are home pages url which does not require any authentication */
Route::get('/', [HomeController::class, 'home']);
Route::get('/about', [HomeController::class, 'about']);
Route::get('/pricing', [HomeController::class, 'pricing']);
Route::get('/faq', [HomeController::class, 'faq']);

/* These are login and signup url which does not require any authentication */
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/check-credentials', [LoginController::class, 'checkCredentials'])->name('checkCredentials');
Route::get('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/register-user', [RegisterController::class, 'registerUser'])->name('register-user'); // Need to work on it
Route::post('/add_email_account', [LinkedInController::class, 'addEmailToAccount'])->name('addEmailAccount');
Route::post('/create-link-account', [LinkedInController::class, 'createLinkAccount'])->name('createLinkAccount');

/* These are for actions like campaign and leads */
Route::match(['get', 'post'], '/unipile-callback', [UnipileController::class, 'handleCallback']);
Route::get('/delete_an_account', [LinkedInController::class, 'delete_an_account'])->name('delete_an_account');

/* These are for dashboard which requires authentication */
Route::middleware(['userAuth'])->group(function () {
    /* These are for dashboard which does not require seat_id in session */
    Route::get('/dashboard', [DasboardController::class, 'dashboard'])->name('dashobardz');
    Route::get('/blacklist', [BlacklistController::class, 'blacklist']);
    Route::get('/team', [TeamController::class, 'team']);
    Route::get('/invoice', [InvoiceController::class, 'invoice']);
    Route::get('/roles-and-permission-setting', [SettingController::class, 'settingrolespermission']);
    Route::prefix('seat')->group(function () {
        Route::get('/getSeatById/{id}', [SeatController::class, 'get_seat_by_id'])->name('getSeatById');
        Route::get('/deleteSeat/{id}', [SeatController::class, 'delete_seat'])->name('deleteSeat');
        Route::get('/updateName/{id}/{seat_name}', [SeatController::class, 'update_name'])->name('updateName');
        Route::get('/filterSeat/{search}', [SeatController::class, 'filterSeat'])->name('filterSeat');
    });
    Route::controller(StripePaymentController::class)->group(function () {
        Route::get('stripe', 'stripe');
        Route::post('stripe', 'stripePost')->name('stripe.post');
    });
    Route::get('/team-rolesandpermission', [RolespermissionController::class, 'rolespermission']);
    Route::post('/logout', [LoginController::class, 'logoutUser'])->name('logout-user');
    
    /* This dashboard uses to update seat_id in session */
    Route::match(['get', 'post'], '/accdashboard', [MaindashboardController::class, 'maindasboard'])->name('acc_dash'); // Need to work on it

    /* This setting might not requires account connectivity */
    Route::get('/setting', [SettingController::class, 'setting'])->name('dash-settings'); // Need to work on it

    /* These are for dashboard which requires account connectivity */
    Route::middleware(['linkedinAccount'])->group(function () {
        Route::prefix('campaign')->group(function () {
            Route::get('/', [CampaignController::class, 'campaign'])->name('campaigns');
            Route::get('/createcampaign', [CampaignController::class, 'campaigncreate'])->name('campaigncreate');
            Route::post('/campaigninfo', [CampaignController::class, 'campaigninfo'])->name('campaigninfo');
            Route::post('/createcampaignfromscratch', [CampaignController::class, 'fromscratch'])->name('createcampaignfromscratch');
            Route::get('/campaignDetails/{campaign_id}', [CampaignController::class, 'getCampaignDetails'])->name('campaignDetails');
            Route::get('/changeCampaignStatus/{campaign_id}', [CampaignController::class, 'changeCampaignStatus'])->name('changeCampaignStatus');
            Route::get('/{campaign_id}', [CampaignController::class, 'deleteCampaign'])->name('deleteCampaign');
            Route::get('/archive/{campaign_id}', [CampaignController::class, 'archiveCampaign'])->name('archiveCampaign');
            Route::get('/getcampaignelementbyslug/{slug}', [CampaignElementController::class, 'campaignElement'])->name('getcampaignelementbyslug');
            Route::post('/createCampaign', [CampaignElementController::class, 'createCampaign'])->name('createCampaign');
            Route::get('/getPropertyDatatype/{id}/{element_slug}', [PropertiesController::class, 'getPropertyDatatype'])->name('getPropertyDatatype');
            // Route::get('/scheduleDays/{schedule_id}', [ScheduleCampaign::class, 'scheduleDays'])->name('scheduleDays');
            Route::get('/editcampaign/{campaign_id}', [CampaignController::class, 'editCampaign'])->name('editCampaign');
            Route::post('/editCampaignInfo/{campaign_id}', [CampaignController::class, 'editCampaignInfo'])->name('editCampaignInfo');
            Route::post('/editCampaignSequence/{campaign_id}', [CampaignController::class, 'editCampaignSequence'])->name('editCampaignSequence');
            Route::get('/getcampaignelementbyid/{element_id}', [CampaignElementController::class, 'getcampaignelementbyid'])->name('getcampaignelementbyid');
            Route::post('/updateCampaign/{campaign_id}', [CampaignController::class, 'updateCampaign'])->name('updateCampaign');
            Route::get('/getPropertyRequired/{id}', [PropertiesController::class, 'getPropertyRequired'])->name('getPropertyRequired');
        });
        Route::prefix('leads')->group(function () {
            Route::get('/', [LeadsController::class, 'leads'])->name('dash-leads');
            Route::get('/getLeadsByCampaign/{id}/{search}', [LeadsController::class, 'getLeadsByCampaign'])->name('getLeadsByCampaign');
            Route::post('/sendLeadsToEmail', [LeadsController::class, 'sendLeadsToEmail'])->name('sendLeadsToEmail');
        });
        Route::prefix('message')->group(function () {
            Route::get('/', [MessageController::class, 'message'])->name('dash-messages');
            Route::get('/chat/profile/{profile_id}/{chat_id}', [MessageController::class, 'get_profile_and_latest_message'])->name('get_profile_and_latest_message');
            Route::get('/latest/{chat_id}', [MessageController::class, 'get_latest_Mesage_chat_id'])->name('get_latest_Mesage_chat_id');
            Route::get('/chat/latest/{chat_id}/{count}', [MessageController::class, 'get_latest_message_in_chat'])->name('get_latest_message_in_chat');
            Route::get('/chat/profile/{profile_id}', [MessageController::class, 'get_chat_Profile'])->name('get_chat_Profile');
            Route::get('/chat/receiver/{chat_id}', [MessageController::class, 'get_chat_receive'])->name('get_chat_receive');
            Route::get('/chat/sender', [MessageController::class, 'get_chat_sender'])->name('get_chat_sender');
            Route::get('/chat/{chat_id}', [MessageController::class, 'get_messages_chat_id'])->name('get_messages_chat_id');
            Route::get('/chat/{chat_id}/{cursor}', [MessageController::class, 'get_messages_chat_id_cursor'])->name('get_messages_chat_id_cursor');
            Route::get('/chats/{cursor}', [MessageController::class, 'get_remain_chats'])->name('get_remain_chats');
            Route::post('/send/chat', [MessageController::class, 'send_a_message'])->name('send_a_message');
            Route::post('/search', [MessageController::class, 'message_search'])->name('message_search');
            Route::get('/unread', [MessageController::class, 'unread_message'])->name('unread_message');
            Route::get('/profile/{profile_id}', [MessageController::class, 'profile_by_id'])->name('profile_by_id');
            Route::post('/retrieve/message/attachment', [UnipileController::class, 'retrieve_an_attachment_from_a_message'])->name('retrieve_an_attachment_from_a_message');
        });
        Route::get('/filterCampaign/{filter}/{search}', [CampaignController::class, 'filterCampaign'])->name('filterCampaign');
        Route::post('/createSchedule', [ScheduleCampaign::class, 'createSchedule'])->name('createSchedule');
        Route::get('/filterSchedule/{search}', [ScheduleCampaign::class, 'filterSchedule'])->name('filterSchedule');
        Route::get('/getElements/{campaign_id}', [CampaignElementController::class, 'getElements'])->name('getElements');
        Route::post('/import_csv', [CsvController::class, 'import_csv'])->name('import_csv');
        Route::get('/report', [ReportController::class, 'report'])->name('dash-reports');
        Route::get('/contacts', [ContactController::class, 'contact']);
        Route::get('/integration', [IntegrationController::class, 'integration'])->name('dash-integrations');
        Route::get('/feature-suggestion', [FeatureController::class, 'featuresuggestions'])->name('dash-feature-suggestions');
    });
});
