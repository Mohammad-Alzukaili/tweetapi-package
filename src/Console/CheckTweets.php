<?php

namespace Mawdoo3\Tweets\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Mawdoo3\Tweets\Http\Controllers\GetTweetsController;
use Mawdoo3\Tweets\Model\TweetSources;
use Mawdoo3\Tweets\Model\DataTweets;
use Mawdoo3\Tweets\Model\SimilarityTweets;
use Mawdoo3\Tweets\Http\Controllers\SimilaritiesController;


use Twitter;

class CheckTweets extends Command
{

    //get tweets every 10 request
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweets:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'to check last 100 tweets for all sources if deleted or not';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //todo: check logs with laravel and replace it with below

        // open file to log new check

        //get all sources

        $array = [];
        $counterForPredictedToDelete = 0;
        $subHours = 1;

        $sources = TweetSources::select('user_id')->pluck('user_id')->toArray();


        foreach ($sources as $source) {

            $tweets = DataTweets::select('tweets_id', 'title')->where([['user_id', '=', $source], ['created_at', '>', Carbon::now()->subHours($subHours)]])->orderBy('created_at', 'desc')->get();

            if (isset($tweets)) {
                //todo: check similar tweets
                // for each tweets as tweet : check if it any similar with
                for ($i = 0; $i < count($tweets); $i++) {
                    $tweet = $tweets[$i];

                    for ($j = $i; $j < count($tweets); $j++) {
                        $another_tweet = $tweets[$j];


                        if ($tweet['tweets_id'] == $another_tweet['tweets_id'] || $tweet['checked'] == 1) {
                            continue;
                        }

                        $sim = similar_text($tweet['title'], $another_tweet['title'], $perc);
                        if ($perc > 90) {
                            //set checked flag
                            //DataTweets::where('tweets_id', $another_tweet['tweets_id'])->update(['checked' => 1]);
                            array_push($array, $another_tweet['tweets_id']);
                            $counterForPredictedToDelete++;
                        }
                    }
                }
            }
        }


        //collect all ids in array of sizes 100
        $newArray = array_chunk($array, 100);
        $counterForDeletedTweets = 0; //to count the num of tweets was deleted from twitter

        //todo: send lookup requests to check list
        foreach ($newArray as $arr) {
            $tweets_objects = Twitter::getStatusesLookup(['id' => implode(',', $arr)]);
            $count = 0;
            $tweets_objects_ids=[];
            foreach ($tweets_objects as $tweets_object) {
                $tweets_objects_ids[$count] = $tweets_object->id;
                $count++;
            }

            foreach ($arr as $id) {
                if (!in_array($id, $tweets_objects_ids)) {
                    //DataTweets::where('tweets_id', $id)->update(['isDeleted' => 1]);
                    $counterForDeletedTweets++;
                }
            }


       //show deleted tweets

        }
        $ob = new GetTweetsController();
        if($counterForPredictedToDelete !=0 ) {
            $this->info("prediction success :" . ($counterForDeletedTweets / $counterForPredictedToDelete * 100) . "%");
        }else{
            $this->info("no Predicted tweets to be duplicated");
        }

    }
}

