<?php

namespace Mawdoo3\Tweets\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Mawdoo3\Tweets\Model\DataTweets;
use Mawdoo3\Tweets\Model\SimilarityTweets;
use Mawdoo3\Tweets\Model\TweetSources;
use Illuminate\Http\Request;
use Twitter;


class GetTweetsController extends Controller
{
    function getUser()
    {
        //first code to get 1000 tweets for one
        $sources = TweetSources::select('user_id')->get();
        foreach ($sources as $source) {
            $datum = Twitter::getUserTimeline(['user_id' => $source['user_id'], 'count' => 1, 'include_rts' => false, 'format' => 'array']);
            $max_id = $datum[0]['id'] + 10;
            for ($i = 0; $i < 5; $i++) {
                $data = Twitter::getUserTimeline(['user_id' => $source['user_id'], 'max_id' => $max_id, 'count' => 200, 'include_rts' => false, 'format' => 'array']);

                foreach ($data as $datum) {
                    DataTweets::create([
                        'tweets_id' => $datum['id'],
                        'user_id' => $datum['user']['id'],
                        'title' => $datum['text'],
                        'payload' => json_encode($datum),
                        'created_at' => strtotime($datum['created_at'])
                    ]);
                }
                $max_id = $datum['id'];
            }
        }

        //get number of rows inside database table tweets_data
        $tweets = DataTweets::all();
        $tweetCount = $tweets->count();

        //  $completion = "successfully" . "<br> num of rows inside tweets_data table is" . $tweetCount . "<br>";
        return view("tweets::tweets");

    }


