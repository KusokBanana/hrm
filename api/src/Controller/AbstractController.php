<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends SymfonyAbstractController
{
    protected function getRequestData(Request $request, array $allowed, array $required = []): ParameterBag
    {
        $data = json_decode($request->getContent(), true);
        $parameters = new ParameterBag($data);

        foreach ($parameters->keys() as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new BadRequestException(sprintf(
                    'Unexpected parameter "%s". Allowed: %s',
                    $key,
                    join(', ', $allowed)
                ));
            }
        }

        foreach ($required as $key) {
            if (!in_array($key, $parameters->keys(), true)) {
                throw new BadRequestException(sprintf(
                    'Parameter "%s" is required.', $key,
                ));
            }
        }

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
