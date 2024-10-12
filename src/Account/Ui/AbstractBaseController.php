<?php

declare(strict_types=1);

namespace App\Account\Ui;

use App\Account\Domain\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @method User|null getUser()
 */
abstract class AbstractBaseController extends AbstractController
{
}
