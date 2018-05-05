<?php
/**
 * Contains EN\Api\ApiBundle\Controller\AbstractController
 *
 * @package EN\Api\ApiBundle
 * @subpackage Controller
 */

declare(strict_types=1);

namespace EN\Api\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

/**
 * Class AbstractController
 * @package EN\Api\ApiBundle\Controller
 */
abstract class AbstractController extends FOSRestController
{
	use \EN\OneReachBasic\DoctrineBundle\Entity\Service\HelperTrait;
}
