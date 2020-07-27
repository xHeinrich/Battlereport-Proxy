<?php


class Battlereport
{


    public $playerReport;
    public $teams;
    public $gameServer;

    public function __construct($data)
    {
        $this->playerReport = $data->playerReport;
        $this->teams = $data->teams;
        $this->gameServer = $data->gameServer;
    }

    public function getAllPlayerIdsInReport()
    {
        $team1 = $this->teams->{'1'}->players;
        $team2 = $this->teams->{'2'}->players;
        return array_merge($team1, $team2);
    }
}
