<?php

namespace Player\Service;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Stdlib\ParametersInterface;
use Laminas\Json\Json;

class SportsInfoApi
{
    private $client_id;
    private $client_secret;
    private $access_token;
    private $base_url = "https://api.sportsinfosolutions.com/api/v1/nfl/";

    /**
     * SportsInfoApi constructor.
     * @param $client_id
     * @param $client_secret
     */
    public function __construct($client_id, $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->authorize();
    }

    private function authorize()
    {
        $request = new Request();
        $uri = "https://auth.sportsinfosolutions.com/connect/token";
        $request->setUri($uri);
        $request->setMethod("POST");
        $request->getPost()->set('client_id', $this->client_id);
        $request->getPost()->set('client_secret', $this->client_secret);
        $request->getPost()->set('grant_type', 'client_credentials');
        $request->getPost()->set('scope', 'sisapi');
        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();
        $json = Json::decode($html,1);
        $this->access_token = $json['access_token'];
    }

    public function makeRequest($endpoint)
    {
        $request = new Request();
        $uri = $this->base_url.$endpoint;
        $request->setUri($uri);
        $headers = $request->getHeaders()->addHeaders([
            "Authorization" => "Bearer ".$this->access_token]);
        $request->setHeaders($headers);
        return $request;
    }

    public function doRequest($request)
    {
        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();
        if (empty($html)) {
            return [];
        } else {
            $json = Json::decode($html,1);
            return $json;
        }
    }

    public function getTeams()
    {
        $request = $this->makeRequest('seasons/2020/teams');
        $result = $this->doRequest($request);
        return $result['data'];
    }

    public function getSchedule($year)
    {
        $request = $this->makeRequest("/schedule/season/{$year}");
        $result = $this->doRequest($request);
        return $result;
    }

    public function getPlayers()
    {
        $request = $this->makeRequest("seasons/2020/players");
        return $this->doRequest($request);
    }

