<?php

namespace App\Http\Controllers;

use App\Api;
use Illuminate\Http\Request;

class SevenController extends Controller
{
    public function reset()
    {
        $api = new Api(config('seven.api.user'), config('seven.api.token'));
        $day = self::resetTimeToAfterLastFrequency($api);

        $message = "Time rolled back to Day $day at 04:00";
        self::serverMessage($api, $message);
        return $message;
    }

    public function preferences()
    {
        $api = new Api(config('seven.api.user'), config('seven.api.token'));
        dd(self::gamePreferences($api));
    }

    public static function serverMessage(Api $api, string $string)
    {
        $api->execute("say \"$string\"");
    }

    public static function gamePreferences(Api $api): array
    {
        $response = $api->execute('getgamepref');

        $commands = explode("\n", $response);
        return collect($commands)
            ->map(fn ($name) => str_replace('GamePref.', '', $name))
            ->filter(fn ($name) => ! empty($name))
            ->map(fn ($name) => explode(' = ', $name))
            ->mapWithKeys(fn ($item, $key) => [$item[0] => $item[1]])
            ->all();
    }

    public static function resetTimeToAfterLastFrequency(Api $api): int
    {
        $settings = self::gamePreferences($api);
        $frequency = (int) $settings['BloodMoonFrequency'];

        $string = $api->execute('gettime');
        preg_match('/\w+ (\d+),/i', $string, $matches);
        [$stringMatch, $dayNumber] = $matches;

        $dayNumber = (int) $dayNumber;
        $daysToBe = $dayNumber - ($dayNumber - 1) % $frequency;

        $command = "settime $daysToBe 4 0";
        $api->execute($command);

        return $daysToBe;
    }
}
