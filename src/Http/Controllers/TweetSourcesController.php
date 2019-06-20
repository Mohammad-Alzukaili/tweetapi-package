<?php

namespace Mawdoo3\Tweets\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Mawdoo3\Tweets\Model\DataTweets;
use Mawdoo3\Tweets\Model\TweetSources;

use Twitter;

class TweetSourcesController extends Controller
{

    //show all sources on view which stored in database
    function getSources()
    {
        $sources = TweetSources::all();
        if ($sources->count() == 0) {
            return view("tweets::tweets");
        }
        $array = [];
        for ($i = 0; $i < count($sources); $i++) {
            $count = DataTweets::where('user_id',$sources[$i]['user_id'])->count();
            $array[$i] = [$sources[$i]['screen_name'],$count];
        }

        return json_encode($array);
    }


    function setSources(Request $request)
    {
        //insert new sources into database
        $source = trim($request->input('source'));
        try {
            //get user id from
            $user_data = Twitter::getUsers(['screen_name' => $source]);
            $user_id = $user_data->id;
            TweetSources::create([
                'user_id' => $user_id,
                'screen_name' => $source,
                'user_data' =>json_encode($user_data)
            ]);

        } catch (\RuntimeException $ex) {
            //return redirect(route("tweets"));
            dd($ex->getMessage());
        }
        return redirect(route("tweets"));
    }




    function deleteSource(Request $request)
    {
        $screen_name = $request->input('screen_name');

        TweetSources::where('screen_name', $screen_name)->delete();

        return redirect(route("tweets"));

    }
}