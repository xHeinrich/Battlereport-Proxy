<?php

/**
 * Simple distributable app to proxy some battlefield 4 battlereport data
 * Input data as json, send content type and accept headers as application/json
 * {
 *    "reports": [
 *       {
 *          "report_id": 1287385081819996352,
 *          "player_id": 287164102,
 *          "all": true
 *       }
 *     ]
 *   }
 */

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Promise\settle;

require_once 'vendor/autoload.php';
require_once 'BattlereportClient.php';
require_once 'Battlereport.php';

$allowed_ips = [
    '127.0.0.1',
    '192.168.10.1'
];

$user_ip = $_SERVER['REMOTE_ADDR'];

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($user_ip, $allowed_ips)) {
    echo 'Battlereport Node - ' . $user_ip;
    return;
}

$data = json_decode(file_get_contents('php://input'), true);

$reports = $data['reports'] ?: [];


$client = (new BattlereportClient)->getClient();

$promises = [];
$responses = [];

foreach($reports as $report) {
    $report_id = $report['report_id'] ?? null;
    $player_id = $report['player_id'] ?? null;
    $all = $report['all'] ?? false;
    $key = $report_id . $player_id;

    if(is_null($report_id) || is_null($player_id)) {
        echo 'Invalid request, ensure input structure is correct.';
        echo json_encode($report);
        exit;
    }

    $promises[$key] = $client->getAsync("http://battlelog.battlefield.com/bf4/battlereport/loadgeneralreport/$report_id/1/$player_id")
        ->then(function(ResponseInterface $res) use(&$responses, &$promises, $report_id, $all, $client) {
            $battleReport = new Battlereport(json_decode($res->getBody()->getContents()));
            $responses[] = $battleReport;

            // if we wanna grab every the reports for every player in the report hit this
            if($all) {
                foreach($battleReport->getAllPlayerIdsInReport() as $player_id) {
                    $promises[$report_id . $player_id] = $client->getAsync("http://battlelog.battlefield.com/bf4/battlereport/loadgeneralreport/$report_id/1/$player_id")
                        ->then(function(ResponseInterface $res) use(&$responses, &$promises, $report_id, $all) {
                            $battleReport = new Battlereport(json_decode($res->getBody()->getContents()));
                            $responses[] = $battleReport;
                        }, function(RequestException $ex) {

                        });
                }
            }
        }, function(RequestException $ex) {

        });
}

settle($promises)->wait();

echo json_encode($responses);
exit;
