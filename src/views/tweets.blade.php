<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h1>Task 3 </h1>
            <div class="thumbnail">
                <p>use <b>thujohn</b> laravel package to get 1000 last tweets for some sources (users) </p>

                <p>
                    - <b>getUserTimeline() </b>- Returns a collection of the most recent Tweets posted by the user
                    indicated by the screen_name or user_id parameters.<br>
                    - <b>getStatusesLookup()</b> - Returns fully-hydrated tweet objects for up to 100 tweets per
                    request, as specified by comma-separated values passed to the id parameter.<br>


                </p>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="col-sm-12 bg-info">
                <h3>Instructions </h3>
                <form action="/import" method="post">
                    <input type="submit" name="submit" value="Get last 1000 tweets for all sources"/>

                </form>
                <div class="col-sm-12 dl-horizontal">
                    <div class="col-sm-12" style="height: 150px"></div>
                </div>
            </div>

            <div class="col-sm-12 bg-warning">
                <h3>Insert New Source </h3>

                <form action="/setSources" method="post">
                    New Source :<input type="text" name="source" id="source"/>
                    <input type="submit" name="submit" value="Insert"/>
                </form>
                <div class="col-sm-12 dl-horizontal">
                    <div class="col-sm-12" style="height: 150px"></div>
                </div>
            </div>

            <div class="col-sm-12 bg-danger">
                <h3>Show The Screen names for each Source </h3>

                <button id="getSources"> get All Sources</button>
                <div id="content"></div>

            </div>
            <div class="col-sm-12 bg-success">
                <h3>Show The similarity texts for deleted tweets and nearest tweets from same source</h3>
                <button id="getSimilarities"> get </button>
                <div class="col-sm-12"id="content-sim"></div>

            </div>

        </div>
    </div>
</div>

<br>


</div>

<script>
    $(document).ready(function () {
        $("#getSources").on('click', function () {
            getData();
        });
        $("#getSimilarities").on('click', function () {
            getDataSim();
        });

    });

    function getData() {
        $.ajax({
            type: "GET",
            url: '/getSources',
            dataType: "html",
            success: function (data) {
                // Call this function on success
                showData(data);

                return data;
            },
            error: function () {
                alert('Error occured');
            }
        });
    }

    function getDataSim() {
        alert();
        $.ajax({
            type: "GET",
            url: '/getSimilarities',
            dataType: "html",
            success: function (data) {
                // Call this function on success
                showDataSim(data);

                return data;
            },
            error: function () {
                alert('Error occured');
            }
        });

    }


    function showData(data) {
        //table = " <form method='post' action='/save'><table class='table table-bordered table-primary table-hover'><tr>" +"<th>title</th><th>link</th><th>description</th><th>comment</th><th>check</th></tr>";
        table = "<table class='table table-bordered  table-hover text-left bg-success'>" + "<tr>" + "<th></th><th>Source Name</th><th></th><th></th></tr>";
        data = JSON.parse(data);
        for (var i = 0; i < data.length; i++) {
            var item = data[i][0];

            table += "<tr><td>" + (i + 1) + "</td><td>" + "<div class=''><h4>" + item + "</h4>" + "<td><p class='badge'>"+data[i][1]+"  tweets</p> </td><td><form action='/getLastTweets' method='get'><input type='hidden' value='" + item + "' name='screen_name' /><button type='submit'   id='update-" + "'" + " class='btn btn-primary'>Get last Tweets</button></form></td>" + "<td><form action='/deleteSource' method='get'><input type='hidden' value='" + item + "' name='screen_name'><button type='submit'   id='delete-" + "'" + " class='btn btn-danger'>Delete</button></form></td>";
            table += " </tr>";
        }

        table += " <table>";
        document.getElementById("content").innerHTML = table;
    }

    function showDataSim(data) {
        document.getElementById("content-sim").innerHTML = data;

    }

</script>
</body>
</html>