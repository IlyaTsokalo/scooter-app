<?php

namespace App\Service;

use App\Repository\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    public function getFirstUserUUID(): string
    {
        $user = $this->userRepository->createQueryBuilder('user')
            ->select('user.uuid')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $user['uuid'];
    }
}
