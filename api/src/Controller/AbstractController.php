<?php

namespace App\Controller;

use App\Entity\User;
use Assert\Assert;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends SymfonyAbstractController
{
    protected function getRequestData(Request $request, array $allowed): ParameterBag
    {
        $data = json_decode($request->getContent(), true);
        $parameters = new ParameterBag($data);

        Assert::thatAll($parameters->keys())->inArray($allowed);

        return $parameters;
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return parent::getUser();
    }
}
