<?php

namespace App\Service;

use App\Repository\ClientRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ClientService
{
    private $repository;
    private $paginator;

    public function __construct(ClientRepository $clientRepository, PaginatorInterface $paginator)
    {
        $this->repository = $clientRepository;
        $this->paginator = $paginator;
    }

    public function paginate(Request $request)
    {
        $query = $this->repository->createQueryBuilder('client');

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $pagination;
    }
}