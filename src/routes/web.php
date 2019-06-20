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

    Route::get('/show',"TweetSourcesController@getSources");
    Route::get('/check',"GetTweetsController@checkLastTweets");

    //get last tweetes routes for distinct source manually
    Route::get('/getLastTweets','GetTweetsController@getLastTweets');

    Route::get('/getSimilarities',"SimilaritiesController@getSimilarities");


});

