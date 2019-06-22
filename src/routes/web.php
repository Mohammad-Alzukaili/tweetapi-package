<?php
Route::group(['namespace'=>'Mawdoo3\Tweets\Http\Controllers'],function(){


    Route::get('/', function (){
        return view("tweets::tweets");
    })->name('tweets');

    Route::post('/import',"GetTweetsController@getUser");

    //set new  sources to database
    Route::get('/getSources',"TweetSourcesController@getSources");


    Route::post('/setSources',"TweetSourcesController@setSources");
    Route::get('/deleteSource',"TweetSourcesController@deleteSource");

    Route::get('/deleteAllTweets',"GetTweetsController@deleteAllTweets");

    Route::get('/show',"TweetSourcesController@getSources");


    Route::get('/check',"GetTweetsController@checkLastTweets");






    //get last tweetes routes for distinct source manually
    Route::get('/getLastTweets','GetTweetsController@getLastTweets');

    Route::get('/getSimilarities',"SimilaritiesController@getSimilarities");
    Route::get('/deleteAllSim',"SimilaritiesController@deleteAllSimilarities");

    //commands
    Route::get('/getCommand',"CommandsController@executeCommandsGet");
    Route::get('/checkCommand',"CommandsController@executeCommandsCheck");


    Route::get('/insertCommand',"CommandsController@insertCommand");
    Route::get('/getArtisanCommands',"CommandsController@getCommandsSignatures");
    Route::get('/allowScheduling',"CommandsController@toggleAllowCommandToRun");
    Route::get('/deleteCommand',"CommandsController@deleteCommand");





});

