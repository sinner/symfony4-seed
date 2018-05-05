<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Traits\ControllerEntityServiceTrait;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * Class AbstractController
 * @package App\Controller
 */
abstract class AbstractController extends FOSRestController
{
	use ControllerEntityServiceTrait;
}
