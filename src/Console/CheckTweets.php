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
        // open file to log new check
        $path = base_path("public") . "/output";
        if (!file_exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        $handle = fopen($path . "/" . 'log-check-tweets.log', 'a+');
        fwrite($handle, "\n**********\nNew Check " . now() . " occured \n");

        //get all sources
        $sources = TweetSources::select('user_id')->pluck('user_id')->toArray();
        foreach ($sources as $source) {
            //get tweets up to 100 for this source for last hour
            $tweets = DataTweets::select('tweets_id')->where([['user_id', '=', $source], ['created_at', '>', Carbon::now()->subHour()]])->orderBy('created_at', 'desc')->take(100)->pluck('tweets_id')->toArray();

            if (isset($tweets)) {
                $tweets_ids = implode(',',$tweets);
                $tweets_objects = Twitter::getStatusesLookup(['id' => $tweets_ids]);

                //get objects ids store them in array
                $tweets_objects_ids = [];

                $count = 0;
                foreach ($tweets_objects as $tweets_object) {
                    $tweets_objects_ids[$count] = $tweets_object->id;
                    $count++;
                }

                fwrite($handle, "\n------ source id :" . $source . "\n");

                //check if deleted or not and flag them in database
                foreach ($tweets as $tweet) {

                    if (!in_array($tweet, $tweets_objects_ids)) {
                        //change flag to deleted (1)
                        $this->info($tweet . " : deleted\n");
                        DataTweets::where('tweets_id', $tweet)->update(['isDeleted' => 1]);

                        fwrite($handle, now() . " tweet id : " . $tweet . " -    : the tweet is 'deleted' " . " \n");
                    } else {
                        $this->info($tweet . " : not deleted\n");

                        fwrite($handle, now() . " tweet id : " . $tweet . " -    : the tweet is 'not deleted' " . " \n");
                    }
                }
            } else {
                continue;
            }
        }
        fclose($handle);

        //check similarity
        $ob = new GetTweetsController();
        $ob->checkLastTweets();
    }
}

