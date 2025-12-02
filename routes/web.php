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
    $router->get('assets/{id}/file', 'AssetController@download');
    $router->get('assets/download-all', 'AssetController@downloadAll');
    $router->get('assets/manifest', 'AssetController@manifest');
    $router->post('assets/download-batch', 'AssetController@downloadBatch');
    
    // Background Downloads (Public)
    $router->get('backgrounds/{id}/file', 'BackgroundController@download');
    
    // Game Asset Downloads (Public)
    $router->get('game-pasangkan-huruf/{id}/asset/{assetType}', 'GamePasangkanHurufController@getAssetFile');
    $router->get('game-cari-hijaiyyah/{id}/asset/{assetType}', 'GameCariHijaiyyahController@getAssetFile');

    
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

        // Game Cari Hijaiyyah (User)
        $router->get('carihijaiyah/progress', 'CariHijaiyyahController@getProgress');
        $router->post('carihijaiyah/start', 'CariHijaiyyahController@startGame');
        $router->post('carihijaiyah/finish', 'CariHijaiyyahController@finishGame');
        $router->get('carihijaiyah/stats', 'CariHijaiyyahController@getStats');

        // Game Pasangkan Huruf (User)
        $router->get('pasangkanhuruf/progress', 'PasangkanHurufController@getProgress');
        $router->post('pasangkanhuruf/start', 'PasangkanHurufController@startGame');
        $router->post('pasangkanhuruf/finish', 'PasangkanHurufController@finishGame');
        $router->get('pasangkanhuruf/stats', 'PasangkanHurufController@getStats');

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