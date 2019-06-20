<?php

namespace Mawdoo3\Tweets\Http\Controllers;

use App\Http\Controllers\Controller;
use Mawdoo3\Tweets\Model\SimilarityTweets;
use Illuminate\Http\Request;
use Mawdoo3\Tweets\Model\TweetSources;

class SimilaritiesController extends Controller
{

    function getScreenNameFUserID($userId)
    {

        $screenName = TweetSources::select('screen_name')->where('user_id', '=', $userId)->pluck('user_id');

        return $screenName[0];

    }

    /**
     * retur html table of deleted tweets with information about similarities with other tweets
     *
     * @return string
     */
    function getSimilarities()
    {

        $rows = SimilarityTweets::select('user_id', 'tweet_id', 'similarity')->get();
        $table="";
        foreach ($rows as $row) {

            $table .= "<table class=' thumbnail table table-condensed table-bordered table-striped bg-info table-hover  '>";
            $table .= "<tr><th>num</th><th>User Name</th><th>Tweet ID (deleted)</th><th>Text</th><th>Information</th></tr>";
            $table .= "<tr><td>" . "" . "</td><td>" . $row['user_id'] . "</td><td>" . $row['tweet_id'] . "</td><td>";
            $content = "";

            $div = "<div class='col-sm-12 thumbnail bg-warning'>";
            $ldiv = "</div>";

            foreach (json_decode($row->similarity) as $key => $datum) {

                foreach ($datum as $item => $value) {
                    $text0 = $value->texts[0];
                    $text1 = $value->texts[1];
                    $texts = "<p class='text-right bg-info'>".$text0."</p>";

                    $content .= "<div class=''><table class='table table-condensed table-bordered table-striped'><tr><td>tweet id</td><td>" . $item . "</td></tr>";
                    $content .= "<tr><td>Similarity</td><td class='";
                    if($value->similarity[1]>85){
                        $content.="bg-success";
                    }elseif($value->similarity[1]<50){
                        $content.="bg-danger";
                    }else{
                        $content.="bg-warning";
                    }

                    $content.="'><span >percentage:" . $value->similarity[1] . "</span><br><br>Matching characters : " . $value->similarity[0] . "</td></tr>";
                    $content .= "<tr><td>lev</td><td><br>" . (100 - ((int)$value->lev)*100 /strlen($text1)). "%</td></tr>";
                    $content .= "<tr>$texts</tr></table></div>";

                }

            }

            $table .= "<h4 class='text-right'>".$text1."</h4></td><td>".$div .$content. $ldiv . "</td></tr>";
            $table .= "</table>";

        }
        return $table;


    }
}