    function checkLastTweets()
    {
        //start log
        $path = base_path("public") . "/output";
        if (!file_exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        $handle = fopen($path . "/" . 'log-check-tweets.log', 'a+');
        fwrite($handle, "\n-------- check similarities  ----- \n");




        $sources = TweetSources::all()->pluck('user_id')->toArray();
        foreach ($sources as $user_id) {

            $deleted_tweets = DataTweets::select('tweets_id', 'created_at', 'title')->where([['user_id', '=', $user_id], ['isDeleted', 0], ['checked',false], ['created_at', '>', Carbon::now()->subHours(5)]])->orderBy('created_at', 'desc')->get();
            //dd($deleted_tweets);
            $sim_arr = [];

            foreach ($deleted_tweets as $deleted_tweet) {

                if ($deleted_tweet['checked'] == 0) {

                    //get nearest 2 tweets  before  deleted tweet
                    $tweets_before_deleted = DataTweets::select('tweets_id', 'title', 'created_at')->where([['user_id', '=', $user_id], ['created_at', '>', Carbon::createFromTimestamp(strtotime($deleted_tweet['created_at']))->subHours(1)], ['created_at', '<', Carbon::createFromTimestamp(strtotime($deleted_tweet['created_at']))], ['isDeleted', 0]])->orderBy('created_at', 'desc')->take(2)->get();

                    //get nearest 2 tweets after of deleted tweet
                    $tweets_after_deleted = DataTweets::select('tweets_id', 'title', 'created_at')->where([['user_id', '=', $user_id], ['created_at', '<', Carbon::createFromTimestamp(strtotime($deleted_tweet['created_at']))->addHours(1)], ['created_at', '>', Carbon::createFromTimestamp(strtotime($deleted_tweet['created_at']))], ['isDeleted', 0]])->orderBy('created_at', 'asc')->take(2)->get();


                    //check similiarity of tweets for two before nearest tweets in date
                    $bef_sim_arr = [];
                    foreach ($tweets_before_deleted as $obj) {
                        fwrite($handle, "tweet id :  ----- " . $deleted_tweet['tweets_id'] . "\n");

                        //similar_text : return num of mathcing characters
                        $sim = similar_text($obj['title'], $deleted_tweet['title'], $per);

                        //levenishtin : return how many of characters to be replaced,insserted and deleted to convert str1 to str2
                        $leven = levenshtein($obj['title'], $deleted_tweet['title']);

                        $bef_sim_arr[$obj['tweets_id']] = ['texts' => [$obj['title'], $deleted_tweet['title']], 'similarity' => [$sim, $per . '%'], 'lev' => $leven];
                    }


                    //check similiarity of texts for two nearest tweets in date
                    $aft_sim_arr = [];
                    foreach ($tweets_after_deleted as $obj) {

                        //similar_text : return num of mathcing characters
                        $sim = similar_text($obj['title'], $deleted_tweet['title'], $per);

                        //levenishtin : return how many of characters to be replaced,insserted and deleted to convert str1 to str2
                        $leven = levenshtein($obj['title'], $deleted_tweet['title']);

                        $aft_sim_arr[$obj['tweets_id']] = ['texts' => [$obj['title'], $deleted_tweet['title']], 'similarity' => [$sim, $per . '%'], 'lev' => $leven];
                    }

                    $sim_arr[$deleted_tweet['tweets_id']] = ["before" => $bef_sim_arr, "after" => $aft_sim_arr];
                    fwrite($handle, "----created similarites \n");

                    //store on simularities table
                    SimilarityTweets::create([
                        'user_id' => $user_id,
                        'tweet_id' => $deleted_tweet['tweets_id'],
                        'similarity' => json_encode($sim_arr[$deleted_tweet['tweets_id']]),
                        'checked' => 1
                    ]);

                    DataTweets::where('tweets_id',$deleted_tweet['tweets_id'])->update(['checked'=>1]);
                }

            }
        }
        fclose($handle);

        return redirect(route('tweets'));


    }








    //test function to get last tweets from one source
    function getLastTweets(Request $request)
    {
        $source_screenName = $request->input('screen_name');
        $source_id = $this->getUserIDFScreenName($source_screenName);
        $last_tweet = DataTweets::select('tweets_id')->where('user_id', $source_id)->orderBy('created_at', 'desc')->first();

        if (!empty($last_tweet)) {
            $since_id = $last_tweet['tweets_id'];

            $data = Twitter::getUserTimeline(['user_id' => $source_id, 'since_id' => $since_id, 'count' => 200, 'include_rts' => false, 'format' => 'array']);
        } else {
            $data = Twitter::getUserTimeline(['user_id' => $source_id, 'count' => 200, 'include_rts' => false, 'format' => 'array']);

        }

        //register a log file
        $path = base_path("public") . "/output";
        if (!file_exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        $handle = fopen($path . "/" . 'log-get_last-tweets.log', 'a+');
        fwrite($handle, now() . " ********************\n source id : " . $source_id . " / screen name : " . $source_screenName . "\n***************\n");

        foreach ($data as $datum) {
            DataTweets::create([
                'tweets_id' => $datum['id'],
                'user_id' => $datum['user']['id'],
                'title' => $datum['text'],
                'payload' => json_encode($datum),
                'created_at' => strtotime($datum['created_at'])
            ]);
            fwrite($handle, " ---- INSERT tweet id :" . $datum['id'] . "  \n");
        }
        fclose($handle);

        return redirect(route('tweets'));
    }


    function getUserIDFScreenName($screenName)
    {
        $userID = TweetSources::select('user_id')->where('screen_name', '=', $screenName)->pluck('user_id');
        return $userID[0];

    }




    function collectDeletedTweets($hours){
        $tweets = DataTweets::select('tweets_id', 'title')->where([['created_at', '>', Carbon::now()->subHours($hours)],['isDeleted',1]])->orderBy('created_at', 'desc')->get();
        dd($tweets);

    }


    function deleteAllTweets(Request $request){
        $source_screen_name = $request->input('screen_name');

        DataTweets::where('user_id',$this->getUserIDFScreenName($source_screen_name))->delete();
        return redirect(route('tweets'));

    }



}