    public function getPlayersQuery($year="2020", $type="", $params=[])
    {
        $defaultArray = [
            'EntityType' => '1',
            'MetricGroup' => '3',
            'MetricGroupSubType' => '1',
            'GameFilters.Conference' => '-1',
            'GameFilters.vsConference' => '-1',
            'GameFilters.vsDivision' => '-1',
            'GameFilters.vsTeam' => '-1',
            'GameFilters.HomeAway' => '-1',
            'GameFilters.IndoorOutdoor' => '-1',
            'GameFilters.Team' => '-1',
            'TimeFilters.SeasonFrom' => '2020',
            'TimeFilters.SeasonTo' => '2020',
            'TimeFilters.StartWeek' => '1',
            'TimeFilters.EndWeek' => '17',
            'SituationFilters.Quarters' => '0',
            'SituationFilters.Downs' => '0',
            'SituationFilters.FieldPositionFromSide' => '-1',
            'SituationFilters.FieldPositionFromYd' => '',
            'SituationFilters.FieldPositionToSide' => '-1',
            'SituationFilters.FieldPositionToYd' => '',
            'SituationFilters.TimeRemainingFrom' => '0',
            'SituationFilters.TimeRemainingTo' => '900',
            'SituationFilters.DistanceFrom' => '0',
            'SituationFilters.DistanceTo' => '100',
            'SituationFilters.ScoreDiffFrom' => '-50',
            'SituationFilters.ScoreDiffTo' => '50',
            'PersonnelFilters.WRs' => '-1',
            'PersonnelFilters.WRFormation' => '-1',
            'PersonnelFilters.TEs' => '-1',
            'PersonnelFilters.DLs' => '-1',
            'PersonnelFilters.DBs' => '-1',
            'PersonnelFilters.Shotgun' => '-1',
            'PersonnelFilters.RBs' => '-1',
            'PersonnelFilters.RBFormation' => '-1',
            'PersonnelFilters.OLs' => '-1',
            'PersonnelFilters.LBs' => '-1',
            'PersonnelFilters.NoHuddle' => '-1',
            'PassingFilters.PlayAction' => '-1',
            'PassingFilters.Bootleg' => '-1',
            'PassingFilters.Pressured' => '-1',
            'PassingFilters.InPocket' => '-1',
            'PassingFilters.ThrowType' => '0',
            'PassingFilters.TargetLinedUp' => '0',
            'PassingFilters.PassDir' => '0',
            'PassingFilters.TargetPos' => '0',
            'PassingFilters.Schemes' => '0',
            'PassingFilters.RouteType' => '0',
            'PassingFilters.DropType' => '0',
            'PassingFilters.MinAirYards' => '-20',
            'PassingFilters.MaxAirYards' => '100',
            'RushingFilters.RunType' => '0',
            'RushingFilters.RunDirection' => '0',
            'RushingFilters.BlockScheme' => '0',
            'RushingFilters.BallCarrierPos' => '0',
            'RushingFilters.DefInBox' => '0',
            'ReceivingFilters.InMotion' => '-1',
            'ReceivingFilters.Catchable' => '-1',
            'ReceivingFilters.TargetLinedUp' => '0',
            'ReceivingFilters.PassDir' => '0',
            'ReceivingFilters.TargetPos' => '0',
            'ReceivingFilters.Schemes' => '0',
            'ReceivingFilters.RecAlignment' => '0',
            'ReceivingFilters.RouteType' => '0',
            'ReceivingFilters.MinAirYards' => '-20',
            'ReceivingFilters.MaxAirYards' => '100',
            'RunDefenseFilters.RunType' => '0',
            'RunDefenseFilters.RunDirection' => '0',
            'RunDefenseFilters.BlockScheme' => '0',
            'RunDefenseFilters.DefenderPos' => '0',
            'RunDefenseFilters.DefInBox' => '0',
            'PassDefenseFilters.RouteType' => '0',
            'PassDefenseFilters.DefenderPos' => '0',
            'PassDefenseFilters.Schemes' => '0',
            'PassDefenseFilters.PassDir' => '0',
            'PassDefenseFilters.TargetLinedUp' => '0',
            'PassDefenseFilters.ReceiverPos' => '0',
            'PassDefenseFilters.MinAirYards' => '-20',
            'PassDefenseFilters.MaxAirYards' => '100',
            'PassRushFilters.Schemes' => '0',
            'PassRushFilters.DefenderPos' => '0',
            'PuntingFilters.PuntType' => '0',
            'KickingFilters.MinFGYards' => '0',
            'KickingFilters.MaxFGYards' => '65',
            'BlockFilters.PlayAction' => '-1',
            'BlockFilters.Bootleg' => '-1',
            'BlockFilters.InPocket' => '-1',
            'BlockFilters.RunDirection' => '0',
            'BlockFilters.BlockScheme' => '0',
            'BlockFilters.DefInBox' => '0',
            'BlockFilters.PassRushers' => '0',
            'BlockFilters.BlockingPosition' => '0',
            'AdjustedBB.TeamId' => '-1',
            'AdjustedBB.BlockingPosition' => '0',
            'PassingFilters.MinAttempts' => '1',
            'RushingFilters.MinCarries' => '5',
            'ReceivingFilters.MinTargets' => '1',
            'PassDefenseFilters.MinTargets' => '1',
            'PassDefenseFilters.MinAttempts' => '1',
            'RunDefenseFilters.MinTackles' => '1',
            'RunDefenseFilters.MinCarries' => '1',
            'PassRushFilters.MinPassRushes' => '1',
            'PassRushFilters.MinPressures' => '1',
            'ReturnFIlters.MinKR' => '1',
            'ReturnFilters.MinPR' => '1',
            'PuntingFilters.MinPunt' => '1',
            'KickingFilters.MinFG' => '1',
            'BlockFilters.MinSnaps' => '10',
            'AdjustedBB.MinPassSnaps' => '1',
        ];

        /**
         * MetricGroups:(1=passing, 5=receiving, 3=rushing, 14=offensive line, 9=passDef, 10=passRush, 11=runDef, 12=returning)
         */
        $metricGroup = [
            "passing" => 1,
            "rushing" => 3,
            "receiving" => 5,
            "blocking" => 14,
            "passDef" => 9,
            "passRush" => 10,
            "runDef" => 11,
            "return" => 12
        ];

        if (!empty($params)) {
            $defaultArray = array_merge($defaultArray, $params);
        }

        $defaultArray['MetricGroup'] = $metricGroup[$type];
        $defaultArray['TimeFilters.SeasonFrom'] = $year;
        $defaultArray['TimeFilters.SeasonTo'] = $year;
        $defaultArray['Result'] = "starts";

        $request = $this->makeRequest('players/query');
        foreach ($defaultArray as $query => $value) {
            $request->getPost()->set($query, $value);
        }

        $request->setMethod(Request::METHOD_POST);
        $response = $this->doRequest($request);
        return $response['data'];
    }

