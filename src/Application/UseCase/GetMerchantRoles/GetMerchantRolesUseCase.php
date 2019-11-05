<?php

namespace App\Application\UseCase\GetMerchantRoles;

use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;

class GetMerchantRolesUseCase
{
    private $roleRepository;

    public function __construct(MerchantUserRoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function execute(int $merchantId): GetMerchantRolesResponse
    {
        $roles = $this->roleRepository->findAllByMerchantId($merchantId);

        return new GetMerchantRolesResponse(...$roles);
    }
}
