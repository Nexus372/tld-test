<?php

namespace App\Controller;

use App\Service\ApiGithubManager;
use http\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GithubDataController extends AbstractController
{
    /**
     * @Route("/{user}/{repository}/{since}/{until}", name="user_repository", defaults={"since"=4, "until"=0})
     */
    public function userRepository(string $user, string $repository, int $since, int $until, ApiGithubManager $apiGithubManager): JsonResponse
    {
        if ($since <= $until) {
            throw new InvalidArgumentException('Since date must be superior to until date');
        }

        $sinceDate = new \DateTime();
        $sinceDate = $sinceDate->setTimestamp(strtotime("-$since week"));
        $untilDate = new \DateTime();
        $untilDate = $untilDate->setTimestamp(strtotime("-$until week"));
       // dd($sinceDate->format(\DateTime::ISO8601));
        dd($apiGithubManager->getCommitsByWeek($user, $repository, $sinceDate, $untilDate));
        return new JsonResponse($apiGithubManager->getCommitsByWeek($user, $repository, $since, $until));
    }
}
