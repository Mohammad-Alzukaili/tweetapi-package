<?php

namespace Mawdoo3\Tweets\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Mawdoo3\Tweets\Model\ArtisanCommands;


class CommandsController extends Controller
{
    function executeCommandsCheck()
    {
        $str = Artisan::call('tweets:check');
        return "output is : " . Artisan::output();
    }


    function executeCommandsGet()
    {
        Artisan::call('tweets:get');
        return "output is : " . Artisan::output();
    }

    function allowSceduling($command){
        $check = ArtisanCommands::where('command',$command)->get();
        if($check[0]['allow_scheduling']==true){
            return true;
        }else{
            return false;
        }

    }


    //command name , command id , command context
    function toggleAllowCommandToRun(Request $request)
    {
        $command_name = $request->input('command_name');
        $command_name = "tweets:get";
        $commands = ArtisanCommands::select('allow_scheduling')->where('command', $command_name)->get();


        if ($commands[0]['allow_scheduling'] == 0) {
            ArtisanCommands::where('command', $command_name)->update(['allow_scheduling' => true]);
        } else {
            ArtisanCommands::where('command', $command_name)->update(['allow_scheduling' => false]);
        }

        return redirect(route('tweets'));

    }

    function getCommandsSignatures()
    {
        $commands = ArtisanCommands::select('command', 'allow_scheduling')->get();

        return json_encode($commands);
    }

    function insertCommand(Request $request)
    {
        $command_name = $request->input('command_name');
        ArtisanCommands::create([
            'command' => $command_name
        ]);
        return redirect(route('tweets'));
    }

    function deleteCommand(Request $request)
    {
        $command_name = $request->input('command_name');
        ArtisanCommands::where('command', $command_name)->delete();
        return redirect(route('tweets'));

    }


}
