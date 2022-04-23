<?php

namespace App\Controller;

use App\Service\ApiGithubManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GithubDataController extends AbstractController
{
    /**
     * Get commits github from user/repository
     *
     * @Route("/{user}/{repository}/{since}/{until}", name="user_repository", defaults={"since"=4, "until"=0})
     */
    public function userRepository(string $user, string $repository, int $since, int $until, ApiGithubManager $apiGithubManager): JsonResponse
    {
        if ($since <= $until) {
            throw new InvalidArgumentException('Since date must be superior to until date');
        }

        if ($until < 0) {
            throw new InvalidArgumentException('Invalid until date');
        }

        // Format date
        $sinceDate = new \DateTime();
        $sinceDate = $sinceDate->setTimestamp(strtotime("-$since week"));
        $untilDate = new \DateTime();
        $untilDate = $untilDate->setTimestamp(strtotime("-$until week"));

        return new JsonResponse($apiGithubManager->getCommitsByWeek($user, $repository, $sinceDate, $untilDate));
    }
}