    public function getTeamsQuery()
    {
        /**
         * MetricGroups:(1=passing, 5=receiving, 3=rushing, 14=offensive line, 9=passDef, 10=passRush, 11=runDef, 12=returning)
         */
        $defaultQuery = [
            "EntityType" => 1,
            "MetricGroup" => 3,
            "MetricGroupSubType" => 1,
            "GameFilters.vsConference" => -1,
            "GameFilters.vsDivision" => -1,
            "GameFilters.vsTeam" => -1,
            "GameFilters.HomeAway" => -1,
            "GameFilters.IndoorOutdoor" => -1,
            "GameFilters.Team" => -1,
            "TimeFilters.SeasonFrom" => 2019,
            "TimeFilters.SeasonTo" => 2019,
            "TimeFilters.StartWeek" => 1,
            "TimeFilters.EndWeek" => 17,
            "SituationFilters.Quarters" => 0,
            "SituationFilters.Downs" => 0,
            "SituationFilters.FieldPositionFromSide" => -1,
            "SituationFilters.FieldPositionFromYd" => "",
            "SituationFilters.FieldPositionToSide" => -1,
            "SituationFilters.FieldPositionToYd" => "",
            "SituationFilters.TimeRemainingFrom" => 0,
            "SituationFilters.TimeRemainingTo" => 900,
            "SituationFilters.DistanceFrom" => 0,
            "SituationFilters.DistanceTo" => 100,
            "SituationFilters.ScoreDiffFrom" => -50,
            "SituationFilters.ScoreDiffTo" => 50,
            "PersonnelFilters.WRs" => -1,
            "PersonnelFilters.WRFormation" => -1,
            "PersonnelFilters.TEs" => -1,
            "PersonnelFilters.DLs" => -1,
            "PersonnelFilters.DBs" => -1,
            "PersonnelFilters.Shotgun" => -1,
            "PersonnelFilters.RBs" => -1,
            "PersonnelFilters.RBFormation" => -1,
            "PersonnelFilters.OLs" => -1,
            "PersonnelFilters.LBs" => -1,
            "PersonnelFilters.NoHuddle" => -1,
            "PassingFilters.PlayAction" => -1,
            "PassingFilters.Bootleg" => -1,
            "PassingFilters.Pressured" => -1,
            "PassingFilters.InPocket" => -1,
            "PassingFilters.ThrowType" => 0,
            "PassingFilters.TargetLinedUp" => 0,
            "PassingFilters.PassDir" => 0,
            "PassingFilters.TargetPos" => 0,
            "PassingFilters.Schemes" => 0,
            "PassingFilters.RouteType" => 0,
            "PassingFilters.DropType" => 0,
            "PassingFilters.MinAirYards" => -20,
            "PassingFilters.MaxAirYards" => 100,
            "RushingFilters.RunType" => 0,
            "RushingFilters.RunDirection" => 0,
            "RushingFilters.BlockScheme" => 0,
            "RushingFilters.BallCarrierPos" => 0,
            "RushingFilters.DefInBox" => 0,
            "ReceivingFilters.InMotion" => -1,
            "ReceivingFilters.Catchable" => -1,
            "ReceivingFilters.TargetLinedUp" => 0,
            "ReceivingFilters.PassDir" => 0,
            "ReceivingFilters.TargetPos" => 0,
            "ReceivingFilters.Schemes" => 0,
            "ReceivingFilters.RecAlignment" => 0,
            "ReceivingFilters.RouteType" => 0,
            "ReceivingFilters.MinAirYards" => -20,
            "ReceivingFilters.MaxAirYards" => 100,
            "RunDefenseFilters.RunType" => 0,
            "RunDefenseFilters.RunDirection" => 0,
            "RunDefenseFilters.BlockScheme" => 0,
            "RunDefenseFilters.DefenderPos" => 0,
            "RunDefenseFilters.DefInBox" => 0,
            "PassDefenseFilters.RouteType" => 0,
            "PassDefenseFilters.DefenderPos" => 0,
            "PassDefenseFilters.Schemes" => 0,
            "PassDefenseFilters.PassDir" => 0,
            "PassDefenseFilters.TargetLinedUp" => 0,
            "PassDefenseFilters.ReceiverPos" => 0,
            "PassDefenseFilters.MinAirYards" => -20,
            "PassDefenseFilters.MaxAirYards" => 100,
            "PassRushFilters.Schemes" => 0,
            "PassRushFilters.DefenderPos" => 0,
            "PuntingFilters.PuntType" => 0,
            "KickingFilters.MinFGYards" => 0,
            "KickingFilters.MaxFGYards" => 65,
            "BlockFilters.PlayAction" => -1,
            "BlockFilters.Bootleg" => -1,
            "BlockFilters.InPocket" => -1,
            "BlockFilters.RunDirection" => 0,
            "BlockFilters.BlockScheme" => 0,
            "BlockFilters.DefInBox" => 0,
            "BlockFilters.PassRushers" => 0,
            "BlockFilters.BlockingPosition" => 0,
            "AdjustedBB.TeamId" => -1,
            "AdjustedBB.BlockingPosition" => 0,
            "PassingFilters.MinAttempts" => 1,
            "RushingFilters.MinCarries" => 5,
            "ReceivingFilters.MinTargets" => 1,
            "PassDefenseFilters.MinTargets" => 1,
            "PassDefenseFilters.MinAttempts" => 1,
            "RunDefenseFilters.MinTackles" => 1,
            "RunDefenseFilters.MinCarries" => 1,
            "PassRushFilters.MinPassRushes" => 1,
            "PassRushFilters.MinPressures" => 1,
            "ReturnFIlters.MinKR" => 1,
            "ReturnFilters.MinPR" => 1,
            "PuntingFilters.MinPunt" => 1,
            "KickingFilters.MinFG" => 1,
            "BlockFilters.MinSnaps" => 10,
            "AdjustedBB.MinPassSnaps" => 1
        ];

        $request = $this->makeRequest('teams/12/1/plays/3');
        foreach ($defaultQuery as $query => $value) {
            $request->getPost()->set($query, $value);
        }
        $request->setMethod('POST');
        return $this->doRequest($request);
    }

