<?php

namespace App\Service;

use Github\Client;

class ApiGithubManager
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Get commits using user and repository by since date and until date
     *
     * @param string $user
     * @param string $repository
     * @param \DateTime $since
     * @param \DateTime $until
     * @param int $page
     * @return array
     * @throws \Exception
     */
    public function getCommitsByWeek(string $user, string $repository, \DateTime $since, \DateTime $until, $page = 1)
    {
        $commits = $this->client->api('repo')->commits()->all($user, $repository, array(
            'per_page' => 500,
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

    /**
     * Recursive method to get commits by week
     *
     * @param string $user
     * @param string $repository
     * @param \DateTime $since
     * @param \DateTime $until
     * @param int $page
     * @param array $result
     * @return array|mixed
     * @throws \Exception
     */
    public function getCommitsByWeekRecursive(string $user, string $repository, \DateTime $since, \DateTime $until, $page = 1, $result = [])
    {
        $commits = $this->client->api('repo')->commits()->all($user, $repository, array(
            'per_page' => 100,
            'page' => $page,
            'since' => $since->format(\DateTime::ISO8601),
            'until' => $until->format(\DateTime::ISO8601)
        ));

        $continue = true;
        foreach ($commits as $commit) {
            $date = new \DateTime($commit['commit']['committer']['date']);
            $week = intval($date->format("W"));

            // Check date if we need a new search
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
            // Avoid ban from github
            usleep(300);
            $this->getCommitsByWeekRecursive($user, $repository, $since, $until, $page + 1, $result);
        }

        return $result;
    }
}
