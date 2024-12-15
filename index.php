<?php
date_default_timezone_set('Asia/Dhaka');  // Replace with your desired timezone
session_start();

require __DIR__.'/functions/enum_declaration.php';
require __DIR__.'/functions/functions.php';

require __DIR__.'/controllers/HomeController.php';
require __DIR__.'/controllers/QueryController.php';



// Routes with direct view rendering (GET method)

Route::view('/test','/test.php');
// Route::view('/smtp','/smtp.php');
// Route::view('/run-campaign','config/corn_jobs_daily_campaign.php');


// Routes with controller actions

// ===============================Common Route Start============================= 
Route::match(['GET'], '/', HomeController::class, 'index');
Route::match(['POST'], '/query/class-shifts', QueryController::class, 'classShifts');
Route::match(['POST'], '/query/class-sections', QueryController::class, 'classSections');
Route::match(['POST'], '/query/class-subjects', QueryController::class, 'classSubjects');
Route::match(['POST'], '/query/student-results', QueryController::class, 'subjectWiseResults');
Route::match(['POST'], '/query/store-results', QueryController::class, 'storeResult');


// ===============================Common Route END============================= 

// =======================User Route START============================




// Dispatch the route based on the request
Route::dispatch();

?>
