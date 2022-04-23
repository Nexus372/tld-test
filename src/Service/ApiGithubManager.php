<?php

namespace App\Service;

use Github\Client;

class ApiGithubManager
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function connect(string $user, string $repository)
    {
        $repositories = $this->client->api('user')->repositories('symfony');

        dd($repositories);
    }

    public function getCommitsByWeek(string $user, string $repository, \DateTime $since, \DateTime $until, $page = 1)
    {
        $commits = $this->client->api('repo')->commits()->all($user, $repository, array(
            'sha' => '6.1',
            'per_page' => 20,
            'page' => $page,
            'since' => $since->format(\DateTime::ISO8601),
            'until' => $until->format(\DateTime::ISO8601)
        ));

        $result = [];
        foreach ($commits as $commit) {
            $date = new \DateTime($commit['commit']['committer']['date']);
            $week = intval($date->format("W"));

            if (!isset($result[$week])) {
                $result[$week] = [
                    'year' => intval($date->format("Y")),
                    'week' => $week,
                    'count' => 0,
                    'commits' => []
                ];
            }
            $result[$week]['commits'][] = $commits;
            $result[$week]['count']++;
        }

        return $result;
    }

    public function getCommitsByWeekRecursive(string $user, string $repository, \DateTime $since, \DateTime $until, $page = 1, $result = [])
    {
        $commits = $this->client->api('repo')->commits()->all($user, $repository, array(
            'sha' => '6.1',
            'per_page' => 100,
            'page' => $page,
            'since' => $since->format(\DateTime::ISO8601),
            'until' => $until->format(\DateTime::ISO8601)
        ));

        $continue = true;
        foreach ($commits as $commit) {
            $date = new \DateTime($commit['commit']['committer']['date']);
            $week = intval($date->format("W"));

            if ($date < $since) {
                $continue = false;
                break;
            }

            if (!isset($result[$week])) {
                $result[$week] = [
                    'year' => intval($date->format("Y")),
                    'week' => $week,
                    'count' => 0,
                    'commits' => []
                ];
            }
            $result[$week]['commits'][] = $commits;
            $result[$week]['count']++;
        }

        if ($continue) {
            $this->getCommitsByWeek($user, $repository, $since, $until, $page + 1, $result);
        }

        return $result;
    }
}
