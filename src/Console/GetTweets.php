<?php

namespace Mawdoo3\Tweets\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Mawdoo3\Tweets\Model\DataTweets;
use Mawdoo3\Tweets\Model\TweetSources;
use Twitter;


class GetTweets extends Command
{

    //get tweets every 10 request
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweets:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get last 200 tweets for each source';

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
        /*
         * get first tweet stored in my database and get the id and get last 200 tweets which newer from the last
         *
         */

        $path = base_path("public") . "/output";
        if (!file_exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }

        $handle = fopen($path . "/" . 'log-get-tweets.log', 'a+');
        fwrite($handle, "\n************************* - NEW GET Command : " . now() . "********************************\n");


        //get all sources stored in table tweet_sources
        $sources = TweetSources::select('user_id')->get();
        foreach ($sources as $source) {
            //get last tweet in table related with the user id

            $last_tweet = DataTweets::select('tweets_id')->where('user_id', $source['user_id'])->orderBy('created_at', 'desc')->first();
//            $since_id = $last_tweet['tweets_id'];

            if (!empty($last_tweet)) {
                $since_id = $last_tweet['tweets_id'];

                $data = Twitter::getUserTimeline(['user_id' => $source['user_id'], 'since_id' => $since_id, 'count' => 200, 'include_rts' => false, 'format' => 'array']);
            } else {
                $data = Twitter::getUserTimeline(['user_id' => $source['user_id'], 'count' => 200, 'include_rts' => false, 'format' => 'array']);

            }


            //$data = Twitter::getUserTimeline(['user_id' => $source['user_id'], 'since_id' => $since_id, 'count' => 200, 'include_rts' => false, 'format' => 'array']);
            if (isset($data)) {
                foreach ($data as $datum) {
                    DataTweets::create([
                        'tweets_id' => $datum['id'],
                        'user_id' => $datum['user']['id'],
                        'title' => $datum['text'],
                        'payload' => json_encode($datum),
                        'created_at' => strtotime($datum['created_at'])
                    ]);

                    if (isset($datum)) {
                        fwrite($handle, now() . " - Source Id : " . $source['user_id'] . "    tweet id :" . $datum['id'] . "\n");
                    }
                }
            } else {
                continue;
            }
        }
        fclose($handle);

        $this->info('done');
    }
}
