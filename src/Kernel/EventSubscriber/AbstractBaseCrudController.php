<?php

declare(strict_types=1);

namespace App\Kernel\EventSubscriber;

use App\Kernel\Security\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @method UserInterface|null getUser()
 */
abstract class AbstractBaseCrudController extends AbstractCrudController
{
}
