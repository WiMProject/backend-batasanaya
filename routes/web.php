<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Web Pages
$router->get('/', 'HomeController@index');
$router->get('/docs', 'DocController@index');
$router->get('/admin/login', 'AdminLoginController@index');
$router->get('/admin', 'AdminDashboardController@index');
$router->get('/test/upload', 'TestUploadController@index');

// PHP Info Check
$router->get('/phpinfo', function() {
    return response()->json([
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time')
    ]);
});

// API Routes
$router->group(['prefix' => 'api'], function () use ($router) {
    
    /*
    |--------------------------------------------------------------------------
    | PUBLIC API ROUTES
    |--------------------------------------------------------------------------
    */
    
    // Authentication (Public)
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('register', 'AuthController@register');
        $router->post('login', 'AuthController@login');
        $router->post('admin-login', 'AuthController@adminLogin');
        $router->post('google', 'AuthController@loginWithGoogle');
        $router->post('refresh', 'AuthController@refresh');
    });
    
    // Asset Downloads (Public)
    $router->get('assets/download-all', 'AssetController@downloadAll');
    $router->get('assets/manifest', 'AssetController@manifest');
    $router->get('assets/{id}/file', 'AssetController@download');
    $router->post('assets/download-batch', 'AssetController@downloadBatch');
    
    // Background Downloads (Public)
    $router->get('backgrounds/{id}/file', 'BackgroundController@download');
    
    // Song Downloads (Public)
    $router->get('songs/{id}/file', 'SongController@stream');

    /*
    |--------------------------------------------------------------------------
    | TESTING ROUTES (AssetControllerTesting) - COMMENTED OUT
    |--------------------------------------------------------------------------
    */
    
    // $router->group(['prefix' => 'test/assets'], function () use ($router) {
    //     $router->post('batch', 'AssetControllerTesting@uploadBatch');
    //     $router->delete('batch', 'AssetControllerTesting@deleteBatch');
    //     $router->get('checksum', 'AssetControllerTesting@getChecksum');
    //     $router->patch('{id}', 'AssetControllerTesting@updateAsset');
    //     $router->get('download-all', 'AssetControllerTesting@downloadAll');
    // });

    
    /*
    |--------------------------------------------------------------------------
    | USER ROUTES (Authentication Required)
    |--------------------------------------------------------------------------
    */
    
    $router->group(['middleware' => 'auth'], function () use ($router) {
        
        // Auth Management (User)
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->get('me', 'AuthController@getMe');
            $router->post('logout', 'AuthController@logout');
            $router->post('reset-password', 'AuthController@resetPassword');
            $router->post('set-pin', 'AuthController@setPin');
            $router->post('verify-pin', 'AuthController@verifyPin');
            $router->post('request-otp', 'AuthController@requestOtp');
            $router->post('verify-otp', 'AuthController@verifyOtp');
        });

        // User Profile Management
        $router->get('users/{id}', 'UserController@show');
        $router->patch('users/{id}', 'UserController@update');
        $router->post('user/profile-picture', 'UserController@uploadProfilePicture');

        // User Preferences
        $router->get('user/preference', 'PreferenceController@show');
        $router->patch('user/preference', 'PreferenceController@update');

        // Game Routes (Generic)
        $router->get('games/{gameType}/progress', 'GameController@getProgress');
        $router->post('games/{gameType}/start', 'GameController@startGame');
        $router->post('games/{gameType}/finish', 'GameController@finishGame');
        $router->get('games/{gameType}/stats', 'GameController@getStats');

        /*
        |--------------------------------------------------------------------------
        | ADMIN ROUTES (Admin Role Required)
        |--------------------------------------------------------------------------
        */
        
        // Admin Dashboard
        $router->get('admin/stats', ['middleware' => 'role:admin', 'uses' => 'AdminDashboardController@getStats']);
        
        // Admin - User Management
        $router->post('users', ['middleware' => 'role:admin', 'uses' => 'UserController@store']);
        $router->delete('users/{id}', ['middleware' => 'role:admin', 'uses' => 'UserController@destroy']);
        
        // Admin - Asset Management
        $router->post('assets', ['middleware' => 'role:admin', 'uses' => 'AssetController@store']);
        $router->post('assets/batch', ['middleware' => 'role:admin', 'uses' => 'AssetController@storeBatch']);
        $router->delete('assets/batch', ['middleware' => 'role:admin', 'uses' => 'AssetController@destroyBatch']);
        $router->get('assets', ['middleware' => 'role:admin', 'uses' => 'AssetController@index']);
        $router->patch('assets/{id}', ['middleware' => 'role:admin', 'uses' => 'AssetController@update']);
        $router->delete('assets/{id}', ['middleware' => 'role:admin', 'uses' => 'AssetController@destroy']);
        
        // Admin - Background Management
        $router->post('backgrounds', ['middleware' => 'role:admin', 'uses' => 'BackgroundController@store']);
        $router->get('backgrounds', ['middleware' => 'role:admin', 'uses' => 'BackgroundController@index']);
        $router->get('backgrounds/{id}', ['middleware' => 'role:admin', 'uses' => 'BackgroundController@show']);
        $router->patch('backgrounds/{id}', ['middleware' => 'role:admin', 'uses' => 'BackgroundController@update']);
        $router->delete('backgrounds/{id}', ['middleware' => 'role:admin', 'uses' => 'BackgroundController@destroy']);
        
        // Admin - Song Management
        $router->post('songs', ['middleware' => 'role:admin', 'uses' => 'SongController@store']);
        $router->get('songs', ['middleware' => 'role:admin', 'uses' => 'SongController@index']);
        $router->get('songs/{id}', ['middleware' => 'role:admin', 'uses' => 'SongController@show']);
        $router->patch('songs/{id}', ['middleware' => 'role:admin', 'uses' => 'SongController@update']);
        $router->get('songs/{id}/file', ['middleware' => 'role:admin', 'uses' => 'SongController@stream']);
        $router->delete('songs/{id}', ['middleware' => 'role:admin', 'uses' => 'SongController@destroy']);
        
        // Admin - Video Management
        $router->post('videos', ['middleware' => 'role:admin', 'uses' => 'VideoController@store']);
        $router->get('videos', ['middleware' => 'role:admin', 'uses' => 'VideoController@index']);
        $router->get('videos/{id}', ['middleware' => 'role:admin', 'uses' => 'VideoController@show']);
        $router->patch('videos/{id}', ['middleware' => 'role:admin', 'uses' => 'VideoController@update']);
        $router->get('videos/{id}/file', ['middleware' => 'role:admin', 'uses' => 'VideoController@stream']);
        $router->delete('videos/{id}', ['middleware' => 'role:admin', 'uses' => 'VideoController@destroy']);
        
        // Admin - Game Progress Monitoring
        $router->group(['middleware' => 'role:admin', 'prefix' => 'admin'], function () use ($router) {
            $router->get('dashboard', 'AdminController@dashboard');
            $router->get('users', 'AdminController@getAllUsers');
            $router->get('users/{userId}/game-progress', 'AdminController@getUserGameProgress');
            $router->get('assets', 'AdminController@getAllAssets');
            $router->delete('assets/bulk', 'AdminController@bulkDeleteAssets');
        });
    });
});
