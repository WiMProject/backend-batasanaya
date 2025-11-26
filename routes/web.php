<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', 'HomeController@index');

// Documentation route
$router->get('/docs', 'DocController@index');

// Admin routes
$router->get('/admin/login', 'AdminLoginController@index');
$router->get('/admin', 'AdminDashboardController@index');
$router->get('/api/admin/stats', ['middleware' => ['auth', 'role:admin'], 'uses' => 'AdminDashboardController@getStats']);

// Grup untuk API
$router->group(['prefix' => 'api'], function () use ($router) {
    
    // Rute Publik (Auth)
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('register', 'AuthController@register');
        $router->post('login', 'AuthController@login');
        $router->post('admin-login', 'AuthController@adminLogin');
        $router->post('google', 'AuthController@loginWithGoogle');
        $router->post('refresh', 'AuthController@refresh');
    });
    
    // --- Rute Download Asset Publik ---
    $router->get('assets/{id}/file', 'AssetController@download');
    $router->get('assets/download-all', 'AssetController@downloadAll');
    $router->get('assets/manifest', 'AssetController@manifest');
    $router->post('assets/download-batch', 'AssetController@downloadBatch');
    
    // --- Rute Download Background Publik ---
    $router->get('backgrounds/{id}/file', 'BackgroundController@download');
    
    // --- Rute Download Game Assets Publik ---
    $router->get('game-pasangkan-huruf/{id}/asset/{assetType}', 'GamePasangkanHurufController@getAssetFile');
    $router->get('game-cari-hijaiyyah/{id}/asset/{assetType}', 'GameCariHijaiyyahController@getAssetFile');

    // Rute yang membutuhkan otentikasi (login)
    $router->group(['middleware' => 'auth'], function () use ($router) {
        
        // Grup Auth yang terproteksi
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('reset-password', 'AuthController@resetPassword');
            $router->post('set-pin', 'AuthController@setPin');
            $router->post('verify-pin', 'AuthController@verifyPin');
            $router->post('request-otp', 'AuthController@requestOtp');
            $router->post('verify-otp', 'AuthController@verifyOtp');
            $router->post('logout', 'AuthController@logout');
            $router->get('me', 'AuthController@getMe');
        });

        // --- Rute Manajemen User --- 
        $router->post('users', ['middleware' => 'role:admin', 'uses' => 'UserController@store']);
        $router->get('users/{id}', 'UserController@show');
        $router->patch('users/{id}', 'UserController@update');
        $router->delete('users/{id}', ['middleware' => 'role:admin', 'uses' => 'UserController@destroy']);
        $router->post('user/profile-picture', 'UserController@uploadProfilePicture');

        // --- Rute Manajemen Asset ---
        $router->post('assets', 'AssetController@store');
        $router->post('assets/batch', 'AssetController@storeBatch');
        $router->get('assets', 'AssetController@index');
        $router->patch('assets/{id}', 'AssetController@update');
        $router->delete('assets/batch', 'AssetController@destroyBatch');
        $router->delete('assets/{id}', 'AssetController@destroy');

        // --- Rute User Preference ---
        $router->get('user/preference', 'PreferenceController@show');
        $router->patch('user/preference', 'PreferenceController@update');

        // --- Rute Manajemen Lagu ---
        $router->post('songs', 'SongController@store');
        $router->get('songs', 'SongController@index');
        $router->get('songs/{id}', 'SongController@show'); 
        $router->post('songs/{id}', 'SongController@update'); 
        $router->get('songs/{id}/file', 'SongController@stream');
        $router->delete('songs/{id}', 'SongController@destroy');

        // --- Rute Manajemen Video ---
        $router->post('videos', 'VideoController@store');
        $router->get('videos', 'VideoController@index');
        $router->get('videos/{id}', 'VideoController@show');
        $router->get('videos/{id}/file', 'VideoController@stream');
        $router->delete('videos/{id}', 'VideoController@destroy');

        // --- Rute Manajemen Background ---
        $router->post('backgrounds', 'BackgroundController@store');
        $router->get('backgrounds', 'BackgroundController@index');
        $router->get('backgrounds/{id}', 'BackgroundController@show');
        $router->patch('backgrounds/{id}', 'BackgroundController@update');
        $router->delete('backgrounds/{id}', 'BackgroundController@destroy');

        // --- Rute Game Cari Hijaiyyah ---
        $router->get('carihijaiyah/progress', 'CariHijaiyyahController@getProgress');
        $router->post('carihijaiyah/start', 'CariHijaiyyahController@startGame');
        $router->post('carihijaiyah/finish', 'CariHijaiyyahController@finishGame');
        $router->get('carihijaiyah/stats', 'CariHijaiyyahController@getStats');

        // --- Rute Game Pasangkan Huruf ---
        $router->get('pasangkanhuruf/progress', 'PasangkanHurufController@getProgress');
        $router->post('pasangkanhuruf/start', 'PasangkanHurufController@startGame');
        $router->post('pasangkanhuruf/finish', 'PasangkanHurufController@finishGame');
        $router->get('pasangkanhuruf/stats', 'PasangkanHurufController@getStats');

        // --- Rute Admin Only ---
        $router->group(['middleware' => 'role:admin', 'prefix' => 'admin'], function () use ($router) {
            $router->get('dashboard', 'AdminController@dashboard');
            $router->get('users', 'AdminController@getAllUsers');
            $router->get('users/{userId}/game-progress', 'AdminController@getUserGameProgress');
            $router->get('assets', 'AdminController@getAllAssets');
            $router->delete('assets/bulk', 'AdminController@bulkDeleteAssets');
        });
    });
});