    public function getSeasonStats($playerId, $year, $type)
    {
        /*
         *                             "gp" => $stats['passing']['g'],
                            "gs" => $stats['passing']['starts'],
                            "td" => $stats['passing']['td'] + $stats['passing']['td'],
                            "pass_att" => $stats['passing']['attempts'],
                            "pass_cmp" => $stats['passing']['comp'],
                            "pass_int" => $stats['passing']['int'],
                            "pass_rtg" => $stats['passing']['qbRating'],
                            "pass_lng" => $stats['passing']['passLong'],
                            "pass_ypa" => $stats['passing']['yardsPerAtt'],
                            "pass_ypc" => $stats['passing']['yardsPerComp'],
                            "pass_td" => $stats['passing']['td'],
         */


        $emptyRushing = [
            "g" => 0,
            "starts" => 0,
            "att" => 0,
            "yards" => 0,
            "int" => 0,
            "qbRating" => 0,
            "passLong" => 0,
            "yardsPerAtt" => 0,
            "yardsPerComp" => 0,
            "td" => 0,
            "long" => 0,
            "firstDown" => 0
        ];

        $emptyReceiving = [
            "g" => 0,
            "starts" => 0,
            "recs" => 0,
            "targets" => 0,
            "yards" => 0,
            "yardsPerRec" => 0,
            "yardsPerTarget" => 0,
            "yardsPerAtt" => 0,
            "yardsPerComp" => 0,
            "td" => 0,
            "firstDowns" => 0,
            "long" => 0,
        ];


        switch ($type) {
            case "QB":
                $request = $this->makeRequest("seasons/{$year}/players/{$playerId}/passing");
                $passing = $this->doRequest($request);
                $request = $this->makeRequest("seasons/{$year}/players/{$playerId}/rushing");
                $rushing = $this->doRequest($request);

                $result['passing'] = (empty($passing['data']) || empty($passing['data'][0]))? [] : $passing['data'][0];
                if (empty($result['passing'])) {
                   $result['passing'] = $passing;
                }
                $result['rushing'] = (empty($rushing['data']))? [] : $rushing['data'][0];
                if (empty($result['rushing'])) {
                   $result['rushing'] = $emptyRushing;
                }

                break;
            case "RB":
            case "WR":
            case "TE":
                $request = $this->makeRequest("seasons/{$year}/players/{$playerId}/rushing");
                $rushing = $this->doRequest($request);
                $request = $this->makeRequest("seasons/{$year}/players/{$playerId}/receiving");
                $receiving = $this->doRequest($request);

                $result['rushing'] = (empty($rushing['data']))? [] : $rushing['data'][0];
                if (empty($result['rushing'])) {
                    $result['rushing'] = $emptyRushing;
                }
                $result['receiving'] = (empty($receiving['data']))? [] : $receiving['data'][0];
                if (empty($result['receiving'])) {
                    $result['receiving'] = $emptyReceiving;
                }
                break;
            case "C":
            case "T":
            case "G":
                $request = $this->makeRequest("seasons/{$year}/players/{$playerId}/blocking");
                $blocking = $this->doRequest($request);

                $result['blocking'] = (empty($blocking))? [] : $blocking['data'][0] ;
                break;
            case "LB":
            case "CB":
            case "DE":
            case "RE":
            case "LE":
            case "DT":
            case "FS":
            case "SS":
                $request = $this->makeRequest("seasons/{$year}/players/{$playerId}/defense");
                $defense = $this->doRequest($request);
                $result['defense'] = (empty($defense['data']))? [] : $defense['data'][0];
                break;
            default:
                $request = $this->makeRequest("season/{$year}/weeks/players/{$playerId}");
                $result = $this->doRequest($request);
        }
        return $result;
    }

    public function getLeaders($year, $type) {
        $request = $this->makeRequest("seasons/2019/leaders/rushing");
        return $this->doRequest($request);
    }


}