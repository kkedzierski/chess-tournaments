<?php

namespace App\Dashboard\Ui;

use App\User\Domain\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @method User|null getUser()
 */
abstract class AbstractBaseController extends AbstractController
{
}
